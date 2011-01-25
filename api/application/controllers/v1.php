<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    uwdatav1
 * @author     Jeff Verkoeyen
 * @copyright  (c) 2010 Jeff Verkoeyen
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
class V1_Controller extends Controller {

  const ALLOW_PRODUCTION = TRUE;

  public function __construct() {
    parent::__construct();

    if ($this->input->ip_address() == '67.159.14.85') {
      echo 'Your access to uwdata has been revoked on account of excessive API usage.<br/>';
      echo 'Cheers.';
      exit;
    }
  }

  private function add_log($return_type, $action, $param1, $param2, $param3) {
    $api_key = $this->input->get('key');
    $db = Database::instance('uwdata_logs');
    $data = array(
      'api_key' => $api_key,
      'action_name' => $action,
      'host_ip' => $this->input->ip_address()
    );
    if ($param1) {
      $data['param1'] = $param1;
    }
    if ($param2) {
      $data['param2'] = $param2;
    }
    if ($param3) {
      $data['param3'] = $param3;
    }
    $query = $this->input->get('q');
    if ($query) {
      $data['query'] = $query;
    }
    $db->insert('api_logs', $data);

    $results = $db->
      from('api_logs')->
      select(array('timestamp'))->
      where('api_key', $api_key)->
      where('timestamp > "'.mysql_escape_string(date('Y-m-d H:i:s', time()-60)).'"')->
      get();

    // HACKHACKHACK: Allow higher throughput for the demo API key.
    if ($api_key != '6a43bfb883aeeff0d72c895e09425538' && count($results) > 10
        || count($results) > 120) {
      $this->echo_formatted_data(array(
        'error' => 'You are only allowed 10 requests/minute. Please wait 5 minutes for your access to be enabled again.'
        ), $return_type);
      exit;
    }
  }

  /**
   * Possible endpoints:
   *   v1/faculty/list.<return type>
   *   - A list of all faculties.
   *     faculty_list
   *   v1/faculty/<faculty acronym>.<return type>
   *   - Detailed information about the given faculty.
   *     faculty_by_id
   *   v1/faculty/<faculty acronym>/courses.<return type>
   *   - All of the courses in the given faculty.
   *     faculty_courses
   */
  public function faculty($param1, $param2 = null, $param3 = null) {
    $return_type = $this->init_action($param1, $param2, $param3);
    if (!$return_type) {
      return;
    }

    $this->add_log($return_type, 'faculty', $param1, $param2, $param3);

    if (preg_match('/^[a-z]+$/i', $param1)) {
      // v1/faculty/<text>

      if (!$param2) {
        // v1/faculty/<text>.<return type>

        if ($param1 == 'list') {
          // v1/faculty/list.<return type>
          $this->faculty_list($return_type);

        } else {
          // v1/faculty/<faculty>.<return type>
          $this->faculty_by_id($param1, $return_type);
        }

      } else if ($param2 == 'courses') {
        // v1/faculty/<faculty>/courses.<return type>
        $this->faculty_courses($param1, $return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else {
      throw new Kohana_404_Exception();
    }
  }

  /**
   * Possible endpoints:
   *   v1/course/<faculty acronym>/<course number>.<return type>
   *   - Detailed information about the given course.
   *     course_by_number
   *   v1/course/<course id>.<return type>
   *   - Detailed information about the given course.
   *     course_by_id
   *   v1/course/<faculty acronym>/<course number>/prereqs.<return type>
   *   - The prerequisites logic and description for the given course.
   *     course_prereqs_by_number
   *   v1/course/<course id>/prereqs.<return type>
   *   - The prerequisites logic and description for the given course.
   *     course_prereqs_by_id
   *   v1/course/<faculty acronym>/<course number>/schedule.<return type>
   *   - The schedule for the given course.
   *     course_schedule_by_number
   *   v1/course/<course id>/schedule.<return type>
   *   - The schedule for the given course.
   *     course_schedule_by_id
   *   v1/course/search.<return type>?q=<query>
   *   - Search the course list by title and description.
   *     course_search
   */
  public function course($param1, $param2 = null, $param3 = null) {
    $return_type = $this->init_action($param1, $param2, $param3);
    if (!$return_type) {
      return;
    }

    $this->add_log($return_type, 'course', $param1, $param2, $param3);

    if (preg_match('/^[a-z]+$/i', $param1) && preg_match('/^[0-9]+[a-z]*$/i', $param2)) {
      // v1/course/<faculty acronym>/<course number>

      if ($param3 == 'prereqs') {
        // v1/course/<faculty acronym>/<course number>/prereqs.<return type>
        $this->course_prereqs_by_number($param1, $param2, $return_type);

      } else if ($param3 == 'schedule') {
        // v1/course/<faculty acronym>/<course number>/schedule.<return type>
        $this->course_schedule_by_number($param1, $param2, $return_type);

      } else if (!$param3) {
          // v1/course/<faculty acronym>/<course number>.<return type>
        $this->course_by_number($param1, $param2, $return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else if (preg_match('/^[0-9]+$/i', $param1)) {
      if ($param2 == 'prereqs') {
        // v1/course/<course id>/prereqs.<return type>
        $this->course_prereqs_by_id($param1, $return_type);

      } else if ($param2 == 'schedule') {
        // v1/course/<course id>/schedule.<return type>
        $this->course_schedule_by_id($param1, $return_type);

      } else if (!$param2) {
        // v1/course/<course id>.<return type>
        $this->course_by_id($param1, $return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else if (!$param2) {
      if ($param1 == 'search') {
        // v1/course/search.<return type>?q=<query>
        $this->course_search($return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else {
      throw new Kohana_404_Exception();
    }
  }

  /**
   * Possible endpoints:
   *   v1/prof/<instructor id>.<return type>
   *   - Information about the given professor.
   *     prof_by_id
   *   v1/prof/<instructor id>/timeslots.<return type>
   *   - All of the timeslots the given professor is running.
   *     prof_timeslots
   *   v1/prof/search.<return type>?q=<query>
   *   - Search the professor list by first and last name.
   *     prof_search
   */
  public function prof($param1, $param2 = null, $param3 = null) {
    $return_type = $this->init_action($param1, $param2, $param3);
    if (!$return_type) {
      return;
    }

    $this->add_log($return_type, 'prof', $param1, $param2, $param3);

    if (preg_match('/^[0-9]+$/', $param1)) {
      if ($param2 == 'timeslots') {
        // v1/prof/<instructor id>/timeslots.<return type>
        $this->prof_timeslots($param1, $return_type);

      } else if (!$param2) {
        // v1/prof/<instructor id>.<return type>
        $this->prof_by_id($param1, $return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else if (!$param2) {
      if ($param1 == 'search') {
        // v1/prof/search.<return type>?q=<query>
        $this->prof_search($return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else {
      throw new Kohana_404_Exception();
    }
  }

  /**
   * Possible endpoints:
   *   v1/term/list.<return type>
   *   - A list of all academic terms.
   *     term_list
   */
  public function term($param1, $param2 = null, $param3 = null) {
    $return_type = $this->init_action($param1, $param2, $param3);
    if (!$return_type) {
      return;
    }

    $this->add_log($return_type, 'term', $param1, $param2, $param3);

    if (!$param2) {
      if ($param1 == 'list') {
        // v1/term/list.<return type>
        $this->term_list($return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else {
      throw new Kohana_404_Exception();
    }
  }

  /**
   * Possible endpoints:
   *   v1/geo/building/list.<return type>
   *   - A list of all buildings.
   *     geo_building_list
   *   v1/geo/parking/list.<return type>
   *   - A list of all parking lots.
   *     geo_parking_list
   */
  public function geo($param1, $param2 = null, $param3 = null) {
    $return_type = $this->init_action($param1, $param2, $param3);
    if (!$return_type) {
      return;
    }

    $this->add_log($return_type, 'geo', $param1, $param2, $param3);

    if (preg_match('/^building$/i', $param1)) {
      // v1/geo/building/<text>

      if (preg_match('/^list$/i', $param2)) {
        // v1/geo/building/list.<return type>
        $this->geo_building_list($return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else if (preg_match('/^parking$/i', $param1)) {
      // v1/geo/parking/<text>

      if (preg_match('/^list$/i', $param2)) {
        // v1/geo/parking/list.<return type>
        $this->geo_parking_list($return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else {
      throw new Kohana_404_Exception();
    }
  }

  /**
   * Possible endpoints:
   *   v1/weather/current.<return type>
   *   - The current weather
   *     weather_current
   */
  public function weather($param1, $param2 = null, $param3 = null) {
    $return_type = $this->init_action($param1, $param2, $param3);
    if (!$return_type) {
      return;
    }

    $this->add_log($return_type, 'weather', $param1, $param2, $param3);

    if (preg_match('/^current$/i', $param1)) {
      // v1/weather/current/

      if (!$param2) {
        // v1/weather/current.<return type>
        $this->weather_current($return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else {
      throw new Kohana_404_Exception();
    }
  }

  /**
   * Possible endpoints:
   *   v1/dump/courses.<return type>
   *   - A dump of all courses for the active calendar year.
   *     dump_courses
   *   v1/dump/schedules.<return type>
   *   - A dump of all schedule information for the given term.
   *     dump_courses
   */
  public function dump($param1, $param2 = null, $param3 = null) {
    $return_type = $this->init_action($param1, $param2, $param3);
    if (!$return_type) {
      return;
    }

    $this->add_log($return_type, 'dump', $param1, $param2, $param3);

    if (preg_match('/^courses$/i', $param1)) {
      // v1/dump/courses

      if (!$param2) {
        // v1/dump/courses.<return type>
        $this->dump_courses($return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else if (preg_match('/^schedules$/i', $param1)) {
      // v1/dump/schedules

      if (!$param2) {
        // v1/dump/schedules.<return type>
        $this->dump_schedules($return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else {
      throw new Kohana_404_Exception();
    }
  }

  //////////////////////////////////
  //////////////////////////////////

  /**
   * A list of all faculties.
   *
   * endpoint: v1/faculty/list.<return type>
   */
  private function faculty_list($return_type) {
    $db = $this->get_db();

    $results = $db->
      from('faculties')->
      select(array('acronym', 'name'))->
      get();

    $result = array();
    foreach ($results as $row) {
      $result []= array('faculty' => $row);
    }

    $this->echo_formatted_data(array('faculties' => $result), $return_type);
  }

  /**
   * Detailed information about the given faculty.
   *
   * endpoint: v1/faculty/<faculty acronym>.<return type>
   */
  private function faculty_by_id($id, $return_type) {
    $db = $this->get_db();

    $result = $db->
      from('faculties')->
      select('acronym', 'name', '__last_touched as last_updated')->
      where('acronym', $id)->
      limit(1)->
      get();

    if (count($result)) {
      foreach ($result as $row) {
        $faculty = array('faculty' => $row);
      }
    } else {
      $faculty = $this->error_data('Unknown faculty');
    }

    $this->echo_formatted_data($faculty, $return_type);
  }

  /**
   * All of the courses in the given faculty.
   *
   * endpoint: v1/faculty/<faculty acronym>/courses.<return type>
   */
  private function faculty_courses($faculty_acronym, $return_type) {
    $db = $this->get_db();

    $result = $db->
      from('courses')->
      select(
        'cid',
        'course_number',
        'title',
        'description')->
      where('faculty_acronym', $faculty_acronym)->
      get();

    $courses = array();
    foreach ($result as $row) {
      $courses []= array('course' => $row);
    }

    $this->echo_formatted_data(array('courses' => $courses), $return_type);
  }

  //////////////////////////////////
  //////////////////////////////////

  /**
   * A list of all buildings.
   *
   * endpoint: v1/geo/building/list.<return type>
   */
  private function geo_building_list($return_type) {
    $db = Database::instance('uwdata_geo');

    $results = $db->
      from('buildings')->
      select(array('name', 'short_name', 'lat', 'lng'))->
      get();

    $result = array();
    foreach ($results as $row) {
      $result []= array('building' => $row);
    }

    $this->echo_formatted_data(array('buildings' => $result), $return_type);
  }

  /**
   * A list of all parking lots.
   *
   * endpoint: v1/geo/parking/list.<return type>
   */
  private function geo_parking_list($return_type) {
    $db = Database::instance('uwdata_geo');

    $results = $db->
      from('parking')->
      select(array('name', 'type', 'payment_type', 'max_cost', 'hourly_cost', 'weekend_cost', 'after5_cost', 'lat', 'lng'))->
      get();

    $result = array();
    foreach ($results as $row) {
      $result []= array('lot' => $row);
    }

    $this->echo_formatted_data(array('parking_lots' => $result), $return_type);
  }

  //////////////////////////////////
  //////////////////////////////////

  /**
   * The current weather
   *
   * endpoint: v1/weather/current.<return type>
   */
  private function weather_current($return_type) {
    $db = Database::instance('uwdata_weather');

    $results = $db->
      from('readings')->
      select()->
      limit(1)->
      orderby('timestamp', 'DESC')->
      get();

    $result = array();
    foreach ($results as $row) {
      $result = $row;
    }

    $this->echo_formatted_data($result, $return_type);
  }

  //////////////////////////////////
  //////////////////////////////////

  /**
   * Detailed information about the given course.
   *
   * endpoint: v1/course/<faculty acronym>/<course number>.<return type>
   * @example v1/course/CS/135.json
   */
  private function course_by_number($faculty_acronym, $course_number, $return_type) {
    $this->course_by_result(
      $this->fetch_course_by_number($faculty_acronym, $course_number), $return_type);
  }

  /**
   * Detailed information about the given course.
   *
   * endpoint: v1/course/<course id>.<return type>
   * @example v1/course/12040.json
   */
  private function course_by_id($cid, $return_type) {
    $this->course_by_result($this->fetch_course_by_id($cid), $return_type);
  }

  /**
   * The prerequisites logic and description for the given course.
   *
   * endpoint: v1/course/<faculty acronym>/<course number>/prereqs.<return type>
   * @example v1/course/CS/135/prereqs.json
   */
  private function course_prereqs_by_number($faculty_acronym, $course_number, $return_type) {
    $db = $this->get_db();

    $result = $db->
      from('courses')->
      select('prereqs')->
      where('faculty_acronym', $faculty_acronym)->
      where('course_number', $course_number)->
      get();

    $this->course_prereqs_by_result($result, $return_type);
  }

  /**
   * The prerequisites logic and description for the given course.
   *
   * endpoint: v1/course/<course id>/prereqs.<return type>
   * @example v1/course/12040/prereqs.json
   */
  private function course_prereqs_by_id($course_id, $return_type) {
    $db = $this->get_db();

    $result = $db->
      from('courses')->
      select('prereqs')->
      where('cid', $course_id)->
      get();

    $this->course_prereqs_by_result($result, $return_type);
  }

  /**
   * The schedule for the given course.
   *
   * endpoint: v1/course/<faculty acronym>/<course number>/schedule.<return type>
   * @example v1/course/CS/135/schedule.json
   */
  private function course_schedule_by_number($faculty_acronym, $course_number, $return_type) {
    $term = $this->get_term_input();

    $db = $this->select_detailed_class_info(Database::instance('uwdata_schedule'));

    $result = $db->
      from('classes')->
      where('faculty_acronym', $faculty_acronym)->
      where('course_number', $course_number)->
      where('term', $term)->
      get();

    $classes = array();
    foreach ($result as $row) {
      $classes []= array('class' => $row);
    }

    $this->echo_formatted_data(array('classes' => $classes), $return_type);
  }

  /**
   * The schedule for the given course.
   *
   * endpoint: v1/course/<faculty acronym>/<course number>/schedule.<return type>
   * @example v1/course/CS/135/schedule.json
   */
  private function course_schedule_by_id($course_id, $return_type) {
    $term = $this->get_term_input();

    $course_result = $this->fetch_course_by_id($course_id, $this->get_calendar_years_from_term($term));
    if (!empty($course_result)) {
      $course = null;
      foreach ($course_result as $row) {
        $course = $row;
      }
      if ($course) {
        $db = $this->select_detailed_class_info(Database::instance('uwdata_schedule'));

        $result = $db->
          from('classes')->
          where('faculty_acronym', $course->faculty_acronym)->
          where('course_number', $course->course_number)->
          where('term', $term)->
          get();

        $classes = array();
        foreach ($result as $row) {
          $classes []= array('class' => $row);
        }

        if (!empty($classes)) {
          $data = array('classes' => $classes);
        } else {
          $data = $this->error_data('No classes this term');
        }

      } else {      
        $data = $this->error_data('No course exists with this id');
      }

    } else {
      $data = $this->error_data('No course exists with this id');
    }

    $this->echo_formatted_data($data, $return_type);
  }

  /**
   * Search the course list by title and description.
   *
   * endpoint: v1/course/search.<return type>?q=<query>
   * @example v1/course/search.json?q=Rhetoric
   */
  private function course_search($return_type) {
    $query = $this->input->get('q');
    if ($query) {
      $db = $this->select_detailed_course_info($this->get_db());

      if (preg_match('/^([a-z]+)\s*([0-9]+[a-z]?)$/i', trim($query), $match)) {
        // They're searching for a specific course.
        $faculty_acronym = $match[1];
        $course_number = $match[2];
        $course_result = $this->fetch_course_by_number($faculty_acronym, $course_number, $return_type);

        if (count($course_result)) {
          $course = null;
          foreach ($course_result as $row) {
            $course = $row;
          }
          $result = array(
            'page_index' => 0,
            'results_per_page' => 1,
            'page_result_count' => 1,
            'total_result_count' => 1,
            'courses' => array(array('course' => $course))
          );

        } else {  
          $result = $this->error_data('No courses found');
        }

      } else if (preg_match('/^[a-z0-9\'" \-]+$/i', $query)) {
        $perpage = $this->input->get('perpage', '10');
        $page = $this->input->get('page', '0');
        if (preg_match('/^[0-9]+$/', $perpage) && preg_match('/^[0-9]+$/', $page)) {
          $results = $db->
            from('courses')->
            where('MATCH (title, description) AGAINST ("'.mysql_escape_string($query).'")')->
            limit($perpage, $page * $perpage)->
            get();

          $total_results = $db->
            from('courses')->
            select('COUNT(*) as total_results')->
            where('MATCH (title, description) AGAINST ("'.mysql_escape_string($query).'")')->
            limit(1)->
            get();

          $total_result_count = 0;
          foreach ($total_results as $row) {
            $total_result_count = $row->total_results;
            break;
          }

          if ($total_result_count > 0) {
            $result = array();
            foreach ($results as $row) {
              $result []= array('course' => $row);
            }

            $result = array(
              'page_index' => $page,
              'results_per_page' => $perpage,
              'page_result_count' => count($results),
              'total_result_count' => $total_result_count,
              'courses' => $result
            );

          } else {
            $result = $this->error_data('No courses found');
          }
        } else {
          $result = $this->error_data('Invalid pagination arguments. Numbers only, please.');
        }

      } else {
        $result = $this->error_data('Illegal characters found in the query');
      }

    } else {
      $result = $this->error_data('Please provide a ?q=<query> expression.');
    }

    $this->echo_formatted_data($result, $return_type);
  }

  //////////////////////////////////
  //////////////////////////////////

  /**
   * Information about the given professor.
   *
   * endpoint: v1/prof/<instructor id>.<return type>
   * @example v1/prof/1409.json
   */
  private function prof_by_id($instructor_id, $return_type) {
    $db = Database::instance('uwdata_schedule');

    $results = $this->select_detailed_prof_info($db)->
      from('instructors')->
      where('id', $instructor_id)->
      limit(1)->
      get();

    if (count($results)) {
      $result = array();
      foreach ($results as $row) {
        $result = array('professor' => $row);
      }

    } else {
      $result = $this->error_data('No professor exists with this id');
    }

    $this->echo_formatted_data($result, $return_type);
  }

  /**
   * All of the timeslots the given professor is running.
   *
   * endpoint: v1/prof/<instructor id>/timeslots.<return type>
   * @example v1/prof/1409/timeslots.json
   */
  private function prof_timeslots($instructor_id, $return_type) {
    $term = $this->get_term_input();

    $db = Database::instance('uwdata_schedule');
    $results = $db->
      from('classes')->
      select()->
      where('instructor_id', $instructor_id)->
      where('term', $term)->
      limit(10)->
      get();

    if (count($results)) {
      $result = array();
      $needed_reserves = array();
      foreach ($results as $row) {
        $result []= array('timeslot' => $row);

        $reserve_key = md5($row->class_number).md5($row->term);
        $needed_reserves[$reserve_key]= array(
          'class_number' => $row->class_number,
          'term' => $row->term,
          'index' => count($result)-1
        );
      }

      $db->
        from('reserves')->
        select();
      foreach ($needed_reserves as $key => $reserve) {
        $db->
          orwhere('class_number', $reserve['class_number'])->
          where('term', $reserve['term']);
      }
      $results = $db->get();
      foreach ($results as $row) {
        $reserve_key = md5($row->class_number).md5($row->term);
        $index = $needed_reserves[$reserve_key]['index'];
        if (!isset($result[$index]['timeslot']->reserves)) {
          $result[$index]['timeslot']->reserves = array('reserves' => array());
        }
        unset($row->class_number);
        unset($row->term);
        $result[$index]['timeslot']->reserves['reserves'] []= array('reserve' => $row);
      }
      $result = array('timeslots' => $result);
    } else {
      $result = $this->error_data('No classes currently being held by this professor');
    }

    $this->echo_formatted_data($result, $return_type);
  }

  /**
   * Search the professor list by first and last name.
   *
   * endpoint: v1/prof/search.<return type>?q=<query>
   * @example v1/prof/search.json?q=Larry Smith
   */
  private function prof_search($return_type) {
    $query = $this->input->get('q');
    if ($query) {
      $db = Database::instance('uwdata_schedule');

      if (preg_match('/^[a-z\'" \-]+$/i', $query)) {
        $perpage = $this->input->get('perpage', '10');
        $page = $this->input->get('page', '0');
        if (preg_match('/^[0-9]+$/', $perpage) && preg_match('/^[0-9]+$/', $page)) {

          $results = $this->select_detailed_prof_info($db)->
            from('instructors')->
            where('MATCH (first_name, last_name) AGAINST ("'.mysql_escape_string($query).'")')->
            limit($perpage, $page * $perpage)->
            get();

          $total_results = $db->
            from('instructors')->
            select('COUNT(*) as total_results')->
            where('MATCH (first_name, last_name) AGAINST ("'.mysql_escape_string($query).'")')->
            limit(1)->
            get();

          $total_result_count = 0;
          foreach ($total_results as $row) {
            $total_result_count = $row->total_results;
            break;
          }

          if ($total_result_count > 0) {
            $result = array();
            foreach ($results as $row) {
              $result []= array('professor' => $row);
            }

            $result = array(
              'page_index' => $page,
              'results_per_page' => $perpage,
              'page_result_count' => count($results),
              'total_result_count' => $total_result_count,
              'professors' => $result
            );

          } else {
            $result = $this->error_data('No professors found');
          }

        } else {
          $result = $this->error_data('Invalid pagination arguments. Numbers only, please.');
        }

      } else {
        $result = $this->error_data('Illegal characters found in the query');
      }

    } else {
      $result = $this->error_data('Please provide a ?q=<query> expression.');
    }

    $this->echo_formatted_data($result, $return_type);
  }

  //////////////////////////////////
  //////////////////////////////////

  /**
   * A list of all academic terms.
   *
   * endpoint: v1/term/list.<return type>
   */
  private function term_list($return_type) {
    $db = Database::instance('uwdata_schedule');

    $results = $db->
      from('terms')->
      select('term_id', 'term_season', 'term_year', '__last_touched as last_updated')->
      get();

    $result = array();
    foreach ($results as $row) {
      $result []= array('term' => $row);
    }

    $this->echo_formatted_data(array('terms' => $result), $return_type);
  }

  //////////////////////////////////
  //////////////////////////////////

  /**
   * Dump all courses in the given file format for the active term.
   *
   * endpoint: v1/dump/courses.<return type>
   */
  private function dump_courses($return_type) {
    $db = $this->select_detailed_course_info($this->get_db());

    $result = $db->
      from('courses')->
      get();

    $courses = array();
    foreach ($result as $row) {
      if ($return_type == 'xml') {
        $row->antireq_desc = htmlentities($row->antireq_desc);
      }
      $courses []= array('course' => $row);
    }
    
    $this->echo_formatted_data(array('courses' => $courses), $return_type);
  }

  /**
   * Dump all schedules in the given file format for the active term.
   *
   * endpoint: v1/dump/schedules.<return type>
   */
  private function dump_schedules($return_type) {
    $term = $this->get_term_input();
    
    $db = $this->select_detailed_class_info(Database::instance('uwdata_schedule'));

    $since = $this->input->get('since');
    if ($since) {
      $db->where('__last_touched > "'.mysql_escape_string($since).'"');
    }

    $result = $db->
      from('classes')->
      where('term', $term)->
      get();

    $classes = array();
    foreach ($result as $row) {
      
      $course_result = $this->fetch_course_by_number($row->faculty_acronym, $row->course_number);
      if (!empty($course_result)) {
        $course = null;
        foreach ($course_result as $course_row) {
          $course = $course_row;
        }
        if (isset($course->title)) {
          $row->title = $course->title;
        } else {
          $row->title = '';
        }
      }
        
      $classes []= array('class' => $row);
    }

    $this->echo_formatted_data(array('schedule' => $classes), $return_type);
  }

  //////////////////////////////////
  //////////////////////////////////

  /**
   * Fetch a course by the faculty acronym and course number.
   *
   * @example fetch_course_by_number('CS', '135');
   * @return  The db result object.
   */
  private function fetch_course_by_number($faculty_acronym, $course_number) {
    $db = $this->select_detailed_course_info($this->get_db());

    $result = $db->
      from('courses')->
      where('faculty_acronym', $faculty_acronym)->
      where('course_number', $course_number)->
      limit(1)->
      get();

    return $result;
  }

  /**
   * Fetch a course by the course id.
   *
   * @example fetch_course_by_id(12040);
   * @param   $cid        The course id.
   * @return  The db result object.
   */
  private function fetch_course_by_id($cid, $calendar_years = null) {
    $db = $this->select_detailed_course_info($this->get_db($calendar_years));

    $result = $db->
      from('courses')->
      where('cid', $cid)->
      limit(1)->
      get();

    return $result;
  }

  /**
   * Prime a db object with the necessary SELECT fields for a course.
   */
  private function select_detailed_course_info($db) {
    $db->select(
      'cid',
      'faculty_acronym',
      'course_number',
      'title',
      'description',
      'has_lec',
      'has_lab',
      'has_tst',
      'has_tut',
      'has_prj',
      'credit_value',
      'has_dist_ed',
      'only_dist_ed',
      'has_stj',
      'only_stj',
      'has_ren',
      'only_ren',
      'has_cgr',
      'only_cgr',
      'needs_dept_consent',
      'needs_instr_consent',
      'avail_fall',
      'avail_winter',
      'avail_spring',
      'prereq_desc',
      'antireq_desc',
      'crosslist_desc',
      'coreq_desc',
      'note_desc',
      'src_url',
      '__last_touched as last_updated');
    return $db;
  }

  /**
   * Prime a db object with the necessary SELECT fields for a class.
   */
  private function select_detailed_class_info($db) {
    $db->select(
      'class_number',
      'term',
      'faculty_acronym',
      'course_number',
      'component_section',
      'campus_location',
      'associated_class',
      'related_component_1',
      'related_component_2',
      'enrollment_cap',
      'enrollment_total',
      'wait_cap',
      'wait_tot',
      'tba_schedule',
      'start_time',
      'end_time',
      'days',
      'is_closed',
      'is_canceled',
      'building',
      'room',
      'instructor',
      'instructor_id',
      'note',
      '__last_touched as last_updated');
    return $db;
  }

  /**
   * Prime a db object with the necessary SELECT fields for a professor.
   */
  private function select_detailed_prof_info($db) {
    $db->select(
      'id',
      'first_name',
      'last_name',
      'ratemyprof_id',
      'number_of_ratings',
      'overall_quality',
      'ease',
      '__last_touched as last_updated');
    return $db;
  }

  /**
   * Internal helper for course_by_number and course_by_id.
   * Handles the common code for retrieving the course object and outputting it.
   */
  private function course_by_result($result, $return_type) {
    if (count($result)) {
      foreach ($result as $row) {
        $course = array('course' => $row);
      }
    } else {
      $course = $this->error_data('Unknown course');
    }

    $this->echo_formatted_data($course, $return_type);
  }

  /**
   * Internal helper for course_by_number and course_by_id.
   * Handles the common code for retrieving the course object and outputting it.
   */
  private function course_prereqs_by_result($result, $return_type) {
    if (count($result)) {
      foreach ($result as $row) {
        $course = $row;
      }
      if ($return_type == 'json') {
        $prereqs = array(
          'prereqs' => json_decode($course->prereqs, TRUE)
        );
      } else {
        $prereqs = array(
          'prereqs' => array(
            'json' => $course->prereqs
          )
        );
      }
    } else {
      $prereqs = $this->error_data("Unknown course");
    }

    $this->echo_formatted_data($prereqs, $return_type);
  }

  //////////////////////////////////
  //////////////////////////////////

  private function init_action(&$param1, &$param2, &$param3) {
    //$profiler = new Profiler;

    $api_key = $this->input->get('key');
    if (!$api_key) {
      throw new Kohana_Exception('api.errors.no_api_key');
    }

    $db = Database::instance('uwdata');
    $email_users_set = $db->
      select('email_users.is_validated', 'user_details.is_disabled')->
      from('email_users')->
      join('user_details', array('email_users.user_id' => 'user_details.id'))->
      where('user_details.public_api_key', $api_key)->
      limit(1)->
      get();

    if (!count($email_users_set)) {
      throw new Kohana_Exception('api.errors.nonexistent_key');
    }

    if ($param3) {
      list($param3, $return_type) = $this->action_info($param3);
    } else if ($param2) {
      list($param2, $return_type) = $this->action_info($param2);
    } else {
      list($param1, $return_type) = $this->action_info($param1);
    }

    if (!$param1) {
      throw new Kohana_404_Exception();
    }

    if (!$return_type) {
      throw new Kohana_Exception('api.errors.no_return_type');
    }

    foreach ($email_users_set as $row) {
      $email_user = $row;
    }
    if (!$email_user->is_validated) {
      $this->echo_formatted_data($this->error_data("The given API key is not activated. Please activate your account at uwdata.ca before issuing further requests."), $return_type);
      return null;
    }
    if ($email_user->is_disabled) {
      $this->echo_formatted_data($this->error_data("The given API key has been disabled. If you feel like this is a mistake, please contact us at accounts@uwdata.ca"), $return_type);
      return null;
    }

    return $return_type;
  }

  private function get_db($calendar_years = null) {
    $cal_year = $calendar_years ? $calendar_years : $this->input->get('cal', '20102011');
    if (!preg_match('/^[0-9]+$/', $cal_year)) {
      return null;
    }

    $db = Database::instance('uwdata_'.$cal_year);

    return $db;
  }

  private function clean_xml_data($data) {
    return preg_replace('/\s&\s/', '&amp;', $data);
  }

  private function create_xml_object(&$xml, $data) {
    foreach ($data as $name => $objectdata) {
      if (is_array($objectdata) || is_object($objectdata)) {
        $object = $xml->addChild($name);
        foreach ($objectdata as $key => $item) {
          if (is_array($item)) {
            $this->create_xml_object($object, $item);
          } else {
            $object->addChild($key, $this->clean_xml_data($item));
          }
        }
      } else {
        $xml->addChild($name, $this->clean_xml_data($objectdata));
      }
    }
  }

  private function echo_formatted_data($data, $datatype) {
    Benchmark::start('echo_formatted_data');

    switch (strtolower($datatype)) {
      case 'json': {
        header('Content-type: application/json');

        $json = json_encode($data);

        $callback = $this->input->get('jsonp_callback');
        if ($callback) {
          echo $callback.'('.$json.');';
        } else {
          echo $json;
        }
        break;
      }

      case 'xml': {
        header('Content-type: application/xml');

        $xml = '<?xml version="1.0" encoding="UTF-8"?><result></result>';
        $xml = simplexml_load_string($xml);

        $this->create_xml_object($xml, $data);

        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        echo $dom->saveXML();
        break;
      }

      case 'csv': {
        header('Content-type: text/csv');

        $outstream = fopen("php://output", 'w');

        if (!isset($data[0])) {
          $data = current($data);
        }
        $firstElm = current($data);
        if (!isset($firstElm[0])) {
          fputcsv($outstream, array_keys(get_object_vars(current(current($data)))), ',', '"');
        } else {
          fputcsv($outstream, array_keys(get_object_vars(current($data))), ',', '"');
        }
        function __outputCSV(&$vals, $key, $filehandler) {
          if (!isset($vals[0])) {
            fputcsv($filehandler, get_object_vars(current($vals)), ',', '"');
          } else {
            fputcsv($filehandler, get_object_vars($vals), ',', '"');
          }
        }
        array_walk($data, '__outputCSV', $outstream);

        fclose($outstream);
        break;
      }

      default: {
        throw new Kohana_404_Exception();
        break;
      }
    }

    Benchmark::stop('echo_formatted_data');
  }

  private function action_info($action) {
    $parts = explode('.', $action);
    if (count($parts) > 2) {
      return null;
    }

    $parts[0] = strtolower($parts[0]);
    if (!isset($parts[1])) {
      return null;
    }
    return $parts;
  }

  private function get_term_input() {
    $term = $this->input->get('term');
    if (!$term) {
      $db = Database::instance('uwdata_schedule');

      $results = $db->
        from('terms')->
        select('MAX(term_id) term_id')->
        get();

      if (count($results)) {
        foreach ($results as $row) {
          $term = $row->term_id;
          break;
        }
      }
    }

    if (!preg_match('/^[0-9]+$/', $term)) {
      throw new Kohana_Exception();
    }

    return $term;
  }

  private function get_calendar_years_from_term($term) {
    $db = Database::instance('uwdata_schedule');

    $results = $db->
      from('terms')->
      select('calendar_years')->
      where('term_id', $term)->
      get();

    $calendar_years = null;
    if (count($results)) {
      foreach ($results as $row) {
        $calendar_years = $row->calendar_years;
        break;
      }
    }

    return $calendar_years;
  }

  private function error_data($text) {
    return array('error' => array('text' => $text));
  }

}