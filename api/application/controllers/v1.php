<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    uwdatav1
 * @author     Jeff Verkoeyen
 * @copyright  (c) 2010 Jeff Verkoeyen
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
class V1_Controller extends Controller {

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

    if (eregi('^[a-z]+$', $param1)) {
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
   */
	public function course($param1, $param2 = null, $param3 = null) {
	  $return_type = $this->init_action($param1, $param2, $param3);

    if (eregi('^[a-z]+$', $param1) && eregi('^[0-9]+[a-z]*$', $param2)) {
      // v1/course/<faculty acronym>/<course number>

      if ($param3 == 'prereqs') {
        // v1/course/<faculty acronym>/<course number>/prereqs.<return type>
        $this->course_prereqs_by_number($param1, $param2, $return_type);

      } else if (!$param3) {
          // v1/course/<faculty acronym>/<course number>.<return type>
        $this->course_by_number($param1, $param2, $return_type);

      } else {
        throw new Kohana_404_Exception();
      }

    } else if (eregi('^[0-9]+$', $param1)) {
      if ($param2 == 'prereqs') {
        // v1/course/<course id>/prereqs.<return type>
        $this->course_prereqs_by_id($param1, $return_type);

      } else if (!$param2) {
        // v1/course/<course id>.<return type>
        $this->course_by_id($param1, $return_type);

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

    if (ereg('^[0-9]+$', $param1)) {
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
      $faculty = array('error' => array('text' => "Unknown faculty"));
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

    $results = $db->
      from('instructors')->
      select()->
      where('id', $instructor_id)->
      limit(1)->
      get();

    if (count($results)) {
      $result = array();
      foreach ($results as $row) {
        $result = array('professor' => $row);
      }

    } else {
      $result = array('error' => array('text' => "No professor exists with this id"));
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
      $result = array('error' => array('text' => "No classes currently being held by this professor"));
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

      if (eregi('^[a-z \-]+$', $query)) {
        $results = $db->
          query('SELECT * FROM instructors WHERE MATCH (first_name, last_name) AGAINST ("'.mysql_escape_string($query).'") LIMIT 1;');

        if (count($results)) {
          foreach ($results as $row) {
            $result []= array('course' => $row);
          }
          $result = array('courses' => $result);

        } else {
          $result = array('error' => array('text' => "No instructors found"));
        }

      } else {
        $result = array('error' => array('text' => "Illegal characters found in the query"));
      }

    } else {
      $result = array('error' => array('text' => "Please provide a ?q=<query> expression."));
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
  private function fetch_course_by_id($cid) {
    $db = $this->select_detailed_course_info($this->get_db());

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
   * Internal helper for course_by_number and course_by_id.
   * Handles the common code for retrieving the course object and outputting it.
   */
  private function course_by_result($result, $return_type) {
    if (count($result)) {
      foreach ($result as $row) {
        $course = array('course' => $row);
      }
    } else {
      $course = array('error' => array('text' => "Unknown course"));
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
      $prereqs = array('error' => array('text' => "Unknown course"));
    }

    $this->echo_formatted_data($prereqs, $return_type);
  }

  //////////////////////////////////
  //////////////////////////////////

  private function init_action(&$param1, &$param2, &$param3) {
		$profiler = new Profiler;

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

    return $return_type;
  }

  private function get_db() {
    $cal_year = $this->input->get('cal', '20092010');
    if (!ereg('^[0-9]+$', $cal_year)) {
      return null;
    }

	  $db = Database::instance('uwdata'.$cal_year);

    return $db;
  }

  private function create_xml_object(&$xml, $data) {
    foreach ($data as $name => $objectdata) {
      $object = $xml->addChild($name);
  		foreach ($objectdata as $key => $item) {
  		  if (is_array($item)) {
  		    $this->create_xml_object($object, $item);
      	} else {
      	  $object->addChild($key, htmlentities($item));
      	}
      }
    }
  }

  private function echo_formatted_data($data, $datatype) {
    Benchmark::start('echo_formatted_data');

    switch (strtolower($datatype)) {
      case 'json': {
        echo json_encode($data);
        break;
      }

      case 'xml': {
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

    if (!ereg('^[0-9]+$', $term)) {
      throw new Kohana_Exception();
    }

    return $term;
  }

}