<?php

// UW course prereqs calculation

include_once COMMON_PATH.'db.php';
include_once COMMON_PATH.'scraper_tools.php';
include_once COMMON_PATH.'simple_html_dom.php';
include_once COMMON_PATH.'Database.class.php';

echo 'Binding ratemyprof ids with the schedule prof names...'."\n";

///////////////////////////////////////////////////////////////////////////////////////////////////
// Now that we know which calendar year we're working with, let's ensure that the tables exist.
///////////////////////////////////////////////////////////////////////////////////////////////////
$db = new Database(DB_HOST, DB_USER, DB_PASS, 'uwdata_schedule');
$db->connect();

$results = $db->query('SELECT class_number, term, instructor FROM classes;');
while ($row = mysql_fetch_assoc($results)) {
  if (!trim($row['instructor'])) {
    continue;
  }
  $parts = explode(',', $row['instructor']);
  $params = array();
  if (isset($parts[0])) {
    $params['last_name'] = trim($parts[0]);
  }
  if (isset($parts[1])) {
    $params['first_name'] = trim($parts[1]);
  }
  $sql_params = array();
  foreach ($params as $key => $value) {
    $sql_params []= $key.' = "'.$value.'"';
  }
  $sql = 'SELECT * FROM instructors WHERE '.implode(' AND ', $sql_params).';';
  echo $sql."\n";
  $search_results = $db->query($sql);
  
  $search_rows = array();
  while ($search_row = mysql_fetch_assoc($search_results)) {
    $search_rows []= $search_row;
  }
  if (count($search_rows) > 1) {
    echo "Too many matches, unsure of which prof to use!\n";
    print_r($params);
    continue;
  }
  if (count($search_rows) < 1) {
    // No matches, add this to the db.
    $db->query('INSERT INTO instructors(first_name, last_name) VALUES("'.$params['first_name'].'", "'.$params['last_name'].'");');

    $search_results = $db->query('SELECT * FROM instructors WHERE '.implode(' AND ', $sql_params).';');

    $search_rows = array();
    while ($search_row = mysql_fetch_assoc($search_results)) {
      $search_rows []= $search_row;
    }
  }

  // One match!
  $instructor = $search_rows[0];
  
  $sql_params = array(
    'class_number = '.$row['class_number'],
    'term = '.$row['term'],
  );
  $db->query('UPDATE classes SET instructor_id = "'.$instructor['id'].'" WHERE '.implode(' AND ', $sql_params).';');
}

$db->close();

?>
