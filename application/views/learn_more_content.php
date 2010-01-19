<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
Let's turn <em>data</em> into <em>features.</em>
================================================

Version 1 of the uwdata API covers the following data sets:

* Course Calendar information back to the 2001-2002 school year.
* Course scheduling for current terms, updated in real time.[^1]
* Professor information, including ratemyprofessors.com scores for ease and quality.
* Course prerequisites parsed in a prefix-order format, allowing easy calculation of satisfaction.

The API is accessed via a set of URLs that are outlined below.

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


### v1/faculty/[faculty acronym]/courses.[xml|json] {#faculty_courses}

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


### v1/course/[faculty acronym]/[course number].[xml|json] {#course_by_number}
### v1/course/[course id].[xml|json] {#course_by_id}

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


### v1/course/[faculty acronym]/[course number]/prereqs.[xml|json] {#course_prereqs_by_number}
### v1/course/[course id]/prereqs.[xml|json] {#course_prereqs_by_id}

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


### v1/course/[faculty acronym]/[course number]/schedule.[xml|json] {#course_schedule_by_number}
### v1/course/[course id]/schedule.[xml|json] {#course_schedule_by_id}

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



### v1/course/search.[xml|json] {#course_search}

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


### v1/prof/[professor id].[xml|json] {#prof_by_id}

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


### v1/prof/[professor id]/timeslots.[xml|json] {#prof_timeslots}

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


### v1/prof/search.[xml|json] {#prof_search}

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


### v1/term/list.[xml|json] {#term_list}

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

[^1]: Real time here means once an hour, between the hours of 8am and 8pm.

