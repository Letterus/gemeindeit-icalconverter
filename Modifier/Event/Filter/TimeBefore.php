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

namespace GemeindeIT\iCalConverter\Modifier\Event\Filter;

use Sabre\VObject\Component;

/**
 * TimeBefore
 * 
 * Marks VEvents starting before a certain date (time) to be filtered out.
 *
 * @author Johannes Brakensiek <johannes@gemeinde-it.de>
 */
class TimeBefore extends AbstractFilter {
    
    /**
     * 
     * @param Component\VEvent $component
     * @return bool True, if the event should be filtered of, false, if not.
     * @throws \InvalidArgumentException
     */
    function process(Component $component) : bool {
        
        if($this->config instanceof \DateTime) {
            $criterium = $this->config;
        
        } elseif(is_string($this->config)) {
            $criterium = new \DateTime($this->config);
            
        } else {
            throw new \InvalidArgumentException('Config contains no valid DateTime value for comparison.');
        }
        
        // Compare times
        if ($component->DTSTART->getDateTime() <= $criterium) {
            $this->logger->debug('Event marked to be filtered out.', array($component->UID, $criterium));
            return true;
        }

        return false;
    }
}
