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

namespace GemeindeIT\iCalConverter\Modifier\Event\Replace;

use Sabre\VObject\Component;

/**
 * Replace: VEvent Location
 * 
 * Replaces a given location of a VEvent with a defined replacement value.
 * Expects an array of search => replacement values.
 * This just works on full strings. There is no search and replace within
 * a string currcently.
 * 
 * You can use the special key 'empty' to replace an empty location with
 * a given string.
 *
 * @author Johannes Brakensiek <johannes@gemeinde-it.de>
 */
class Location extends AbstractReplace {
    
    /**
     * 
     * @param Component\VEvent $component
     * @return bool True, if the event was changed, false if not.
     * @throws \InvalidArgumentException
     */
    function process(Component $component) : bool {
        if (!is_array($this->config) || empty($this->config)) {
            throw new \InvalidArgumentException(__CLASS__ . ' was not configured using an array of values.');
        }
        
        // If a given location is found within the configuration, replace it
        // using the defined replacement.
        if(array_key_exists(trim($component->LOCATION), $this->config)) {
            
            $component->LOCATION = $this->config[trim($component->LOCATION)];
            $this->logger->debug('Set location of event to ' . $component->LOCATION, array($component->UID, $this));
            
            return true;
        }
        
        // Replace an empty location with the value for config placeholder 'empty' if set
        if(empty(trim($component->LOCATION)) && array_key_exists('empty', $this->config)) {
            $component->LOCATION = $this->config['empty'];
            $this->logger->debug('Set location of event to ' . $component->LOCATION, array($component->UID, $this));
            
            return true;
        }
        
        // Nothing was changed when we get here.
        return false;
    }
}
