<?php

define('COURSE_CAL_ROOT_URL', 'http://ugradcalendar.uwaterloo.ca/');

$calendar_urls = array(
  '20092010' => 'http://ugradcalendar.uwaterloo.ca/',
  '20082009' => 'http://ugradcalendar.uwaterloo.ca/?pageID=10300',
  '20072008' => 'http://ugradcalendar.uwaterloo.ca/?pageID=10301',
  '20062007' => 'http://ugradcalendar.uwaterloo.ca/?pageID=10302',
  '20052006' => 'http://ugradcalendar.uwaterloo.ca/?pageID=10303',
  '20042005' => 'http://ugradcalendar.uwaterloo.ca/?pageID=10304',
  '20032004' => 'http://ugradcalendar.uwaterloo.ca/?pageID=10305',
  '20022003' => 'http://ugradcalendar.uwaterloo.ca/?pageID=10306',
  '20012002' => 'http://ugradcalendar.uwaterloo.ca/?pageID=10307',
  //'20002001' => 'http://ugradcalendar.uwaterloo.ca/?pageID=10308',
);


$schema = <<<SCHEMA
CREATE TABLE IF NOT EXISTS `courses` (
  `cid` int(10) unsigned NOT NULL,
  `faculty_acronym` varchar(10) NOT NULL,
  `course_number` varchar(6) NOT NULL,
  `title` text NOT NULL,
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
  `src_url` text NOT NULL,
  `prereqs` text NOT NULL,
  `__last_touched` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`cid`),
  UNIQUE KEY `faculty_acronym` (`faculty_acronym`,`course_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

CREATE TABLE IF NOT EXISTS `faculties` (
  `acronym` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `__last_touched` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`acronym`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

SCHEMA;
