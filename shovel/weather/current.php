<?php

include_once COMMON_PATH.'db.php';
include_once COMMON_PATH.'scraper_tools.php';
include_once COMMON_PATH.'simple_html_dom.php';
include_once COMMON_PATH.'Database.class.php';

echo 'Scraping current weather...'."\n";

define('FAST_CACHE_EXPIRY_TIMESPAN', 60*15);

$db = new Database(DB_HOST, DB_USER, DB_PASS, 'uwdata_weather');
$db->connect();

$index_data = fetch_url('http://weather.uwaterloo.ca/', FAST_CACHE_EXPIRY_TIMESPAN);

if (!$index_data) {
  echo 'Failed to grab the weather page.'."\n";
  exit;
}

$index_html = str_get_html($index_data);

$tableElm = $index_html->find('table');
if (!$tableElm) {
  echo 'Unable to find the table'."\n";
  exit;
}

$tableElm = current($tableElm);

$tableRowElms = $tableElm->find('tr');

$date = null;
$time = null;

$reading = array();

$index = 0;
foreach ($tableRowElms as $tableRowElm) {
  switch ($index) {
    case 0: {
      if (preg_match('/([a-z]+ [0-9]+, [0-9]+)/i', $tableRowElm->innertext, $match)) {
        $date = $match[1];
      }
      if (preg_match('/([0-9]+:[0-9]+ [ap]m+)/i', $tableRowElm->innertext, $match)) {
        $time = $match[1];
      }
      break;
    }
    case 1: {
      if (preg_match('/> *([\-0-9.]+) *<\/font>/i', $tableRowElm->innertext, $match)) {
        $reading['temp'] = $match[1];
      }
      break;
    }
    case 2: {
      if (preg_match_all('/> *([\-0-9.]+) *<\/font>/i', $tableRowElm->innertext, $match)) {
        $reading['temp_24hourmax'] = $match[1][0];
        $reading['temp_24hourmin'] = $match[1][1];
      }
      break;
    }
    case 3: {
      if (preg_match_all('/> *([\-0-9.]+) *<\/font>/i', $tableRowElm->innertext, $match)) {
        $reading['relative_humidity'] = $match[1][0];
        $reading['dew_point'] = $match[1][1];
      }
      break;
    }
    case 4: {
      if (preg_match('/> *([\-0-9.]+) *<\/font>/i', $tableRowElm->innertext, $match)) {
        $reading['wind_speed'] = $match[1];
      }
      if (preg_match('/> *([a-z]+) *<\/font>/i', $tableRowElm->innertext, $match)) {
        $reading['wind_direction'] = $match[1];
      }
      break;
    }
    case 5: {
      if (preg_match('/> *([\-0-9.]+) *<\/font>/i', $tableRowElm->innertext, $match)) {
        $reading['barometric_pressure'] = $match[1];
      }
      if (preg_match_all('/> *([a-z]+) *<\/font>/i', $tableRowElm->innertext, $match)) {
        $reading['barometric_direction'] = $match[1][1];
      }
      break;
    }
    case 6: {
      if (preg_match('/> *([\-0-9.]+) *<\/font>/i', $tableRowElm->innertext, $match)) {
        $reading['incoming_radiation'] = $match[1];
      }
      break;
    }
  }
  $index++;
}

$reading['timestamp'] = date('Y-m-d H:i:s', strtotime($date.' '.$time));

echo 'Reading for '.$reading['timestamp']."\n";

$escaped_values = array();
foreach (array_values($reading) as $value) {
  $escaped_values []= '"'.mysql_escape_string($value).'"';
}
$update_query_arr = array();
foreach ($reading as $key => $value) {
  $update_query_arr []= $key.'="'.mysql_escape_string($value).'"';
}
$sql = 'INSERT INTO readings('.implode(',', array_keys($reading)).') VALUES('.implode(',', $escaped_values).') ON DUPLICATE KEY UPDATE '.implode(',', $update_query_arr).';';
$db->query($sql);
