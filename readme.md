# iCalConverter

## About

iCalConverter is a command line tool that uses the 
[sabre/vobject](https://github.com/fruux/sabre-vobject) library to perform
changes on iCal/ics files. It won't convert to other file formats.

You can use it f.e. if you want to free your room reservation calendar from
Google Calendar and convert this calendar to a format that can be easily
imported to [Booked Scheduler](https://www.bookedscheduler.com/).


## How to use

1. Install [composer](https://getcomposer.org/download/).
2. `php composer.phar install`
3. Execute iCalConverter:

```
    Usage:
    ./iCalConverter.php <configurationfile> <importfile> <exportfile> [-debug]

      <configurationfile> Path to the file which describes how
          to handle <importfile>.
      <importfile> Path to the .ics/iCalendar file to import.
      <exportfile> Path to the (new) .ics/iCalendar file to 
          export the converted data to.
  
    -debug Add this option if you want to save debug information to log files in the log directory.
  
    Using the options --help, -help, -h oder -? you get this help.
```

## How to configure

You will need to setup a php configuration file that tells iCalConverter how to
handle the import file. See the [example file](config/Configuration_Example.php).

## How to extend

The provided configurable "modifiers" that change the input ical data are very
basic. You can extend iCalConverter building your own "Modifier" class that
handles input data the way you want.

If you want to filter out data extend [AbstractFilter](Modifier/Event/Filter/AbstractFilter.php).
If you want to modify data extend [AbstractReplace](Modifier/Event/Replace/AbstractReplace.php) or
[AbstractSet](Modifier/Event/Set/AbstractSet.php) or create a new type of modifier.

Add your created class to [config/ModifierClassMap.php](config/ModifierClassMap.php) to make it available for
[configuration](config/Configuration_Example.php).

## Questions or support?

*Can I use iCalConverter for my webservice?*

Yes, you can. Please consider the requirements of the AGPL. Keep in mind the
software was build for local execution via CLI. It does only basic input
validation currently and is served without any warranty.

Further questions? Contact johannes@gemeinde-it.de.