<?php
/*
 * Copyright (C) 2017 Johannes Brakensiek <johannes@gemeinde-it.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace GemeindeIT\iCalConverter;

use DateTimeInterface;
use Sabre\VObject;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use GemeindeIT\iCalConverter\Modifier\AbstractModifier;
use GemeindeIT\iCalConverter\Modifier\Event\Filter\AbstractFilter;

/**
 * Main process class handling the convertation
 *
 * @author Johannes Brakensiek <johannes@gemeinde-it.de>
 */
class Converter implements LoggerAwareInterface {
    use LoggerAwareTrait;
    
    /**
     *
     * @var array|Modifier
     */
    protected $modifierRegistry = array();
    
    /**
     *
     * @var string
     */
    protected $importFileName;
    
    /**
     * @var VObject\Component\VCalendar
     */
    protected $importCal = null;

    /**
     * @var VObject\Component\VCalendar
     */
    protected $exportCal = null;
    
    /**
     *
     * @var string
     */
    protected $exportFileName;

    /**
     * 
     * @param string $importFile Path to the .ics/iCal file to import
     * @param string $exportFile Path to the .ics/iCal file to export the data. 
     *                           Will be overwritten if it already exists.
     * @param Psr\Log\LoggerInterface $logger
     */
    function __construct(string $importFile, string $exportFile, LoggerInterface $logger) {
        $this->importFileName = $importFile;
        $this->exportFileName = $exportFile;
        $this->logger         = $logger;
    }
    
    /**
     * Loads data from specified file
     * 
     * Do not run this method if you want to inject data or calendar objects.
     * 
     * @throws \InvalidArgumentException If calendar is not found or data is not valid.
     * @throws \Exception If input file could not be opened.
     * @return \self
     */
    function prepare() : self {
        $importFileHandle = $this->openImportFile($this->importFileName);
        
        $this->importCal = VObject\Reader::read($importFileHandle);
        fclose($importFileHandle);

        $this->exportCal = new VObject\Component\VCalendar();
        
        $this->validateCalendar($this->importCal);
        
        return $this;
    }
    
    /**
     * Execute the conversion of the data
     * 
     * @return self
     * @param DateTimeInterface $expandEnd Time until which recurring events of the import calendar should be computed.
     * @throws \DomainException If there is no data prepared to convert.
     * @throws \DomainException If the input calendar data is not valid.
     * @see prepare()
     */
    function convert(DateTimeInterface $expandEnd = null) : self {
        $this->checkPreconditionsToConvert();
        
        // Copy timezone information.
        $this->exportCal->VTIMEZONE = $this->importCal->VTIMEZONE;
        
        // Expand calendar (compute recurring events depending on parameter)
        if ($expandEnd !== null) {
            $this->logger->info('Computing recurring events in import calendar until… (expanding)', array($expandEnd));
            $this->importCal = $this->importCal->expand($this->getEarliestStartDateOfCal($this->importCal), $expandEnd);
        }

        // Setup counters
        $numEventsChanged = 0;
        $numEventsFilteredOut = 0;
        $numEvents = count($this->importCal->VEVENT);
        
        $this->logger->info('Number of events to process: ', array($numEvents));
        
        // Process each event of the calendar
        foreach($this->importCal->VEVENT as $event) {
            $changed = false;
            
            // Process each modifier on this event
            foreach($this->modifierRegistry as $modifier) {
                
                $result = $modifier->process($event);
                $this->logger->debug('Modifier returns:', array($modifier, $result));
                
                // true and a filter: the event is to be sorted out: stop both(!) loops
                if($result && $modifier instanceof AbstractFilter) {
                    $numEventsFilteredOut++;
                    continue 2;
                }
                
                // If the event was modified we need to remeber that in this
                // extra variable so the next modifier won't set it to false.
                if($result) {
                    $changed = true;
                }
            }
            
            // Add processed event to the new calendar
            $this->exportCal->add($event);
            
            if($changed) {
                $numEventsChanged++;
            }
        }
        
        $this->logger->info('Events filtered out.', array($numEventsFilteredOut));
        $this->logger->info('Events updated.', array($numEventsChanged));
        
        return $this;
    }
    
    /**
     * Saves converted calendar data to file.
     * 
     * @return int 0 if no error.
     * @throws \DomainException If there is no data to save.
     */
    function save() : int {
        if($this->importCal === null || $this->exportCal === null) {
            throw new \DomainException(__CLASS__. ' was not prepared with data.');
        }
        
        $numEvents = count($this->exportCal->VEVENT);
        $this->saveExportFile($this->exportCal, $this->exportFileName);
        $this->logger->info('Events saved to export file.', array($this->exportFileName, $numEvents));
        return 0;
    }
    
    /**
     * Add a modifier to the registry to be processed during conversion
     * 
     * Each modifier will be applied on each event of the calendar. Modifiers
     * should already be configured.
     * 
     * @param AbstractModifier $modifier
     */
    function pushRegistry(AbstractModifier $modifier) {
        $this->modifierRegistry[] = $modifier;
        
        $this->logger->debug('Added modifier to registry.', array($this, $modifier));
    }

    protected function openImportFile(string $importFilename) {
        if (!file_exists($importFilename)) {
            throw new \InvalidArgumentException($importFilename . ' does not exist.');
        }

        $importFileHandle = fopen($importFilename, 'r');
        if(!$importFileHandle) {
            throw new \Exception ($importFilename . ' could not be opened for reading.');
        }
        
        return $importFileHandle;
    }
    
    protected function validateCalendar(VObject\Component\VCalendar $calendar) : bool {
        $result = $calendar->validate();
        
        if (empty($result)) {
            $this->logger->info('Calendar is valid.', array(__METHOD__));
            return true;
        }

        foreach($result as $problem) {
            switch ($problem['level']) {
                case 3:
                    $message = 'Invalid calendar data: ' . $problem['message'];
                    $this->logger->critical($message, array($problem['node'], __METHOD__));
                    throw new \InvalidArgumentException($message);
                    break;
                
                case 2:
                    $message = 'Calendar data is not fully valid, proceeding anyways: ' . $problem['message'];
                    $this->logger->warn($message, array($problem['node'], __METHOD__));
                    break;
               
                case 1:
                    $message = 'There was a problem with invalid calendar data, but it was repaired: ' . $problem['message'];
                    $this->logger->info($message, array($problem['node'], __METHOD__));
                    break;
            }
        }
        
        return true;
    }
    
    protected function checkPreconditionsToConvert() {
        if($this->importCal === null || $this->exportCal === null) {
            throw new \DomainException(__CLASS__. ' was not prepared with data.');
        }
        
        if(empty($this->modifierRegistry)) {
            throw new \DomainException(__CLASS__. ' has no modifiers set up.');
        }
    }
    
    protected function getEarliestStartDateOfCal(VObject\Component\VCalendar $calendar) {
        $earliestStartDate = new \DateTime();
        $this->logger->debug('Setting earliest start date to now.', array($earliestStartDate, __METHOD__));
        
        foreach($calendar->VEVENT as $event) {
            
            if($event->DTSTART->getDateTime($earliestStartDate->getTimezone()) < $earliestStartDate) {
                
                $earliestStartDate = $event->DTSTART->getDateTime($earliestStartDate->getTimezone());
                $this->logger->debug('Setting earliest start date to: ', array($earliestStartDate, __METHOD__));
            }
        }
        
        return $earliestStartDate;
    }

    protected function saveExportFile(VObject\Component\VCalendar $calendar, string $exportFileName) {
        $fileHandle = fopen($exportFileName, 'w');
        
        if(!$fileHandle) {
            throw new \Exception($exportFileName . ' could not be opened for saving updated data.');
        }
        
        fwrite($fileHandle, VObject\Writer::write($calendar));
        
        fclose($fileHandle);
    }
    
    /**
     * Returns input calendar to imported
     * 
     * @return \Sabre\VObject\Component\VCalendar
     */
    function getImportCal(): VObject\Component\VCalendar {
        return $this->importCal;
    }

    /**
     * Returns output calendar to be exported
     * 
     * @return \Sabre\VObject\Component\VCalendar
     */
    function getExportCal(): VObject\Component\VCalendar {
        return $this->exportCal;
    }

    /**
     * Set calendar with data to be converted/imported
     * 
     * @param \Sabre\VObject\Component\VCalendar $importCal
     * @return $this
     */
    function setImportCal(VObject\Component\VCalendar $importCal) {
        $this->importCal = $importCal;
        return $this;
    }

    /**
     * Set calendar instance data should be converted to
     * 
     * @param \Sabre\VObject\Component\VCalendar $exportCal
     * @return $this
     */
    function setExportCal(VObject\Component\VCalendar $exportCal) {
        $this->exportCal = $exportCal;
        return $this;
    }

}
