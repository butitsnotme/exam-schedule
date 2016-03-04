# Exams Schedule Calendar

Creates a iCalendar file for a specified set of exams.

## Installation
To use run this web app you need a University of Waterloo Open Data API key
(https://api.uwaterloo.ca/). Additionally, you will need PHP5 and Apache with
`mod_rewrite` enabled and MySQL (or MariaDB).

Pick a directory to run from, and copy the PHP files there. Copy the Apache
config to the apache sites folder and adjust to match your installation.
Check and run the .sql file to create the database. Next, copy
`passwords.example.php` to `passwords.php` and fill in the values.

To populate the database cache run the update-cache.php file. An example cron
job has been provided in update-cache.cron which runs this once a day.

Once apache has been restarted to use the new configuration the site should work

## Problems?
Open an issue on GitHub. Please try to provide as many details as possible.

## Got a fix?
Submit a pull request on GitHub. The fix should be clearly identified and should
not introduce any known bugs (if it does, state upfront what they are and make
sure that it is not on the master branch).

## Development Notes
This web app was developed on, and primarily runs on, two servers:
 * Web server
   * Debian 8.1 (Jessie)
   * Running Apache HTTPd 2.4
   * MySQL client libraries 5.5
   * PHP 5.6
 * Database server
   * Debian 8.1 (Jessie)
   * Running MariaDB 10.0

### Basic Design
The web app periodically loads all exam information from the UWaterloo API
and places it into a database. As a user builds a calendar the database is
queried to retirieve that information. The list of courses that a user has
picked resides in the URL. This data is not the exam information, just a 
reference to that information, on each page display or calendar request, the
most current information for the selected courses is pulled from the database.

### Description of Files
Below is a brief description of the most important files

#### update-cache.php
This file updates the database cache from the UWaterloo API. It should be run
periodically. It makes several calls to the UWaterloo API to request the 
complete exam schedule for every available term. As of this writing, that is
approximately 10 API calls.

#### index.php
This file produces lists of links which enable the user to build the list of
exams into the url to for use with the cal.php file.

#### cal.php
This file produces an iCalendar/vCal file from the data contained in the URL
and the Database. The format of the URL is such that it should be able to 
downloaded as a `.ics` file or added as a URL to a calendar system (such as
Google Calendar).

#### common.php and passwords.php
These files contain the information necessary to connect to the database and
extract the data from, and into, the URL.
