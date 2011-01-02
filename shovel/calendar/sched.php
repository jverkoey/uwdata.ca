<?php

// UW course prereqs calculation

include_once COMMON_PATH.'db.php';
include_once COMMON_PATH.'scraper_tools.php';
include_once COMMON_PATH.'simple_html_dom.php';
include_once COMMON_PATH.'Database.class.php';

echo 'Scraping schedule...'."\n";

define('FAST_CACHE_EXPIRY_TIMESPAN', 60*60);
define('GRAD_ROOT_URL', 'http://www.adm.uwaterloo.ca/infocour/CIR/SA/grad.html');
define('UNDERGRAD_ROOT_URL', 'http://www.adm.uwaterloo.ca/infocour/CIR/SA/under.html');
define('CGI_URL', 'http://www.adm.uwaterloo.ca/cgi-bin/cgiwrap/infocour/salook.pl');

if(sizeof($argv) < 2) {
    $is_grad  =  false;
} else {
    $is_grad = $argv[1] == "grad";
}

if($is_grad) {
    $root_url = GRAD_ROOT_URL;
    $salook_level = 'grad';
    echo "Running for graduate schedules\n";
} else {
    $root_url = UNDERGRAD_ROOT_URL;
    $salook_level = 'under';
    echo "Running for undergraduate schedules\n";
}
///////////////////////////////////////////////////////////////////////////////////////////////////
// Now that we know which calendar year we're working with, let's ensure that the tables exist.
///////////////////////////////////////////////////////////////////////////////////////////////////
$db = new Database(DB_HOST, DB_USER, DB_PASS, 'uwdata_schedule');
$db->connect();

$index_data = fetch_url($root_url, FAST_CACHE_EXPIRY_TIMESPAN);

if (!$index_data) {
  echo 'Failed to grab the schedule page.'."\n";
  exit;
}

$terms = array();
if (preg_match_all('/([0-9]+=[a-z]+ [0-9]+)/i', $index_data, $matches)) {
  foreach ($matches[1] as $match) {
    preg_match('/([0-9]+)=([a-z]+) ([0-9]+)/i', $match, $parts);
    $term_id = $parts[1];
    $term_season = $parts[2];
    $term_year = $parts[3];

    switch (strtolower($term_season)) {
      case 'spring':
      case 'winter': {
        $calendar_years = (intval($term_year)-1).$term_year;
        break;
      }
      case 'fall': {
        $calendar_years = $term_year.(intval($term_year)+1);
        break;
      }
    }

    $terms []= $term_id;

    $sql = 'INSERT INTO terms(term_id, term_season, term_year, calendar_years) VALUES("'.$term_id.'", "'.$term_season.'", "'.$term_year.'", "'.$calendar_years.'") ON DUPLICATE KEY UPDATE term_season="'.$term_season.'", term_year="'.$term_year.'", calendar_years="'.$calendar_years.'";';
    $db->query($sql);
  }
}

$col_keys = array(
  'class_number',
  'component_section',
  'campus_location',
  'associated_class',
  'related_component_1',
  'related_component_2',
  'enrollment_cap',
  'enrollment_total',
  'wait_cap',
  'wait_tot',
  'dates',
  'location',
  'instructor'
);

function munge_military_dates($format, &$object) {
  if (preg_match('/([0-9]+):([0-9]+)-([0-9]+):([0-9]+)([A-Z]+)/', $format, $match)) {
    $military_start = intval($match[1].$match[2]);
    $military_end = intval($match[3].$match[4]);
    if ($military_end < $military_start) {
      $military_start += 1200;
    }
    if ($military_start <= 700 || $military_end - $military_start >= 130) {
      $military_start += 1200;
      $military_end += 1200;
    }
    $object['start_time'] = $military_start;
    $object['end_time'] = $military_end;
    $object['days'] = $match[5];
    return true;

  } else if (strpos($format, '12:00-12:00') === 0) {  
    // Do nothing.
    return true;

  } else if ($format == 'Closed Section') {
    $object['is_closed'] = true;
    return true;
    
  } else if ($format == 'Cancelled Section') {
    $object['is_closed'] = true;
    return true;

  } else if ($format) {
    echo "Unknown format.\n";
    echo $format."\n";
    exit;
  }
  return false;
}

if (preg_match_all('/<OPTION VALUE="([A-Z]+)"(?: SELECTED)?>[A-Z]+/', $index_data, $faculty_match)) {
  foreach ($faculty_match[1] as $faculty) {
  foreach ($terms as $term_id) {
    echo $faculty.": ".$term_id."\n";
    $data = fetch_url(CGI_URL, FAST_CACHE_EXPIRY_TIMESPAN, array(
      'sess' => $term_id,
      'subject' => $faculty,
      'level' => $salook_level
    ));

    if (strpos($data, 'Sorry, but your query had no matches') !== FALSE) {
      echo 'No results found.'."\n";
      continue;
    }

    $html = str_get_html($data);
    unset($data);

    $classes = array();
    $active_course = null;
    foreach ($html->find('table[border=2] > tr') as $tr) {
      if (count($tr->find('td')) == 4) {
        $course_info = $tr->find('td');
        $active_course = array(
          'faculty_acronym' => trim($course_info[0]->innertext),
          'course_number' => trim($course_info[1]->innertext)
        );
      }

      if (count($tr->find('td[colspan=4]')) == 1) {
        $active_course['note'] = trim(str_replace('<b>Notes:</b>', '', $tr->find('td[colspan=4]', 0)->innertext));
      }

      if (count($tr->find('td[colspan=3]')) == 1) {
        $course_table = $tr->find('td[colspan=3] table', 0);
        foreach ($course_table->find('tr') as $row) {
          if (count($row->find('th')) > 0) {
            continue;
          }
          if (count($row->find('td[colspan=10]')) > 0) {
            continue;
          }
          // Otherwise, this is a legit row.
          
          if (count($row->find('td[colspan=6]')) > 0) {
            // This is a special case of a row. It's part of the last proper row, but is just
            // additional information.
            $index = 0;
            $reserve = array();
            foreach ($row->find('td') as $td) {
              if (0 == $index) {
                $reserve['reserve_group'] = trim(strip_tags(str_replace('Reserve: ', '', $td->innertext)));
              } else {
                $text = trim(str_replace('&nbsp', '', $td->innertext));
                if ($text) {
                  $reserve[$col_keys[$index]] = $text;
                }
              }

              $index += max(1, intval($td->colspan));
            }

            if (!isset($classes[count($classes) - 1]['reserves'])) {
              $classes[count($classes) - 1]['reserves'] = array();
            }
            $classes[count($classes) - 1]['reserves'] []= $reserve;

          } else {
            $index = 0;
            $new_class = $active_course;
            foreach ($col_keys as $key) {
              $new_class[$key] = '';
            }
            foreach ($row->find('td') as $td) {
              $new_class[$col_keys[$index]] = trim(str_replace('&nbsp', '', $td->innertext));

              $index += max(1, intval($td->colspan));
            }

            $classes []= $new_class;
          }
        }
      }
    }

    $html->__destruct();
    unset($html);

    foreach ($classes as $class) {
      if (preg_match('/([A-Z]+) +([0-9A-Z]+)/', $class['location'], $match)) {
        $class['building'] = $match[1];
        $class['room'] = $match[2];
      } else {
        $class['building'] = '';
        $class['room'] = '';
      }
      unset($class['location']);

      if ($class['dates'] == 'TBA') {
        $class['tba_schedule'] = true;
      } else if (munge_military_dates($class['dates'], $class)) {
        // do nothing
      } else {
        $class['tba_schedule'] = false;
      }
      unset($class['dates']);

      if (isset($class['reserves'])) {
        foreach ($class['reserves'] as $reserve) {
          $reserve['term'] = $term_id;
          $reserve['class_number'] = $class['class_number'];
          if (isset($reserve['dates'])) {
            if (munge_military_dates($reserve['dates'], $reserve)) {
              // Do nothing.
            }
            unset($reserve['dates']);
          }

          if (isset($reserve['location'])) {
            if (preg_match('/([A-Z]+) +([0-9A-Z]+)/', $reserve['location'], $match)) {
              $reserve['building'] = $match[1];
              $reserve['room'] = $match[2];
            }
            unset($reserve['location']);
          }

          $escaped_values = array();
          foreach (array_values($reserve) as $value) {
            $escaped_values []= '"'.mysql_escape_string($value).'"';
          }
          $update_query_arr = array();
          foreach ($reserve as $key => $value) {
            $update_query_arr []= $key.'="'.mysql_escape_string($value).'"';
          }
          $sql = 'INSERT INTO reserves('.implode(',', array_keys($reserve)).') VALUES('.implode(',', $escaped_values).') ON DUPLICATE KEY UPDATE '.implode(',', $update_query_arr).';';
          $db->query($sql);
        }
        unset($class['reserves']);
      }

      $class['term']    = $term_id;
      $class['is_grad'] = $is_grad;

      $escaped_values = array();
      foreach (array_values($class) as $value) {
        $escaped_values []= '"'.mysql_escape_string($value).'"';
      }
      $update_query_arr = array();
      foreach ($class as $key => $value) {
        $update_query_arr []= $key.'="'.mysql_escape_string($value).'"';
      }
      $sql = 'INSERT INTO classes('.implode(',', array_keys($class)).') VALUES('.implode(',', $escaped_values).') ON DUPLICATE KEY UPDATE '.implode(',', $update_query_arr).';';
      $db->query($sql);
    }
    unset($classes);
  }}
}

$db->close();

?>
