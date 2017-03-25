#!/usr/bin/env php
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

require __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;

if ($argc < 4 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

This script converts iCal data as described by the configuration file.

  Usage:
  <?php echo $argv[0]; ?> <configurationfile> <importfile> <exportfile> [-debug]

  <configurationfile> Path to the file which describes how
      to handle <importfile>.
  <importfile> Path to the .ics/iCalendar file to import.
  <exportfile> Path to the (new) .ics/iCalendar file to 
      export the converted data to.
  
  -debug Add this option if you want to save debug information to log files in the log directory.
  
  Using the options --help, -help, -h oder -? you get this help.

<?php
} else {
    
    // Prepare logger
    $now = date(DATE_ATOM);
    $logger = new Logger('iCC');
    
    // Setup log
    // Debug log
    $debug = isset($argv[4]);
    if($debug) {
        $logger->pushHandler(new StreamHandler(__DIR__.'/log/icalconverter-' . $now . '.log', Logger::DEBUG));
    }
    
    // User output
    $logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::INFO));
    
    // Load config and run the application
    $app = new Application();
    exit($app->run(loadConfig($argv[1]), $argv[2], $argv[3], $logger));
    
}

/**
 * Loads configuration file
 * 
 * @param string $cfgFileName Path to the php configuration file
 * @return array Containing configuration information.
 * @throws \InvalidArgumentException If the file was not found.
 */
function loadConfig(string $cfgFileName) : array {
    if (!file_exists($cfgFileName)) {
        throw new \InvalidArgumentException('Configuration file "' . $cfgFileName . '" was not found.');
    }

    return require_once $cfgFileName;
}