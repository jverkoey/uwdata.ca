<?php

// UW course prereqs calculation

include_once COMMON_PATH.'db.php';
include_once COMMON_PATH.'scraper_tools.php';
include_once COMMON_PATH.'simple_html_dom.php';
include_once COMMON_PATH.'Database.class.php';

echo 'Scraping ratemyprofs.com...'."\n";

///////////////////////////////////////////////////////////////////////////////////////////////////
// Now that we know which calendar year we're working with, let's ensure that the tables exist.
///////////////////////////////////////////////////////////////////////////////////////////////////
$db = new Database(DB_HOST, DB_USER, DB_PASS, 'uwdata_schedule');
$db->connect();

$rmp_base_url = 'http://www.ratemyprofessors.com/SelectTeacher.jsp?the_dept=All&sid=1490&orderby=TLName&letter=';

$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
for ($letter = 0; $letter < strlen($letters); ++$letter) {
  $url = $rmp_base_url.$letters[$letter];
  $data = fetch_url($url);

  if (!$data) {
    echo 'Failed to grab the page: '.$url."\n";
    exit;
  }

  $html = str_get_html($data);

  $table = $html->find('#rmp_table', 0);
  foreach ($table->find('tr') as $tr) {
    if ($tr->class == 'table_headers') {
      continue;
    }

    if (preg_match('/<a href="[a-z]+\.jsp\?tid=([0-9]+)">([a-z\-\' ]+)<\/a>, ([a-z\-\' \.]+)/i', $tr->find('td', 3)->innertext, $match)) {
      $prof = array(
        'first_name' => $match[3],
        'last_name' => $match[2],
        'ratemyprof_id' => $match[1],
        'number_of_ratings' => $tr->find('td', 5)->innertext,
        'overall_quality' => $tr->find('td', 6)->innertext,
        'ease' => $tr->find('td', 7)->innertext
      );

      $escaped_values = array();
      foreach (array_values($prof) as $value) {
        $escaped_values []= '"'.mysql_escape_string($value).'"';
      }
      $update_query_arr = array();
      foreach ($prof as $key => $value) {
        $update_query_arr []= $key.'="'.mysql_escape_string($value).'"';
      }
      $sql = 'INSERT INTO instructors('.implode(',', array_keys($prof)).') VALUES('.implode(',', $escaped_values).') ON DUPLICATE KEY UPDATE '.implode(',', $update_query_arr).';';
      $db->query($sql);
    } else {
      echo "Couldn't find this prof's details.\n";
      echo $tr->find('td', 3)->innertext."\n";
    }
  }

  $html->__destruct();
  unset($html);
}

$db->close();

?>
