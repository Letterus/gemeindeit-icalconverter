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

$grosserSaal   = 'Haus Fuhr - Großer Saal (EG)';
$seminarraum   = 'Haus Fuhr - Seminarraum (1. OG)';
$foyer         = 'Haus Fuhr - Foyer (EG)';
$heckerzimmer  = 'Haus Fuhr - Heckerzimmer (EG)';
$saaletage     = 'Haus Fuhr - Saaletage';
$saalFoyer     = 'Haus Fuhr - gr. Saal und Foyer';
$ohneZuordnung = 'Haus Fuhr - Ohne Zuordnung';

// Calculate recurring events for the next two years
$interval = new \DateInterval('P2Y');
$end = new \DateTime();
$end->add($interval);


// This example shows how to configure a conversion of a Google calendar
// to the ressource management software Booked Scheduler.
return [
    // General calendar converting configuration
    'calendar' => [
        'expandEnd' => $end
    ],
    
    // Filter all events in the past (before now)
    ['filter_time_before' => new \DateTime()],
    
    // Set the organizer of all events to this address. The account of this
    // address will become the owner of the schedules in Booked. It will be
    // created if it does not exist yet.
    ['set_organizer'      => 'user@host.com'],
    
    // Same as above (locations will be ressources in Booked, not existing
    // ones will be created), but do a simple search and replace. Full text
    // only. No regexp yet.
    ['replace_location'   => [
            // Replace the first value by the second one.
            'gr. Saal' => $grosserSaal,
            'Seminarraum' => $seminarraum,
            'Foyer' => $foyer,
            'Foyer, Terrasse, Küche' => $foyer,
            'Heckerzimmer' => $heckerzimmer,
            'Saaletge' => $saaletage,
            'Saaletage' => $saaletage,
            'gr. Saal, Foyer, Gaderobe' => $saalFoyer,
            'gr. Saal und Foyer' => $saalFoyer,
            'Foyer, Küche, Garderobe und Terasse' => $foyer,
            'Foyer, Küche, Garderobe' => $foyer,
            'ganzes Haus' => 'Haus Fuhr - ganzes Haus',
            'gr. Saal oder Seminarraum' => 'Haus Fuhr - gr. Saal und Seminarraum',
            '???' => $ohneZuordnung,
        
            // Special key: Replace empty/unset locations with the second value.
            // This could be the standard or the 'collect the rest' ressource in
            // Booked.
            'empty' => $ohneZuordnung
    ]],  
];