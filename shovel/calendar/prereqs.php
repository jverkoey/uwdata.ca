<?php

// UW course prereqs calculation

include_once COMMON_PATH.'db.php';
include_once COMMON_PATH.'scraper_tools.php';
include_once COMMON_PATH.'simple_html_dom.php';
include_once COMMON_PATH.'Database.class.php';

define('WORD_TYPE_FACULTY', 1);
define('WORD_TYPE_CNUM', 2);

if( sizeof($argv) < 2 ) {
  $calendar_years = '20092010';
} else {
  $calendar_years = $argv[1];
}

if (!isset($calendar_urls[$calendar_years])) {
  echo "Unknown calendar years: $calendar_years\n";
  echo "Try something like 20092010 or 20042005\n";
  exit;
}

$calendar_url = $calendar_urls[$calendar_years];

$dbName = 'uwdata_'.str_replace('-', '', $calendar_years);

echo 'Calculating prereqs from the '.$calendar_years.' calendar year'."\n";
echo '  db: '.$dbName."\n";

///////////////////////////////////////////////////////////////////////////////////////////////////
// Now that we know which calendar year we're working with, let's ensure that the tables exist.
///////////////////////////////////////////////////////////////////////////////////////////////////
$db = new Database(DB_HOST, DB_USER, DB_PASS, $dbName);
$db->connect();

// Create the schemas.
foreach (explode(';', trim($schema, ';')) as $query) {
  if (trim($query)) {
    $db->query($query);
  }
}

function get_cid($acr, $num) {
  global $db;
  $results = $db->query('SELECT cid FROM courses WHERE faculty_acronym LIKE "'.mysql_escape_string($acr).'" AND course_number = "'.mysql_escape_string($num).'";');
  while ($row = mysql_fetch_assoc($results)) {
    return $row['cid'];
  }
  return null;
}

class course {
  public $fac = null;
  public $num = null;
  //public $cid = null;

  public function __construct($fac, $num) {
    $this->fac = $fac;
    $this->num = $num;
    //$this->cid = get_cid($fac, $num);
  }
}

function parse_part($part) {
  //echo 'Parsing part: '.$part."\n";
  // Let's start breaking down the reqs into logical units.
  // XXX ###(, ###, ###, ...)
  //    Example: PHARM 129, 131, 220
  //    Means: You must take all three courses.
  // XXX ### or ###
  //    Example: AFM 101 or 128
  //    Means: You must take one or the other.

  $operators = array();
  $state_machine = 0;
  $depth = 0;
  $brackets = array();
  $grouped_operators = array();
  $in_word = false;
  $word_buffer = '';
  $last_faculty = null;
  $course_group = array();
  $group_operator = 'and';
  $last_operand = false;
  $last_number = null;
  $last_numerical_count = null;
  $is_pairing = false;
  $last_word_type = 0;

  $numbers = array(
    'one',
    'two',
    'three',
    'four',
    'five',
    'six',
    'seven',
    'eight',
    'nine'
  );

  $part = ' '.$part.' ';

  for ($ix = 0; $ix < strlen($part); ++$ix) {
    $letter = $part[$ix];
    if ($letter == '(') {
      ++$depth;
      $brackets []= $ix;
    } else if ($letter == ')') {
      --$depth;
      $start_bracket = array_pop($brackets);
      if (!$depth) {
        $sub_operators = parse_reqs(substr($part, $start_bracket + 1, $ix - $start_bracket - 1));
        $course_group = array_merge($course_group, $sub_operators);
      }
    } else if ($depth == 0) {
      if (eregi('[a-z0-9]', $letter)) {
        $word_buffer .= $letter;
        $in_word = true;
      } else if ($in_word) {
        if ($letter == ',') {
          //echo "found a comma...";
          if ($last_operand) {
            if (!empty($course_group)) {
              $operators []= array_merge(array($group_operator), $course_group);
              $course_group = array();
            }
            $last_operand = false;
          }
        }

        $new_word_type = 0;
        if (strtolower($word_buffer) == 'or') {
          if ($last_operand && $group_operator != strtolower($word_buffer)) {
            // We've already added the last value for the previous operator, so let's
            // group everything up to this point.
            $course_group = array(array($group_operator, $course_group));
          }
          //echo "found an or operator\n";
          $group_operator = 'or';
          $last_operand = true;

        } else if (strtolower($word_buffer) == 'and') {
          //echo "found an and operator\n";
          $group_operator = 'and';
          $last_operand = true;

        } else if (ereg('^[A-Z]+$', $word_buffer)) {
          //echo 'found a faculty: '.$word_buffer."\n";
          if ($is_pairing && $last_word_type == WORD_TYPE_FACULTY) {
            $last_faculty = array_merge((array)$last_faculty, (array)$word_buffer);
            $is_pairing = false;
          } else {
            $last_faculty = $word_buffer;
          }
          $new_word_type = WORD_TYPE_FACULTY;

        } else if (ereg('^[0-9]{2,}[A-Z]?$', $word_buffer) && $letter != '%') {
          foreach ((array)$last_faculty as $faculty) {
            //echo 'found a course: '.$faculty.' '.$word_buffer."\n";
            $course_group []= new course($faculty, $word_buffer);
          }

          if ($is_pairing) {
            $course2 = array_pop($course_group);
            $course1 = array_pop($course_group);

            $course_group []= array(
              'pair',
              $course1,
              $course2
            );

            $is_pairing = false;
          }

          $new_word_type = WORD_TYPE_CNUM;

        } else if (in_array(strtolower($word_buffer), $numbers)) {
          $value = array_search(strtolower($word_buffer), $numbers) + 1;
          $last_number = $value;
          //echo 'found a number: '.$value."\n";

        } else if ($last_number && strtolower($word_buffer) == 'of') {  
          $last_numerical_count = $last_number;
          $last_number = null;

        } else {
          //echo 'found a word: '.$word_buffer."\n";

        }
        
        $last_word_type = $new_word_type;

        if ($letter == '/') {
          //echo "found a pairing operator\n";
          $is_pairing = true;
        }

        $word_buffer = '';
        $in_word = false;
      }
    }
  }

  if (!empty($course_group)) {
    if ($last_numerical_count) {
      $group_info = array($last_numerical_count);
    } else {
      $group_info = array($group_operator);
    }
    $operators []= array_merge($group_info, $course_group);
  }
  //echo "done\n";
  return $operators;
}

function parse_reqs($reqs) {
  $parts = explode(';', $reqs);
  //echo 'Parsing: '.$reqs."\n";
  foreach ($parts as &$xxx) {
    $xxx = trim($xxx);
  }
  unset($xxx);

  // Let's start breaking down the reqs into logical units.
  // XXX ###(, ###, ###, ...)
  //    Example: PHARM 129, 131, 220
  //    Means: You must take all three courses.
  // XXX ### or ###
  //    Example: AFM 101 or 128
  //    Means: You must take one or the other.

  $operators = array();
  foreach ($parts as $part) {
    $sub_operators = parse_part($part);
    $operators = array_merge($operators, $sub_operators);
  }

  return $operators;
}

$results = $db->query('SELECT cid, prereq_desc, title, faculty_acronym, course_number FROM courses;');
while ($row = mysql_fetch_assoc($results)) {
  $row['prereq_desc'] = trim(str_replace('Prereq:', '', $row['prereq_desc']));

  $reqs = $row['prereq_desc'];
  $cid = $row['cid'];
  if (ereg('[A-Z]{2,}', $reqs)) {

    echo $row['faculty_acronym'].' '.$row['course_number'].': '.$row['title']."\n";
    $operators = parse_reqs($reqs);
    
    //echo "logic:\n";
    echo json_encode($operators)."\n";

    $db->query('UPDATE courses SET prereqs = "'.mysql_escape_string(json_encode($operators)).'" WHERE cid="'.$cid.'";');
  }
}

$db->close();

?>
