<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
Let's turn <em>data</em> into <em>features.</em>
================================================

Version 1 of the uwdata API covers the following data sets:

* Course Calendar information back to the 2001-2002 school year.
* Course scheduling for current terms, updated in real time.[^1]
* Professor information, including ratemyprofessors.com scores for ease and quality.
* Course prerequisites parsed in a prefix-order format, allowing easy calculation of satisfaction.

The API is accessed via a set of URLs that are outlined below.

API Version 1
-------------

### v1/faculty/list.[xml|json]

A list of all faculties in the school.

#### Optional parameters:

* `cal` The calendar years to check. Default: the latest calendar year. Example: `&cal=20072008`.

#### Example JSON result:
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



#### Example XML response:

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
        <faculty>
          <acronym>KIN</acronym>
          <name>Kinesiology</name>
        </faculty>
        <faculty>
    ...
        <faculty>
          <acronym>SCBUS</acronym>
          <name>Science and Business</name>
        </faculty>
      </faculties>
    </result>


[^1]: Real time here means once an hour, between the hours of 8am and 8pm.
