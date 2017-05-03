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

namespace GemeindeIT\iCalConverter\Modifier\Event\Set;

use Sabre\VObject\Component;

/**
 * Modifier for Organizer: Just set it to a specific value.
 *
 * @author Johannes Brakensiek <johannes@gemeinde-it.de>
 */
class Organizer extends AbstractSet {
    
    /**
     * 
     * @param Component\VEvent $component
     * @return bool
     * @throws \InvalidArgumentException
     */
    function process(Component $component) : bool {
        if (!is_string($this->config)) {
            throw new \InvalidArgumentException(__CLASS__ . ' was not configured using a string value.');
        }

        $modified = false;
        
        if(!isset($component->ORGANIZER) 
                || strtolower($component->ORGANIZER) !== strtolower($this->config))
        {
            $component->ORGANIZER = $this->config;
            $modified = true;
            $this->logger->debug('Setting organizer of event to ' . $this->config, array($component->UID, $this));
        }
        
        return $modified;
    }
}
