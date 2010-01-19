<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
Let's turn <em>data</em> into <em>features.</em>
================================================

Version 1 of the uwdata API covers the following data sets:

* Course Calendar information back to the 2001-2002 school year.
* Course scheduling for current terms, updated in real time.[^1]
* Professor information, including ratemyprofessors.com scores for ease and quality.
* Course prerequisites parsed in a prefix-order format, allowing easy calculation of satisfaction.

The API is accessed via a set of URLs that are outlined below.

<div class="blocks">
<div class="block" markdown="1">
### Faculties

* [Faculty List](#faculty_list)
* [Faculty Information](#faculty_by_id)
</div>

<div class="block" markdown="1">
### Faculties

* [Faculty List](#faculty_list)
* [Faculty Information](#faculty_by_id)
</div>

</div>
<div class="clearfix"></div>

API Version 1
-------------

### v1/faculty/list.[xml|json] {#faculty_list}

A list of all faculties in the school for any given year.

#### Optional parameters:

* `cal` The calendar years to check. Default: the latest calendar year. Example: `&cal=20072008`.

#### Example JSON response for [/v1/faculty/list.json](http://api.uwdata.ca/v1/faculty/list.json):
    {
       faculties: [
          {
             faculty: {
                acronym: 'GERON',
                name: 'Gerontology'
             }
          },
          {
             faculty: {
                acronym: 'HLTH',
                name: 'Health Studies'
             }
          },
    ...
          {
             faculty: {
                acronym: 'SCBUS',
                name: 'Science and Business'
             }
          }
       ]
    }

#### Example XML response for [/v1/faculty/list.xml](http://api.uwdata.ca/v1/faculty/list.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <faculties>
        <faculty>
          <acronym>GERON</acronym>
          <name>Gerontology</name>
        </faculty>
        <faculty>
          <acronym>HLTH</acronym>
          <name>Health Studies</name>
        </faculty>
    ...
        <faculty>
          <acronym>SCBUS</acronym>
          <name>Science and Business</name>
        </faculty>
      </faculties>
    </result>

### v1/faculty/[faculty acronym].[xml|json] {#faculty_by_id}

Detailed information about the given faculty for any given year.

#### Optional parameters:

* `cal` The calendar years to check. Default: the latest calendar year. Example: `&cal=20072008`.

#### Example JSON response for [/v1/faculty/CS.json](http://api.uwdata.ca/v1/faculty/CS.json):

    {
       faculty: {
          acronym: 'CS',
          name: 'Computer Science',
          last_updated: '2010-01-19 05:01:59'
       }
    }

#### Example XML response for [/v1/faculty/CS.xml](http://api.uwdata.ca/v1/faculty/CS.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <faculty>
        <acronym>CS</acronym>
        <name>Computer Science</name>
        <last_updated>2010-01-19 05:01:59</last_updated>
      </faculty>
    </result>


[^1]: Real time here means once an hour, between the hours of 8am and 8pm.

