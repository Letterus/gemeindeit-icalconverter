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

namespace GemeindeIT\iCalConverter\Modifier;

use Sabre\VObject\Component;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Description of AbstractModifier
 *
 * @author Johannes Brakensiek <johannes@gemeinde-it.de>
 */
abstract class AbstractModifier implements LoggerAwareInterface {
    use LoggerAwareTrait;
    
    protected $config = null;
    
    /**
     * Set configuration and logger for the modifier
     * 
     * @param type $config
     * @param Psr\Log\LoggerInterface $logger
     */
    function __construct($config, LoggerInterface $logger) {
        $this->config = $config;
        $this->logger = $logger;
    }
    
    /**
     * Modifys a VObject component.
     * 
     * @param Sabre\VObject\Component $component
     * @return bool True, if the component was modified or filtered/sorted out.
     */
    abstract public function process(Component $component) : bool;
}
