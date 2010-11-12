<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
Let's turn <em>data</em> into <em>features.</em>
================================================

Version 1 of the uwdata API covers the following data sets:

* Course Calendar information back to the 2001-2002 school year.
* Course scheduling for current terms, updated in real time.[^1]
* Professor information, including ratemyprofessors.com scores for ease and quality.
* Course prerequisites parsed in a prefix-order format, allowing easy calculation of satisfaction.

The API is accessed via a set of URLs that are outlined below. Access the UW API via
`http://api.uwdata.ca/`.

Examples
--------

### JQuery

#### Search the course list

    $.ajax({
      dataType: 'jsonp',
      data: {q: query, key: 'your_api_key'},
      jsonp: 'jsonp_callback',
      url: 'http://api.uwdata.ca/v1/course/search.json',
      success: function(data) {
        console.log(data);
      }
    });

<div class="blocks">
<div class="block" markdown="1">
### Faculties

* [Faculty List](#faculty_list)
* [Faculty Information](#faculty_by_id)
* [Faculty Courses](#faculty_courses)
</div>

<div class="block" markdown="1">
### Courses

* [Course by Number](#course_by_number)
* [Course by ID](#course_by_id)
* [Course Prereqs by Number](#course_prereqs_by_number)
* [Course Prereqs by ID](#course_prereqs_by_id)
* [Course Schedule by Number](#course_schedule_by_number)
* [Course Schedule by ID](#course_schedule_by_id)
* [Course Search](#course_search)
</div>

<div class="block" markdown="1">
### Professors

* [Professor by ID](#prof_by_id)
* [Professor Timeslots](#prof_timeslots)
* [Professor Search](#prof_search)
</div>

<div class="block" markdown="1">
### Terms

* [Term List](#term_list)
</div>

<div class="block" markdown="1">
### Weather

* [Current Weather](#current_weather)
</div>

<div class="block" markdown="1">
### Geo

* [Building List](#building_list)
* [Parking List](#parking_list)
</div>

<div class="block" markdown="1">
### Dump

* [Courses](#dump_courses)
* [Schedules](#dump_schedules)
</div>

</div>
<div class="clearfix"></div>

API Version 1
-------------

### v1/faculty/list.[xml|json|csv] {#faculty_list}

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

### v1/faculty/[faculty acronym].[xml|json|csv] {#faculty_by_id}

Detailed information about the given faculty for any given year.

#### Optional parameters:

* `cal` The calendar years to check. Default: the latest calendar year. Example: `&cal=20072008`.

#### Example JSON response for [/v1/faculty/CS.json](http://api.uwdata.ca/v1/faculty/CS.json):

    {
       faculty: {
          acronym: 'ENGL',
          name: 'English',
          last_updated: '2010-01-19 05:01:59'
       }
    }

#### Example XML response for [/v1/faculty/CS.xml](http://api.uwdata.ca/v1/faculty/CS.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <faculty>
        <acronym>ENGL</acronym>
        <name>English</name>
        <last_updated>2010-01-19 05:01:59</last_updated>
      </faculty>
    </result>


### v1/faculty/[faculty acronym]/courses.[xml|json|csv] {#faculty_courses}

All of the courses in the given faculty.

#### Optional parameters:

* `cal` The calendar years to check. Default: the latest calendar year. Example: `&cal=20072008`.

#### Example JSON response for [/v1/faculty/ENGL/courses.json](http://api.uwdata.ca/v1/faculty/ENGL/courses.json):

    {
       courses: [
          {
             course: {
                cid: '11580',
                course_number: '101A',
                title: 'Introduction to Literary Studies',
                description: 'An introduction to the study of literature, covering such areas of enquiry as literary history, genre, criticism, analysis, and theory.'
             }
          },
          {
             course: {
                cid: '11581',
                course_number: '101B',
                title: 'Introduction to Rhetorical Studies',
                description: 'An introduction to the study and practice of persuasion, including the history and theory of rhetoric, the structures and strategies of arguments, and the analysis of texts and artifacts.'
             }
          },
    ...
          {
             course: {
                cid: '5224',
                course_number: '495B',
                title: 'Supervision of Honours Essay',
                description: 'Senior Honours Essay will be completed under supervision.'
             }
          }
       ]
    }

#### Example XML response for [/v1/faculty/ENGL/courses.xml](http://api.uwdata.ca/v1/faculty/ENGL/courses.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <courses>
        <course>
          <cid>11580</cid>
          <course_number>101A</course_number>
          <title>Introduction to Literary Studies</title>
          <description>An introduction to the study of literature, covering such areas of enquiry as literary history, genre, criticism, analysis, and theory.</description>
        </course>
        <course>
          <cid>11581</cid>
          <course_number>101B</course_number>
          <title>Introduction to Rhetorical Studies</title>
          <description>An introduction to the study and practice of persuasion, including the history and theory of rhetoric, the structures and strategies of arguments, and the analysis of texts and artifacts.</description>
        </course>
    ...
        <course>
          <cid>5224</cid>
          <course_number>495B</course_number>
          <title>Supervision of Honours Essay</title>
          <description>Senior Honours Essay will be completed under supervision.</description>
        </course>
      </courses>
    </result>


### v1/course/[faculty acronym]/[course number].[xml|json|csv] {#course_by_number}
### v1/course/[course id].[xml|json|csv] {#course_by_id}

Detailed information about the given course, retrieved by faculty and course number or course
calendar id. Both endpoints return the same data.

#### Optional parameters:

* `cal` The calendar years to check. Default: the latest calendar year. Example: `&cal=20072008`.

#### Example JSON response for [/v1/course/ECON/102.json](http://api.uwdata.ca/v1/course/ECON/102.json):
#### Example JSON response for [/v1/course/4877.json](http://api.uwdata.ca/v1/course/4877.json):

    {
       course: {
          cid: '4877',
          faculty_acronym: 'ECON',
          course_number: '102',
          title: 'Introduction to Macroeconomics',
          description: 'This course provides an introduction to macroeconomic analysis relevant for understanding the Canadian economy as a whole. The determinants of national output, the unemployment rate, the price level (inflation), interest rates, the money supply and the balance of payments, and the role of government fiscal and monetary policy are the main topics covered. ',
          has_lec: '1',
          has_lab: '0',
          has_tst: '1',
          has_tut: '0',
          has_prj: '0',
          credit_value: '0.5',
          has_dist_ed: '1',
          only_dist_ed: '0',
          has_stj: '0',
          only_stj: '0',
          has_ren: '0',
          only_ren: '0',
          has_cgr: '0',
          only_cgr: '0',
          needs_dept_consent: '0',
          needs_instr_consent: '0',
          avail_fall: '0',
          avail_winter: '0',
          avail_spring: '0',
          prereq_desc: '',
          antireq_desc: '',
          crosslist_desc: '',
          coreq_desc: '',
          note_desc: '[Note: Fee of up to $100 may be required for subscription to a test/assignment service.]',
          src_url: 'http://www.ucalendar.uwaterloo.ca/0910/COURSE/course-ECON.html#ECON102',
          last_updated: '2010-01-19 05:02:48'
       }
    }

#### Example XML response for [/v1/course/ECON/102.xml](http://api.uwdata.ca/v1/course/ECON/102.xml):
#### Example XML response for [/v1/course/4877.xml](http://api.uwdata.ca/v1/course/4877.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <course>
        <cid>4877</cid>
        <faculty_acronym>ECON</faculty_acronym>
        <course_number>102</course_number>
        <title>Introduction to Macroeconomics</title>
        <description>This course provides an introduction to macroeconomic analysis relevant for understanding the Canadian economy as a whole. The determinants of national output, the unemployment rate, the price level (inflation), interest rates, the money supply and the balance of payments, and the role of government fiscal and monetary policy are the main topics covered. </description>
        <has_lec>1</has_lec>
        <has_lab>0</has_lab>
        <has_tst>1</has_tst>
        <has_tut>0</has_tut>
        <has_prj>0</has_prj>
        <credit_value>0.5</credit_value>
        <has_dist_ed>1</has_dist_ed>
        <only_dist_ed>0</only_dist_ed>
        <has_stj>0</has_stj>
        <only_stj>0</only_stj>
        <has_ren>0</has_ren>
        <only_ren>0</only_ren>
        <has_cgr>0</has_cgr>
        <only_cgr>0</only_cgr>
        <needs_dept_consent>0</needs_dept_consent>
        <needs_instr_consent>0</needs_instr_consent>
        <avail_fall>0</avail_fall>
        <avail_winter>0</avail_winter>
        <avail_spring>0</avail_spring>
        <prereq_desc/>
        <antireq_desc/>
        <crosslist_desc/>
        <coreq_desc/>
        <note_desc>[Note: Fee of up to $100 may be required for subscription to a test/assignment service.]</note_desc>
        <src_url>http://www.ucalendar.uwaterloo.ca/0910/COURSE/course-ECON.html#ECON102</src_url>
        <last_updated>2010-01-19 05:02:48</last_updated>
      </course>
    </result>


### v1/course/[faculty acronym]/[course number]/prereqs.[xml|json|csv] {#course_prereqs_by_number}
### v1/course/[course id]/prereqs.[xml|json|csv] {#course_prereqs_by_id}

The prerequisite logic for the given course, retrieved by faculty and course number or course
calendar id. Both endpoints return the same data.

#### Optional parameters:

* `cal` The calendar years to check. Default: the latest calendar year. Example: `&cal=20072008`.

#### Example JSON response for [/v1/course/CS/488/prereqs.json](http://api.uwdata.ca/v1/course/CS/488/prereqs.json):
#### Example JSON response for [/v1/course/4437/prereqs.json](http://api.uwdata.ca/v1/course/4437/prereqs.json):

`Prereq description: (CM 339/CS 341 or SE 240) and (CS 350 or ECE 354) and (CS 370 or 371)`

    {
       prereqs: [
          [
             'and',
             [
                'or',
                [
                   'pair',
                   {
                      fac: 'CM',
                      num: '339'
                   },
                   {
                      fac: 'CS',
                      num: '341'
                   }
                ],
                {
                   fac: 'SE',
                   num: '240'
                }
             ],
             [
                'or',
                {
                   fac: 'CS',
                   num: '350'
                },
                {
                   fac: 'ECE',
                   num: '354'
                }
             ],
             [
                'or',
                {
                   fac: 'CS',
                   num: '370'
                },
                {
                   fac: 'CS',
                   num: '371'
                }
             ]
          ]
       ]
    }

#### Example XML response for [/v1/course/CS/488/prereqs.xml](http://api.uwdata.ca/v1/course/CS/488/prereqs.xml):
#### Example XML response for [/v1/course/4437/prereqs.xml](http://api.uwdata.ca/v1/course/4437/prereqs.xml):

`Prereq description: (CM 339/CS 341 or SE 240) and (CS 350 or ECE 354) and (CS 370 or 371)`

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <prereqs>
        <json>[["and",["or",["pair",{"fac":"CM","num":"339"},{"fac":"CS","num":"341"}],{"fac":"SE","num":"240"}],["or",{"fac":"CS","num":"350"},{"fac":"ECE","num":"354"}],["or",{"fac":"CS","num":"370"},{"fac":"CS","num":"371"}]]]</json>
      </prereqs>
    </result>


### v1/course/[faculty acronym]/[course number]/schedule.[xml|json|csv] {#course_schedule_by_number}
### v1/course/[course id]/schedule.[xml|json|csv] {#course_schedule_by_id}

The schedule for the given course, retrieved by faculty and course number or course
calendar id. Both endpoints return the same data.

#### Optional parameters:

* `term` The academic term id to check. Default: the latest academic term. Example: `&term=1101`.

#### Example JSON response for [/v1/course/MUSIC/100/schedule.json](http://api.uwdata.ca/v1/course/MUSIC/100/schedule.json):
#### Example JSON response for [/v1/course/6944/schedule.json](http://api.uwdata.ca/v1/course/6944/schedule.json):

    {
       classes: [
          {
             class: {
                class_number: '2986',
                term: '1105',
                faculty_acronym: 'MUSIC',
                course_number: '100',
                component_section: 'LEC 001',
                campus_location: 'CGC   G',
                associated_class: '1',
                related_component_1: '0',
                related_component_2: '0',
                enrollment_cap: '70',
                enrollment_total: '0',
                wait_cap: '0',
                wait_tot: '0',
                tba_schedule: '0',
                start_time: '1900',
                end_time: '2150',
                days: 'W',
                is_closed: '0',
                is_canceled: '0',
                building: 'CGC',
                room: '1302',
                instructor: 'Brownell,John',
                instructor_id: '165',
                note: '',
                last_updated: '2010-01-17 21:37:52'
             }
          }
       ]
    }

#### Example XML response for [/v1/course/MUSIC/100/schedule.xml](http://api.uwdata.ca/v1/course/MUSIC/100/schedule.xml):
#### Example XML response for [/v1/course/6944/schedule.xml](http://api.uwdata.ca/v1/course/6944/schedule.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <classes>
        <class>
          <class_number>2986</class_number>
          <term>1105</term>
          <faculty_acronym>MUSIC</faculty_acronym>
          <course_number>100</course_number>

          <component_section>LEC 001</component_section>
          <campus_location>CGC   G</campus_location>
          <associated_class>1</associated_class>
          <related_component_1>0</related_component_1>
          <related_component_2>0</related_component_2>
          <enrollment_cap>70</enrollment_cap>

          <enrollment_total>0</enrollment_total>
          <wait_cap>0</wait_cap>
          <wait_tot>0</wait_tot>
          <tba_schedule>0</tba_schedule>
          <start_time>1900</start_time>
          <end_time>2150</end_time>

          <days>W</days>
          <is_closed>0</is_closed>
          <is_canceled>0</is_canceled>
          <building>CGC</building>
          <room>1302</room>
          <instructor>Brownell,John</instructor>

          <instructor_id>165</instructor_id>
          <note/>
          <last_updated>2010-01-17 21:37:52</last_updated>
        </class>
      </classes>
    </result>



### v1/course/search.[xml|json|csv] {#course_search}

Search the course list by title and description.

#### Required parameters:

* `q` The query to perform. Examples: `ENGL 408C` or `psychology`

#### Optional parameters:

* `cal` The calendar years to check. Default: the latest calendar year. Example: `&cal=20072008`.

#### Example JSON response for [/v1/course/search.json?q=psych](http://api.uwdata.ca/v1/course/search.json?q=psych):

    {
       page_index: '0',
       results_per_page: '10',
       page_result_count: 8,
       total_result_count: '8',
       courses: [
          {
             course: {
                cid: '7865',
                faculty_acronym: 'PSYCH',
                course_number: '101',
                title: 'Introductory Psychology',
                description: 'A general survey course designed to provide the student with an understanding of the basic concepts and techniques of modern psychology as a behavioural science. The combination of PSYCH 120R and 121R is cross-listed with PSYCH 101. [Offered: F,W,S.]',
                has_lec: '1',
                has_lab: '0',
                has_tst: '0',
                has_tut: '0',
                has_prj: '0',
                credit_value: '0.5',
                has_dist_ed: '1',
                only_dist_ed: '0',
                has_stj: '1',
                only_stj: '0',
                has_ren: '0',
                only_ren: '0',
                has_cgr: '0',
                only_cgr: '0',
                needs_dept_consent: '0',
                needs_instr_consent: '0',
                avail_fall: '0',
                avail_winter: '0',
                avail_spring: '0',
                prereq_desc: '',
                antireq_desc: 'Antireq: PSYCH 121R',
                crosslist_desc: '',
                coreq_desc: '',
                note_desc: '',
                src_url: 'http://www.ucalendar.uwaterloo.ca/0910/COURSE/course-PSYCH.html#PSYCH101',
                last_updated: '2010-01-19 05:02:49'
             }
          },
    ...
          {
             course: {
                cid: '11499',
                faculty_acronym: 'MSCI',
                course_number: '422',
                title: 'Economic Impact of Technological Change and Entrepreneurship',
                description: 'This course is designed to analyse the impact of technological change and entrepreneurship at a firm and societal level, primarily in terms of the economic antecedents and consequences of new technology. The scope of the course ranges from the study of the determination of productivity and its effect on economic growth to the determination of innovative activity and performance. Prereq: (One of CIVE 292, ECON 101, ENVE 292, MSCI 261, SYDE 331) and (One of BIOL 460, CHE 220, CIVE 224, ECE 316, ECON 221, ENVE 224, ENVS 271, 277, 278, ISS 250R, KIN 222, MSCI 252, ME 202, MTE 201, NE 115, PSCI 214, PSYCH 292, REC 371, 371A, SOC 280, STAT 202, 204, 206, 211, 221, 231, 241, SYDE 214) and level at least 3A. [Offered: F]',
                has_lec: '1',
                has_lab: '0',
                has_tst: '0',
                has_tut: '0',
                has_prj: '0',
                credit_value: '0.5',
                has_dist_ed: '0',
                only_dist_ed: '0',
                has_stj: '0',
                only_stj: '0',
                has_ren: '0',
                only_ren: '0',
                has_cgr: '0',
                only_cgr: '0',
                needs_dept_consent: '0',
                needs_instr_consent: '0',
                avail_fall: '0',
                avail_winter: '0',
                avail_spring: '0',
                prereq_desc: 'Prereq: See course description for prerequisite details',
                antireq_desc: '',
                crosslist_desc: '',
                coreq_desc: '',
                note_desc: '',
                src_url: 'http://www.ucalendar.uwaterloo.ca/0910/COURSE/course-MSCI.html#MSCI422',
                last_updated: '2010-01-19 05:02:50'
             }
          }
       ]
    }

#### Example XML response for [/v1/course/search.xml?q=psych](http://api.uwdata.ca/v1/course/search.xml?q=psych):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <page_index>0</page_index>
      <results_per_page>10</results_per_page>
      <page_result_count>8</page_result_count>
      <total_result_count>8</total_result_count>
      <courses>
        <course>
          <cid>7865</cid>
          <faculty_acronym>PSYCH</faculty_acronym>
          <course_number>101</course_number>
          <title>Introductory Psychology</title>
          <description>A general survey course designed to provide the student with an understanding of the basic concepts and techniques of modern psychology as a behavioural science. The combination of PSYCH 120R and 121R is cross-listed with PSYCH 101. [Offered: F,W,S.]</description>
          <has_lec>1</has_lec>
          <has_lab>0</has_lab>
          <has_tst>0</has_tst>
          <has_tut>0</has_tut>
          <has_prj>0</has_prj>
          <credit_value>0.5</credit_value>
          <has_dist_ed>1</has_dist_ed>
          <only_dist_ed>0</only_dist_ed>
          <has_stj>1</has_stj>
          <only_stj>0</only_stj>
          <has_ren>0</has_ren>
          <only_ren>0</only_ren>
          <has_cgr>0</has_cgr>
          <only_cgr>0</only_cgr>
          <needs_dept_consent>0</needs_dept_consent>
          <needs_instr_consent>0</needs_instr_consent>
          <avail_fall>0</avail_fall>
          <avail_winter>0</avail_winter>
          <avail_spring>0</avail_spring>
          <prereq_desc/>
          <antireq_desc>Antireq: PSYCH 121R</antireq_desc>
          <crosslist_desc/>
          <coreq_desc/>
          <note_desc/>
          <src_url>http://www.ucalendar.uwaterloo.ca/0910/COURSE/course-PSYCH.html#PSYCH101</src_url>
          <last_updated>2010-01-19 05:02:49</last_updated>
        </course>
    ...
        <course>
          <cid>11499</cid>
          <faculty_acronym>MSCI</faculty_acronym>
          <course_number>422</course_number>
          <title>Economic Impact of Technological Change and Entrepreneurship</title>
          <description>This course is designed to analyse the impact of technological change and entrepreneurship at a firm and societal level, primarily in terms of the economic antecedents and consequences of new technology. The scope of the course ranges from the study of the determination of productivity and its effect on economic growth to the determination of innovative activity and performance. Prereq: (One of CIVE 292, ECON 101, ENVE 292, MSCI 261, SYDE 331) and (One of BIOL 460, CHE 220, CIVE 224, ECE 316, ECON 221, ENVE 224, ENVS 271, 277, 278, ISS 250R, KIN 222, MSCI 252, ME 202, MTE 201, NE 115, PSCI 214, PSYCH 292, REC 371, 371A, SOC 280, STAT 202, 204, 206, 211, 221, 231, 241, SYDE 214) and level at least 3A. [Offered: F]</description>
          <has_lec>1</has_lec>
          <has_lab>0</has_lab>
          <has_tst>0</has_tst>
          <has_tut>0</has_tut>
          <has_prj>0</has_prj>
          <credit_value>0.5</credit_value>
          <has_dist_ed>0</has_dist_ed>
          <only_dist_ed>0</only_dist_ed>
          <has_stj>0</has_stj>
          <only_stj>0</only_stj>
          <has_ren>0</has_ren>
          <only_ren>0</only_ren>
          <has_cgr>0</has_cgr>
          <only_cgr>0</only_cgr>
          <needs_dept_consent>0</needs_dept_consent>
          <needs_instr_consent>0</needs_instr_consent>
          <avail_fall>0</avail_fall>
          <avail_winter>0</avail_winter>
          <avail_spring>0</avail_spring>
          <prereq_desc>Prereq: See course description for prerequisite details</prereq_desc>
          <antireq_desc/>
          <crosslist_desc/>
          <coreq_desc/>
          <note_desc/>
          <src_url>http://www.ucalendar.uwaterloo.ca/0910/COURSE/course-MSCI.html#MSCI422</src_url>
          <last_updated>2010-01-19 05:02:50</last_updated>
        </course>
      </courses>
    </result>


### v1/prof/[professor id].[xml|json|csv] {#prof_by_id}

Detailed information about the given professor.

#### Example JSON response for [/v1/prof/4877.json](http://api.uwdata.ca/v1/prof/1409.json):

    {
       professor: {
          id: '1409',
          first_name: 'Larry',
          last_name: 'Smith',
          ratemyprof_id: '9845',
          number_of_ratings: '504',
          overall_quality: '4.3',
          ease: '3',
          last_updated: '2010-01-17 18:13:59'
       }
    }

#### Example XML response for [/v1/prof/1409.xml](http://api.uwdata.ca/v1/prof/1409.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <professor>
        <id>1409</id>
        <first_name>Larry</first_name>
        <last_name>Smith</last_name>
        <ratemyprof_id>9845</ratemyprof_id>
        <number_of_ratings>504</number_of_ratings>
        <overall_quality>4.3</overall_quality>
        <ease>3</ease>
        <last_updated>2010-01-17 18:13:59</last_updated>
      </professor>
    </result>


### v1/prof/[professor id]/timeslots.[xml|json|csv] {#prof_timeslots}

All of the class timeslots the given professor is running.

#### Optional parameters:

* `term` The academic term id to check. Default: the latest academic term. Example: `&term=1101`.

#### Example JSON response for [/v1/course/1409/timeslots.json](http://api.uwdata.ca/v1/course/1409/timeslots.json):

    {
       timeslots: [
          {
             timeslot: {
                class_number: '2154',
                term: '1105',
                faculty_acronym: 'ECON',
                course_number: '102',
                component_section: 'LEC 002',
                campus_location: 'UW    U',
                associated_class: '2',
                related_component_1: '101',
                related_component_2: '0',
                enrollment_cap: '370',
                enrollment_total: '0',
                wait_cap: '0',
                wait_tot: '0',
                tba_schedule: '0',
                start_time: '1900',
                end_time: '2150',
                days: 'T',
                is_closed: '0',
                is_canceled: '0',
                building: 'RCH',
                room: '101',
                instructor: 'Smith,Larry',
                instructor_id: '1409',
                note: '',
                __last_touched: '2010-01-17 21:36:32',
                reserves: {
                   reserves: [
                      {
                         reserve: {
                            enrollment_cap: '50',
                            enrollment_total: '0',
                            instructor: null,
                            is_closed: '0',
                            reserve_group: 'Year 1 Arts students',
                            start_time: null,
                            end_time: null,
                            days: null,
                            building: null,
                            room: null,
                            __last_touched: '2010-01-17 21:36:32'
                         }
                      }
                   ]
                }
             }
          }
       ]
    }

#### Example XML response for [/v1/course/1409/timeslots.xml](http://api.uwdata.ca/v1/course/1409/timeslots.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <timeslots>
        <timeslot>
          <class_number>2154</class_number>
          <term>1105</term>
          <faculty_acronym>ECON</faculty_acronym>
          <course_number>102</course_number>
          <component_section>LEC 002</component_section>
          <campus_location>UW    U</campus_location>
          <associated_class>2</associated_class>
          <related_component_1>101</related_component_1>
          <related_component_2>0</related_component_2>
          <enrollment_cap>370</enrollment_cap>
          <enrollment_total>0</enrollment_total>
          <wait_cap>0</wait_cap>
          <wait_tot>0</wait_tot>
          <tba_schedule>0</tba_schedule>
          <start_time>1900</start_time>
          <end_time>2150</end_time>
          <days>T</days>
          <is_closed>0</is_closed>
          <is_canceled>0</is_canceled>
          <building>RCH</building>
          <room>101</room>
          <instructor>Smith,Larry</instructor>
          <instructor_id>1409</instructor_id>
          <note/>
          <__last_touched>2010-01-17 21:36:32</__last_touched>
          <reserves>
            <reserve>
              <enrollment_cap>50</enrollment_cap>
              <enrollment_total>0</enrollment_total>
              <instructor/>
              <is_closed>0</is_closed>
              <reserve_group>Year 1 Arts students</reserve_group>
              <start_time/>
              <end_time/>
              <days/>
              <building/>
              <room/>
              <__last_touched>2010-01-17 21:36:32</__last_touched>
            </reserve>
          </reserves>
        </timeslot>
      </timeslots>
    </result>


### v1/prof/search.[xml|json|csv] {#prof_search}

Search the professor list by first and last name.

#### Required parameters:

* `q` The query to perform. Examples: `Larry Smith` or `Ragde`

#### Optional parameters:

* `term` The academic term id to check. Default: the latest academic term. Example: `&term=1101`.

#### Example JSON response for [/v1/prof/search.json?q=ragde](http://api.uwdata.ca/v1/prof/search.json?q=ragde):

    {
       page_index: '0',
       results_per_page: '10',
       page_result_count: 1,
       total_result_count: '1',
       professors: [
          {
             professor: {
                id: '1238',
                first_name: 'Prabhakar',
                last_name: 'Ragde',
                ratemyprof_id: '10000',
                number_of_ratings: '92',
                overall_quality: '3.4',
                ease: '2.4',
                last_updated: '2010-01-17 18:13:52'
             }
          }
       ]
    }

#### Example XML response for [/v1/prof/search.xml?q=ragde](http://api.uwdata.ca/v1/prof/search.xml?q=ragde):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <page_index>0</page_index>
      <results_per_page>10</results_per_page>
      <page_result_count>1</page_result_count>
      <total_result_count>1</total_result_count>
      <professors>
        <professor>

          <id>1238</id>
          <first_name>Prabhakar</first_name>
          <last_name>Ragde</last_name>
          <ratemyprof_id>10000</ratemyprof_id>
          <number_of_ratings>92</number_of_ratings>
          <overall_quality>3.4</overall_quality>

          <ease>2.4</ease>
          <last_updated>2010-01-17 18:13:52</last_updated>
        </professor>
      </professors>
    </result>


### v1/term/list.[xml|json|csv] {#term_list}

A list of all known academic terms.

#### Example JSON response for [/v1/term/list.json](http://api.uwdata.ca/v1/term/list.json):

    {
       terms: [
          {
             term: {
                term_id: '1095',
                term_season: 'Spring',
                term_year: '2009',
                last_updated: '2010-01-17 21:35:01'
             }
          },
          {
             term: {
                term_id: '1099',
                term_season: 'Fall',
                term_year: '2009',
                last_updated: '2010-01-17 21:35:01'
             }
          },
          {
             term: {
                term_id: '1101',
                term_season: 'Winter',
                term_year: '2010',
                last_updated: '2010-01-17 21:35:01'
             }
          },
          {
             term: {
                term_id: '1105',
                term_season: 'Spring',
                term_year: '2010',
                last_updated: '2010-01-17 21:35:01'
             }
          }
       ]
    }

#### Example XML response for [/v1/term/list.xml](http://api.uwdata.ca/v1/term/list.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <terms>
        <term>
          <term_id>1095</term_id>
          <term_season>Spring</term_season>
          <term_year>2009</term_year>
          <last_updated>2010-01-17 21:35:01</last_updated>
        </term>
        <term>
          <term_id>1099</term_id>
          <term_season>Fall</term_season>
          <term_year>2009</term_year>
          <last_updated>2010-01-17 21:35:01</last_updated>
        </term>
        <term>
          <term_id>1101</term_id>
          <term_season>Winter</term_season>
          <term_year>2010</term_year>
          <last_updated>2010-01-17 21:35:01</last_updated>
        </term>
        <term>
          <term_id>1105</term_id>
          <term_season>Spring</term_season>
          <term_year>2010</term_year>
          <last_updated>2010-01-17 21:35:01</last_updated>
        </term>
      </terms>
    </result>


### v1/weather/current.[xml|json|csv] {#current_weather}

The current weather, updated every 15 minutes.

#### Example JSON response for [/v1/weather/current.json](http://api.uwdata.ca/v1/weather/current.json):

    {
      "temp": "2",
      "temp_24hourmax": "13.9",
      "timestamp": "2010-11-09 21:00:00",
      "barometric_direction": "Rising",
      "barometric_pressure": "102",
      "wind_speed": "0",
      "temp_24hourmin": "-4",
      "relative_humidity": "100",
      "dew_point": "1.9",
      "incoming_radiation": "0",
      "wind_direction": "NE"
    }

#### Example XML response for [/v1/weather/current.xml](http://api.uwdata.ca/v1/weather/current.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <timestamp>2010-11-09 21:00:00</timestamp>
      <temp>2</temp>
      <temp_24hourmax>13.9</temp_24hourmax>
      <temp_24hourmin>-4</temp_24hourmin>
      <relative_humidity>100</relative_humidity>
      <dew_point>1.9</dew_point>
      <wind_speed>0</wind_speed>
      <wind_direction>NE</wind_direction>
      <barometric_pressure>102</barometric_pressure>
      <barometric_direction>Rising</barometric_direction>
      <incoming_radiation>0</incoming_radiation>
    </result>



### v1/geo/building/list.[xml|json|csv] {#building_list}

Geographical location of all buildings on campus.

#### Example JSON response for [/v1/geo/building/list.json](http://api.uwdata.ca/v1/geo/building/list.json):

    {
      "buildings": [
        {
          "building": {
            "name": "Mathematics and Computer",
            "lng": "-80.5439376831055",
            "short_name": "MC",
            "lat": "43.4719657897949"
          }
        },
        ...
        {
          "building": {
            "name": "Commissary (UW Police & Parking)",
            "lng": "-80.5428085327148",
            "short_name": "COM",
            "lat": "43.4741401672363"
          }
        }
      ]
    }

#### Example XML response for [/v1/geo/building/list.xml](http://api.uwdata.ca/v1/geo/building/list.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <buildings>
        <building>
          <name>Mathematics and Computer</name>
          <short_name>MC</short_name>
          <lat>43.4719657897949</lat>
          <lng>-80.5439376831055</lng>
        </building>
        ...
        <building>
          <name>Commissary (UW Police&amp;Parking)</name>
          <short_name>COM</short_name>
          <lat>43.4741401672363</lat>
          <lng>-80.5428085327148</lng>
        </building>
      </buildings>
    </result>


### v1/geo/parking/list.[xml|json|csv] {#parking_list}

Geographical location of all parking lots on campus with cost per hour/daily cost.

#### Example JSON response for [/v1/geo/parking/list.json](http://api.uwdata.ca/v1/geo/parking/list.json):

    {
      "parking_lots": [
        {
          "lot": {
            "name": "N",
            "after5_cost": null,
            "lng": "-80.5446",
            "weekend_cost": null,
            "type": "visitor",
            "lat": "43.474847",
            "hourly_cost": null,
            "max_cost": "3",
            "payment_type": "payanddisplay"
          }
        },
        ...
        {
          "lot": {
            "name": "UWP",
            "after5_cost": null,
            "lng": "-80.53373",
            "weekend_cost": null,
            "type": "visitor",
            "lat": "43.471297",
            "hourly_cost": null,
            "max_cost": "3",
            "payment_type": "payanddisplay"
          }
        }
      ]
    }

#### Example XML response for [/v1/geo/parking/list.xml](http://api.uwdata.ca/v1/geo/parking/list.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <parking_lots>
        <lot>
          <name>N</name>
          <type>visitor</type>
          <payment_type>payanddisplay</payment_type>
          <max_cost>3</max_cost>
          <hourly_cost/>
          <weekend_cost/>
          <after5_cost/>
          <lat>43.474847</lat>
          <lng>-80.5446</lng>
        </lot>
        ...
        <lot>
          <name>UWP</name>
          <type>visitor</type>
          <payment_type>payanddisplay</payment_type>
          <max_cost>3</max_cost>
          <hourly_cost/>
          <weekend_cost/>
          <after5_cost/>
          <lat>43.471297</lat>
          <lng>-80.53373</lng>
        </lot>
      </parking_lots>
    </result>




### v1/dump/courses.[xml|json|csv] {#dump_courses}

A straight dump of all course information for a given calendar year.

#### Optional parameters:

* `cal` The calendar years to dump. Default: the latest calendar year. Example: `&cal=20072008`.

#### Example JSON response for [/v1/dump/courses.json](http://api.uwdata.ca/v1/dump/courses.json):

    {
      "courses": [
        {
          "course": {
            "antireq_desc": "",
            "avail_spring": "0",
            "has_cgr": "0",
            "note_desc": "",
            "prereq_desc": "Prereq: OPTOM 342A; Optometry students only",
            "avail_winter": "0",
            "faculty_acronym": "OPTOM",
            "src_url": "http://www.ucalendar.uwaterloo.ca/1011/COURSE/course-OPTOM.html#OPTOM342B",
            "avail_fall": "0",
            "has_ren": "0",
            "only_stj": "0",
            "title": "Case Analysis and Optometric Therapies 2",
            "has_prj": "0",
            "has_tut": "1",
            "needs_dept_consent": "0",
            "has_stj": "0",
            "course_number": "342B",
            "needs_instr_consent": "0",
            "only_cgr": "0",
            "only_dist_ed": "0",
            "has_tst": "0",
            "has_lec": "1",
            "description": "A continuation of Optometry 342A. Emphasis is placed on the differential diagnostic method of analyzing clinical data with special emphasis on refractive and binocular vision conditions.",
            "cid": "10389",
            "crosslist_desc": "",
            "credit_value": "0.5",
            "coreq_desc": "",
            "only_ren": "0",
            "has_dist_ed": "0",
            "has_lab": "0"
          }
        },
        ...
        {
          "course": {
            "antireq_desc": "",
            "avail_spring": "0",
            "has_cgr": "0",
            "note_desc": "",
            "prereq_desc": "Prereq: Level at least 3A Science and Business students",
            "avail_winter": "0",
            "faculty_acronym": "SCBUS",
            "src_url": "http://www.ucalendar.uwaterloo.ca/1011/COURSE/course-SCBUS.html#SCBUS425",
            "avail_fall": "0",
            "has_ren": "0",
            "only_stj": "0",
            "title": "Science & Business Workshop 6",
            "has_prj": "0",
            "has_tut": "0",
            "needs_dept_consent": "0",
            "has_stj": "0",
            "course_number": "425",
            "needs_instr_consent": "0",
            "only_cgr": "0",
            "only_dist_ed": "0",
            "has_tst": "0",
            "has_lec": "1",
            "description": "This workshop addresses the implications for Canadian science and technology based firms of competing in the global competitive environment. [Offered: W]",
            "cid": "12257",
            "crosslist_desc": "",
            "credit_value": "0.5",
            "coreq_desc": "",
            "only_ren": "0",
            "has_dist_ed": "0",
            "has_lab": "1"
          }
        }
      ]
    }

#### Example XML response for [/v1/dump/courses.xml](http://api.uwdata.ca/v1/dump/courses.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <courses>
        <course>
          <cid>10389</cid>
          <faculty_acronym>OPTOM</faculty_acronym>
          <course_number>342B</course_number>
          <title>Case Analysis and Optometric Therapies 2</title>
          <description>A continuation of Optometry 342A. Emphasis is placed on the differential diagnostic method of analyzing clinical data with special emphasis on refractive and binocular vision conditions.</description>
          <has_lec>1</has_lec>
          <has_lab>0</has_lab>
          <has_tst>0</has_tst>
          <has_tut>1</has_tut>
          <has_prj>0</has_prj>
          <credit_value>0.5</credit_value>
          <has_dist_ed>0</has_dist_ed>
          <only_dist_ed>0</only_dist_ed>
          <has_stj>0</has_stj>
          <only_stj>0</only_stj>
          <has_ren>0</has_ren>
          <only_ren>0</only_ren>
          <has_cgr>0</has_cgr>
          <only_cgr>0</only_cgr>
          <needs_dept_consent>0</needs_dept_consent>
          <needs_instr_consent>0</needs_instr_consent>
          <avail_fall>0</avail_fall>
          <avail_winter>0</avail_winter>
          <avail_spring>0</avail_spring>
          <prereq_desc>Prereq: OPTOM 342A; Optometry students only</prereq_desc>
          <antireq_desc/>
          <crosslist_desc/>
          <coreq_desc/>
          <note_desc/>
          <src_url>http://www.ucalendar.uwaterloo.ca/1011/COURSE/course-OPTOM.html#OPTOM342B</src_url>
        </course>
        ...
        <course>
          <cid>12257</cid>
          <faculty_acronym>SCBUS</faculty_acronym>
          <course_number>425</course_number>
          <title>Science&amp;Business Workshop 6</title>
          <description>This workshop addresses the implications for Canadian science and technology based firms of competing in the global competitive environment. [Offered: W]</description>
          <has_lec>1</has_lec>
          <has_lab>1</has_lab>
          <has_tst>0</has_tst>
          <has_tut>0</has_tut>
          <has_prj>0</has_prj>
          <credit_value>0.5</credit_value>
          <has_dist_ed>0</has_dist_ed>
          <only_dist_ed>0</only_dist_ed>
          <has_stj>0</has_stj>
          <only_stj>0</only_stj>
          <has_ren>0</has_ren>
          <only_ren>0</only_ren>
          <has_cgr>0</has_cgr>
          <only_cgr>0</only_cgr>
          <needs_dept_consent>0</needs_dept_consent>
          <needs_instr_consent>0</needs_instr_consent>
          <avail_fall>0</avail_fall>
          <avail_winter>0</avail_winter>
          <avail_spring>0</avail_spring>
          <prereq_desc>Prereq: Level at least 3A Science and Business students</prereq_desc>
          <antireq_desc/>
          <crosslist_desc/>
          <coreq_desc/>
          <note_desc/>
          <src_url>http://www.ucalendar.uwaterloo.ca/1011/COURSE/course-SCBUS.html#SCBUS425</src_url>
        </course>
      </courses>
    </result>


### v1/dump/schedules.[xml|json|csv] {#dump_schedules}

A straight dump of all schedule information for a given term.

#### Optional parameters:

* `term` The academic term id to check. Default: the latest academic term. Example: `&term=1101`.
* `since` The earliest time stamp of data to return. Default: return all data. Example: `&since=2010-11-04%2014:00:08` Please note the %20 to indicate a space between the date and time.

#### Example JSON response for [/v1/dump/schedules.json](http://api.uwdata.ca/v1/dump/schedules.json):

    {
      "schedule": [
        {
          "class": {
            "instructor": "",
            "building": "MC",
            "enrollment_total": "179",
            "associated_class": "1",
            "end_time": "1620",
            "campus_location": "UW    U",
            "faculty_acronym": "ACTSC",
            "title": "Mathematics of Finance",
            "days": "M",
            "tba_schedule": "0",
            "component_section": "TUT 101",
            "term": "1111",
            "is_canceled": "0",
            "is_closed": "0",
            "enrollment_cap": "190",
            "wait_tot": "0",
            "course_number": "231",
            "last_updated": "2010-11-03 20:00:16",
            "note": "",
            "related_component_1": "0",
            "class_number": "5026",
            "related_component_2": "0",
            "instructor_id": "0",
            "room": "2065",
            "start_time": "1530",
            "wait_cap": "0"
          }
        },
        ...
        {
          "class": {
            "instructor": "Muszynski,Alicja",
            "building": "",
            "enrollment_total": "0",
            "associated_class": "1",
            "end_time": "0",
            "campus_location": "UW    U",
            "faculty_acronym": "WS",
            "title": "Senior Honours Thesis",
            "days": "",
            "tba_schedule": "1",
            "component_section": "RDG 001",
            "term": "1111",
            "is_canceled": "0",
            "is_closed": "0",
            "enrollment_cap": "1",
            "wait_tot": "0",
            "course_number": "499B",
            "last_updated": "2010-06-21 08:08:36",
            "note": "",
            "related_component_1": "0",
            "class_number": "6890",
            "related_component_2": "0",
            "instructor_id": "0",
            "room": "",
            "start_time": "0",
            "wait_cap": "0"
          }
        }
      ]
    }

#### Example XML response for [/v1/dump/schedules.xml](http://api.uwdata.ca/v1/dump/schedules.xml):

    <?= '<?xml version="1.0" encoding="UTF-8"?>'."\n" ?>
    <result>
      <schedule>
        <class>
          <class_number>5026</class_number>
          <term>1111</term>
          <faculty_acronym>ACTSC</faculty_acronym>
          <course_number>231</course_number>
          <component_section>TUT 101</component_section>
          <campus_location>UW    U</campus_location>
          <associated_class>1</associated_class>
          <related_component_1>0</related_component_1>
          <related_component_2>0</related_component_2>
          <enrollment_cap>190</enrollment_cap>
          <enrollment_total>179</enrollment_total>
          <wait_cap>0</wait_cap>
          <wait_tot>0</wait_tot>
          <tba_schedule>0</tba_schedule>
          <start_time>1530</start_time>
          <end_time>1620</end_time>
          <days>M</days>
          <is_closed>0</is_closed>
          <is_canceled>0</is_canceled>
          <building>MC</building>
          <room>2065</room>
          <instructor/>
          <instructor_id>0</instructor_id>
          <note/>
          <last_updated>2010-11-03 20:00:16</last_updated>
          <title>Mathematics of Finance</title>
        </class>
        ...
        <class>
          <class_number>6890</class_number>
          <term>1111</term>
          <faculty_acronym>WS</faculty_acronym>
          <course_number>499B</course_number>
          <component_section>RDG 001</component_section>
          <campus_location>UW    U</campus_location>
          <associated_class>1</associated_class>
          <related_component_1>0</related_component_1>
          <related_component_2>0</related_component_2>
          <enrollment_cap>1</enrollment_cap>
          <enrollment_total>0</enrollment_total>
          <wait_cap>0</wait_cap>
          <wait_tot>0</wait_tot>
          <tba_schedule>1</tba_schedule>
          <start_time>0</start_time>
          <end_time>0</end_time>
          <days/>
          <is_closed>0</is_closed>
          <is_canceled>0</is_canceled>
          <building/>
          <room/>
          <instructor>Muszynski,Alicja</instructor>
          <instructor_id>0</instructor_id>
          <note/>
          <last_updated>2010-06-21 08:08:36</last_updated>
          <title>Senior Honours Thesis</title>
        </class>
      </schedule>
    </result>

#### Example CSV response for [/v1/dump/schedules.csv](http://api.uwdata.ca/v1/dump/schedules.csv):

    class_number,term,faculty_acronym,course_number,component_section,campus_location,associated_class,related_component_1,related_component_2,enrollment_cap,enrollment_total,wait_cap,wait_tot,tba_schedule,start_time,end_time,days,is_closed,is_canceled,building,room,instructor,instructor_id,note,last_updated,title
    5026,1111,ACTSC,231,"TUT 101","UW    U",1,0,0,190,179,0,0,0,1530,1620,M,0,0,MC,2065,,0,,"2010-11-03 20:00:16","Mathematics of Finance"
    5025,1111,ACTSC,231,"LEC 001","UW    U",1,101,0,190,179,0,0,0,1330,1420,MWF,0,0,MC,2065,"Weng,Chengguo",0,,"2010-11-03 20:00:16","Mathematics of Finance"
    5217,1111,ACTSC,232,"TUT 101","UW    U",1,0,0,190,190,0,0,0,1430,1520,M,0,0,MC,2066,,0,,"2010-11-04 13:00:08","Introduction to Actuarial Mathematics"
    5027,1111,ACTSC,232,"LEC 001","UW    U",1,101,0,190,190,0,0,0,1000,1120,TT,0,0,MC,2066,,0,,"2010-11-04 13:00:08","Introduction to Actuarial Mathematics"
    5028,1111,ACTSC,331,"LEC 001","UW    U",1,101,0,120,118,0,0,0,1430,1520,MWF,0,0,MC,4061,"Freeland,Robert Keith",0,,"2010-11-01 20:00:04","Life Contingencies 1"
    5218,1111,ACTSC,331,"TUT 101","UW    U",1,0,0,120,118,0,0,0,1530,1620,W,0,0,MC,4061,,0,,"2010-11-01 20:00:04","Life Contingencies 1"
    5271,1111,ACTSC,371,"LEC 001","UW    U",1,101,0,215,215,0,0,0,930,1020,MWF,0,0,AL,113,"Wood,Peter J",0,,"2010-11-04 08:00:53","Corporate Finance 1"
    5272,1111,ACTSC,371,"TUT 101","UW    U",1,0,0,215,215,0,0,0,1630,1720,M,0,0,DC,1351,,0,,"2010-11-04 08:00:54","Corporate Finance 1"
    5264,1111,ACTSC,372,"LEC 001","UW    U",1,101,0,225,207,0,0,0,2430,120,MWF,0,0,DC,1351,"Wood,Peter J",0,,"2010-11-02 08:00:24","Corporate Finance 2"
    5265,1111,ACTSC,372,"TUT 101","UW    U",1,0,0,225,207,0,0,0,1730,1820,M,0,0,DC,1351,,0,,"2010-11-02 08:00:24","Corporate Finance 2"
    5029,1111,ACTSC,433,"LEC 001","UW    U",1,0,0,132,74,0,0,0,1300,1420,TT,0,0,MC,1085,"Zou,Xiaorong",0,,"2010-10-29 14:00:19","Analysis of Survival Data"
    5030,1111,ACTSC,446,"LEC 001","UW    U",1,101,0,130,97,0,0,0,1430,1550,TT,0,0,MC,2066,"Zou,Xiaorong",0,,"2010-11-03 16:00:13",
    5534,1111,ACTSC,446,"LEC 002","UW    U",2,102,0,100,99,0,0,0,1130,1250,TT,0,0,MC,4059,"Freeland,Robert Keith",0,,"2010-11-01 14:00:13",

[^1]: Real time here means once an hour, between the hours of 8am and 8pm.
