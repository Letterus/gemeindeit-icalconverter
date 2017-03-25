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

use Modifier\Event\Filter;
use Modifier\Event\Replace;
use Modifier\Event\Set;

/**
 * Defines which class should be loaded for which key in the php configuration
 * file/array. The corresponding value in the config file will be the parameter
 * given to the class when beeing instanciated.
 */
return [
    'filter_time_before' => Filter\TimeBefore::class,
    'set_organizer'      => Set\Organizer::class,
    'replace_location'   => Replace\Location::class,
];