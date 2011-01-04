<?php

date_default_timezone_set('EST');

define('COURSE_CAL_ROOT_URL', 'http://ugradcalendar.uwaterloo.ca/');
define('GRAD_COURSE_CAL_ROOT_URL', 'http://gradcalendar.uwaterloo.ca/');

$calendar_urls = array(
  '20102011' => 'http://ugradcalendar.uwaterloo.ca',
  '20092010' => 'http://ugradcalendar.uwaterloo.ca/?ActiveDate=9/1/2009',
  '20082009' => 'http://ugradcalendar.uwaterloo.ca/?ActiveDate=9/1/2008',
  '20072008' => 'http://ugradcalendar.uwaterloo.ca/?ActiveDate=9/1/2007',
  '20062007' => 'http://www.ucalendar.uwaterloo.ca/0607',
  '20052006' => 'http://www.ucalendar.uwaterloo.ca/0506',
  '20042005' => 'http://www.ucalendar.uwaterloo.ca/0405',
  '20032004' => 'http://www.ucalendar.uwaterloo.ca/0304',
  '20022003' => 'http://www.ucalendar.uwaterloo.ca/0203',
  '20012002' => 'http://www.ucalendar.uwaterloo.ca/0102'
  //'20002001' => 'http://ugradcalendar.uwaterloo.ca/?pageID=10308',
);

//Grad calendars technically come in 3 per term, but the courses are identical
$grad_calendar_urls = array(
    '20102011'   => 'http://gradcalendar.uwaterloo.ca',
    '20092010'   => 'http://gradcalendar.uwaterloo.ca/?ActiveDate=09/01/2009',
    '20082009'   => 'http://gradcalendar.uwaterloo.ca/?ActiveDate=09/01/2008',
    '20072008'   => 'http://gradcalendar.uwaterloo.ca/?ActiveDate=09/01/2007',
    '20062007'   => 'http://gradcalendar.uwaterloo.ca/?ActiveDate=09/01/2006'
);


$schema = <<<SCHEMA
CREATE TABLE IF NOT EXISTS `courses` (
  `cid` int(10) unsigned NOT NULL,
  `faculty_acronym` varchar(10) NOT NULL,
  `course_number` varchar(6) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `has_lec` tinyint(1) NOT NULL,
  `has_lab` tinyint(1) NOT NULL,
  `has_tst` tinyint(1) NOT NULL,
  `has_tut` tinyint(1) NOT NULL,
  `has_prj` tinyint(1) NOT NULL,
  `credit_value` float NOT NULL,
  `has_dist_ed` tinyint(1) NOT NULL,
  `only_dist_ed` tinyint(1) NOT NULL,
  `has_stj` tinyint(1) NOT NULL,
  `only_stj` tinyint(1) NOT NULL,
  `has_ren` tinyint(1) NOT NULL,
  `only_ren` tinyint(1) NOT NULL,
  `has_cgr` tinyint(1) NOT NULL,
  `only_cgr` tinyint(1) NOT NULL,
  `is_grad` tinyint(1) NOT NULL,
  `needs_dept_consent` tinyint(1) NOT NULL,
  `needs_instr_consent` tinyint(1) NOT NULL,
  `avail_fall` tinyint(1) NOT NULL,
  `avail_winter` tinyint(1) NOT NULL,
  `avail_spring` tinyint(1) NOT NULL,
  `prereq_desc` text NOT NULL,
  `antireq_desc` text NOT NULL,
  `crosslist_desc` text NOT NULL,
  `coreq_desc` text NOT NULL,
  `note_desc` text NOT NULL,
  `src_url` varchar(100) NOT NULL,
  `prereqs` text NOT NULL,
  `__last_touched` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`cid`),
  UNIQUE KEY `faculty_acronym` (`faculty_acronym`,`course_number`),
  FULLTEXT KEY `title` (`title`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

CREATE TABLE IF NOT EXISTS `faculties` (
  `acronym` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `__last_touched` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`acronym`),
  KEY `name` (`name`),
  FULLTEXT KEY `name_2` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SCHEMA;
