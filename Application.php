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

use Psr\Log\LoggerInterface;

/**
 * Application
 * 
 * Prepares and executes converter.
 *
 * @author Johannes Brakensiek <johannes@gemeinde-it.de>
 */
class Application {
    
    const CLASS_MAP_FILE = __DIR__.'/config/ModifierClassMap.php';
    
    /**
     *
     * @var array
     */
    protected $configuration;
    
    /**
     * 
     * @param array $configuration
     * @param string $importFile
     * @param string $exportFile
     * @param LoggerInterface $logger
     * @return int
     */
    function run(array $configuration, string $importFile, string $exportFile, LoggerInterface $logger) : int {
        $this->configuration = $configuration;
        
        $converter = new Converter($importFile, $exportFile, $logger);
        
        // Add modifiers…
        $this->addModifiersToConverter($converter, $this->getModifierClassMap(), $logger);
        
        $converter->prepare()
                  ->convert()
                  ->save();
        
        return 0;
    }
    
    /**
     * 
     * @param \GemeindeIT\iCalConverter\Converter $converter
     * @param array $classMap
     * @param LoggerInterface $logger
     * @throws \OutOfRangeException
     */
    function addModifiersToConverter(Converter $converter, array $classMap, LoggerInterface $logger) {
        
        foreach ($this->configuration as $item) {
            foreach($item as $class => $classConfig) {

                if(!isset($classMap[$class])) {
                    throw new \OutOfRangeException('Class map does not know of a PHP class for ' . $class);
                }
                
                $fullClassName = __NAMESPACE__ . '\\' . $classMap[$class];
                $modifier = new $fullClassName($classConfig, $logger);
                $converter->pushRegistry($modifier);
                
            }
        }
    }

    protected function getModifierClassMap() : array {
        return require_once self::CLASS_MAP_FILE;
    }
}
