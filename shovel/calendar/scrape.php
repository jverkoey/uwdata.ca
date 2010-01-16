<?php

// Welcome to the UW Course Calendar scraper.

include_once COMMON_PATH.'scraper_tools.php';
include_once COMMON_PATH.'simple_html_dom.php';
include_once COMMON_PATH.'Database.class.php';

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

// Let's set the current year...
$cookie = null;
$ckfile = tempnam("/tmp", "CURLCOOKIE");

$ch = curl_init($calendar_url);
curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'UWDataSpider/1.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1.7)');
$output = curl_exec($ch);
if (preg_match('/Set-Cookie: ([a-z0-9_\.]+)=([a-z0-9]+)/i', $output, $match)) {
  $cookie = $match[1].'='.$match[2];
} else {
  echo "No cookie found.\n";
  exit;
}

$is_old_calendar = false;
$calendar_url = get_redirect_url($calendar_url);
if (0 === strpos($calendar_url, 'http://www.ucalendar')) {
  // This is an old calendar that links straight to the ucalendar data.
  $is_old_calendar = true;
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// First, let's hit the UW course calendar web page and see which year we're currently looking at.
///////////////////////////////////////////////////////////////////////////////////////////////////
if ($is_old_calendar) {
  $course_cal_data = fetch_url($calendar_url);

  if (!$course_cal_data) {
    echo 'Failed to grab the course calendar data from '.$calendar_url."\n";
    exit;
  }

  $course_cal_html = str_get_html($course_cal_data);

  $calendarYearElm = $course_cal_html->find('h3');
  if (!$calendarYearElm) {
    echo 'Unable to find the calendar year from '.$calendar_url."\n";
    exit;
  }

  if (preg_match('/([0-9]+-[0-9]+)/', current($calendarYearElm)->innertext, $match)) {
    $calendarYear = $match[1];
  } else {
    echo 'Unable to parse the calendar year from '.$calendar_url."\n";
    exit;
  }

} else {
  $course_cal_data = fetch_url(COURSE_CAL_ROOT_URL);

  if (!$course_cal_data) {
    echo 'Failed to grab the course calendar data from '.COURSE_CAL_ROOT_URL."\n";
    exit;
  }

  $course_cal_html = str_get_html($course_cal_data);

  $calendarYearElm = $course_cal_html->find('span.CalendarYear');
  if (!$calendarYearElm) {
    echo 'Unable to find the calendar year from '.COURSE_CAL_ROOT_URL."\n";
    exit;
  }

  $calendarYear = current($calendarYearElm)->innertext;

}

$dbName = 'uwdata'.str_replace('-', '', $calendarYear);

echo 'Scraping data from the '.$calendarYear.' calendar year'."\n";
echo '  db: '.$dbName."\n";

///////////////////////////////////////////////////////////////////////////////////////////////////
// Now that we know which calendar year we're working with, let's ensure that the tables exist.
///////////////////////////////////////////////////////////////////////////////////////////////////
$db = new Database('localhost', 'uwdata', 'uwdata', $dbName);
$db->connect();

$schema = <<<SCHEMA
CREATE TABLE IF NOT EXISTS `courses` (
  `cid` int(10) unsigned NOT NULL,
  `faculty_acronym` varchar(10) NOT NULL,
  `course_number` int(10) unsigned NOT NULL,
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
  `__last_touched` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`cid`),
  KEY `faculty_acronym` (`faculty_acronym`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `faculties` (
  `acronym` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `__last_touched` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`acronym`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SCHEMA;

// Create the schemas.
foreach (explode(';', trim($schema, ';')) as $query) {
  $db->query($query);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Now let's get all of the faculties.
// These are the URLs on the left side of the page (in the nav area).
// On older course calendars, they're just part of a list.
///////////////////////////////////////////////////////////////////////////////////////////////////

if ($is_old_calendar) {
  $linkElms = $course_cal_html->find('li a');
} else {
  $linkElms = $course_cal_html->find('a.Level1');
}
$links = array();
foreach ($linkElms as $e) {
  if (0 === strpos($e->innertext, 'Faculty')) {
    $links[$e->innertext] = $calendar_url.$e->href;
  }
}


///////////////////////////////////////////////////////////////////////////////////////////////////
// And then find the course pages for each faculty.
// We look for the link that starts with "Courses" and then grab each of the faculties listed on
// that page.
///////////////////////////////////////////////////////////////////////////////////////////////////

$faculties = array();

foreach ($links as $title => $url) {
  $data = fetch_url($url);

  if (!$data) {
    echo 'Failed to grab the url data from '.$url."\n";
    continue;
  }

  $html = str_get_html($data);

  if ($is_old_calendar) {
    $lis = $html->find('li a');
    foreach ($lis as $anchor) {
      $searchText = preg_replace('/[ \t\r\n]+/', ' ', $anchor->innertext);
      if (0 === strpos($searchText, 'Course Description')) {
        if (!preg_match('/course-(.+)\.html/', $anchor->href, $match)) {
          echo "Unable to find the faculty acronym for ".$anchor->href."\n";
          exit;
        }

        $faculty_acronym = $match[1];

        $faculty_url = dirname($url).'/'.$anchor->href;

        $faculty_data = fetch_url($faculty_url);

        if (!$faculty_data) {
          echo 'Failed to grab the url data from '.$faculty_url."\n";
          continue;
        }

        $faculty_html = str_get_html($faculty_data);
        $titles = $faculty_html->find('title');
        $title = null;
        foreach ($titles as $inner_elm) {
          if (preg_match('/Courses (.+)/', $inner_elm->innertext, $match)) {
            $title = trim($match[1]);
            break;
          }
        }
        if (!$title) {
          echo 'Failed to grab the faculty name from '.$faculty_url."\n";
          continue;
        }

        $faculties[$faculty_acronym] = array(
          'name' => $title,
          'url'  => get_final_url($faculty_url)
        );

        unset($faculty_data);
        $faculty_html->__destruct();
        unset($faculty_html);
      } // if
    } // foreach

  } else {
    $elm = $html->find('a.Level2Group');

    $courses_url = null;
    foreach ($elm as $e) {
      if (0 === strpos($e->innertext, 'Courses')) {
        $courses_url = COURSE_CAL_ROOT_URL.$e->href;
        break;
      }
    }

    if (!$courses_url) {
      echo 'Failed to find the course url from '.$url."\n";
      continue;
    }

    $data = fetch_url($courses_url);

    if (!$data) {
      echo 'Failed to grab the course url data from '.$url."\n";
      continue;
    }

    $html = str_get_html($data);

    $elm = $html->find('a.Level2Group');

    foreach ($elm as $e) {
      $facultyUrl = COURSE_CAL_ROOT_URL.$e->href;
      preg_match_all('/(.+) \((.+)\)/', $e->innertext, $matches);
      $facultyName = $matches[1][0];
      $facultyAcronym = $matches[2][0];
      $faculties[$facultyAcronym] = array(
        'name' => $facultyName,
        'url'  => get_final_url($facultyUrl)
      );
    }
  }

  $html->__destruct();
  unset($html);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Great, we have all of the faculties. Now let's cross reference it with the existing db.
///////////////////////////////////////////////////////////////////////////////////////////////////

$results = $db->query('SELECT * FROM faculties;');
while ($row = mysql_fetch_assoc($results)) {
  // Check if this faculty no longer exists.
  if (!isset($faculties[$row['acronym']])) {
    $db->query('DELETE FROM faculties WHERE acronym = "'.mysql_escape_string($row['acronym']).'";');
  }
}

foreach ($faculties as $acronym => $info) {
  $sql = 'INSERT INTO faculties(acronym, name) VALUES("'.$acronym.'", "'.$info['name'].'") ON DUPLICATE KEY UPDATE name="'.$info['name'].'";';
  $db->query($sql);
}

echo 'Updated faculties...'."\n";

///////////////////////////////////////////////////////////////////////////////////////////////////
// We've updated all of the faculties, now let's scrape the course information.
///////////////////////////////////////////////////////////////////////////////////////////////////

$courses = array();

foreach ($faculties as $acronym => $info) {
  $data = fetch_url($info['url']);

  if (!$data) {
    echo 'Failed to grab the course url data from '.$url."\n";
    continue;
  }

  $html = str_get_html($data);
  unset($data);

  $elm = $html->find('table');

  foreach ($elm as $table) {
    if ($table->width == '80%') {
      $course = array();

      $tr = $table->find('tr');

      // Header, two columns
      $leftCol = current(current($tr)->find('td[align=left]'));
      $anchor = current($leftCol->find('a'))->name;
      $data = strip_tags($leftCol->innertext);
      if (!preg_match('/([a-z]+) ([a-z0-9]+) ([a-z,]+) ([0-9\.]+)/i', $data, $matches)) {
        echo "Unknown course header data\n";
        echo $data;
        exit;
      }

      $course['faculty_acronym'] = $matches[1];
      $course['course_number'] = $matches[2];
      $course['offerings'] = $matches[3];
      $course['credit_value'] = $matches[4];
      $course['src_url'] = $info['url'].'#'.$anchor;

      $offerings = explode(',', $course['offerings']);
      foreach ($offerings as $offering) {
        if ($offering == 'LEC') {
          $course['has_lec'] = true;
        } else if ($offering == 'TUT') {
          $course['has_tut'] = true;
        } else if ($offering == 'LAB') {
          $course['has_lab'] = true;
        } else if ($offering == 'PRJ') {
          $course['has_prj'] = true;
        } else if ($offering == 'TST') {
          $course['has_tst'] = true;
        }
      }
      unset($course['offerings']);

      $data = current(current($tr)->find('td[align=right]'))->innertext;
      preg_match('/Course ID: ([0-9]+)/i', $data, $matches);
      $course['cid'] = $matches[1];
      next($tr);

      // Title
      $data = current(current($tr)->find('td'))->innertext;
      $course['title'] = strip_tags($data);
      next($tr);

      // Description
      $data = current(current($tr)->find('td'))->innertext;
      $course['description'] = strip_tags($data);
      next($tr);

      // And then some number of extra fields...
      $extra_fields = array();
      while (current($tr)) {
        $data = ltrim(trim(strip_tags(current(current($tr)->find('td'))->innertext)), '.');
        if ($data) {
          if (0 === strpos($data, 'Prereq')) {
            $course['prereq_desc'] = $data;
          } else if (0 === strpos($data, 'Antireq')) {
            $course['antireq_desc'] = $data;
          } else if (0 === strpos(ltrim($data, '('), 'Coreq')) {
            $course['coreq_desc'] = $data;
          } else if (0 === strpos($data, '(Cross-listed')) {
            $course['crosslist_desc'] = $data;
          } else if (0 === strpos($data, 'Also offered by Distance Education')) {
            $course['has_dist_ed'] = true;
          } else if (0 === strpos($data, 'Only offered by Distance Education')) {
            $course['only_dist_ed'] = true;
          } else if (0 === strpos($data, 'Offered at St. Jerome\'s University')) {
            $course['only_stj'] = true;
          } else if (0 === strpos($data, 'Also offered at St. Jerome\'s University')) {
            $course['has_stj'] = true;
          } else if (0 === strpos($data, 'Department Consent Required')) {
            $course['needs_dept_consent'] = true;
          } else if (0 === strpos($data, 'Offered at Renison College') ||
                     0 === strpos($data, 'Offered at Renison University College')) {
            $course['only_ren'] = true;
          } else if (0 === strpos($data, 'Also offered at Renison College') ||
                     0 === strpos($data, 'Also offered at Renison University College')) {
            $course['has_ren'] = true;
          } else if (0 === strpos($data, 'Offered at Conrad Grebel University College')) {
            $course['only_cgr'] = true;
          } else if (0 === strpos($data, 'Also offered at Conrad Grebel University College')) {
            $course['has_cgr'] = true;
          } else if (0 === strpos($data, 'Instructor Consent Required')) {
            $course['needs_instr_consent'] = true;
          } else if (0 === strpos($data, '[Note:')) {
            $course['note_desc'] = $data;
          } else {
            $extra_fields []= $data;
          }
        }

        next($tr);
      }
      if ($extra_fields) {
        $course['extra_fields'] = $extra_fields;
      }

      $courses[$course['cid']] = $course;
    }
  }

  // We need to forcefully destruct this object to avoid memory growing forever.
  $html->__destruct();
  unset($html);
}

// Prune dead courses.
$results = $db->query('SELECT * FROM courses;');
while ($row = mysql_fetch_assoc($results)) {
  if (!isset($courses[$row['cid']])) {
    $db->query('DELETE FROM courses WHERE cid = "'.mysql_escape_string($row['cid']).'";');
  }
}

// And update existing ones/insert new ones.
foreach ($courses as $cid => $course) {
  if (isset($course['extra_fields'])) {
    print_r($course['extra_fields']);
    unset($course['extra_fields']);
  }
  $escaped_values = array();
  foreach (array_values($course) as $value) {
    $escaped_values []= '"'.mysql_escape_string($value).'"';
  }
  $update_query_arr = array();
  foreach ($course as $key => $value) {
    $update_query_arr []= $key.'="'.mysql_escape_string($value).'"';
  }
  $sql = 'INSERT INTO courses('.implode(',', array_keys($course)).') VALUES('.implode(',', $escaped_values).') ON DUPLICATE KEY UPDATE '.implode(',', $update_query_arr).';';
  $db->query($sql);
}

unset($courses);

$db->close();

?>
