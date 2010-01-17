<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Default Kohana controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class V1_Controller extends Controller {

	// Disable this controller when Kohana is set to production mode.
	// See http://docs.kohanaphp.com/installation/deployment for more details.
	const ALLOW_PRODUCTION = FALSE;

  private function get_db() {
    $cal_year = $this->input->get('cal', '20092010');
    if (!ereg('^[0-9]+$', $cal_year)) {
      return null;
    }

	  $db = Database::instance('uwdata'.$cal_year);

    return $db;
  }

	public function faculty($primary_action, $param1 = null) {
		//$profiler = new Profiler;

    if ($param1) {
      list($action, $returntype) = $this->action_info($param1);
    } else {
      list($action, $returntype) = $this->action_info($primary_action);
    }

    if (!$action) {
      throw new Kohana_404_Exception('Unknown API action');
    }

    $db = $this->get_db();

    if ($action == 'list' && $param1 == null) {
      $result = $db->from('faculties')->select(array('acronym', 'name'))->get();

      $faculties = array();
      foreach ($result as $row) {
        $faculties []= array('faculty' => $row);
      }

      $this->echo_formatted_data(array('faculties' => $faculties), $returntype);

    } else if ($primary_action == 'courses') {
      $result = $db->from('courses')->select()->like('faculty_acronym', $action)->get();

      $courses = array();
      foreach ($result as $row) {
        $courses []= array('course' => $row);
      }

      $this->echo_formatted_data(array('courses' => $courses), $returntype);

    } else {
  	  $result = $db->from('faculties')->select(array('acronym', 'name'))->like('acronym', $action)->limit(1)->get();

      if (count($result)) {
        foreach ($result as $row) {
          $faculty = array('faculty' => $row);
        }
      } else {
        $faculty = array('error' => array('text' => "Unknown faculty"));
      }

      $this->echo_formatted_data($faculty, $returntype);
    }
	}

	public function course($param1, $param2 = null, $param3 = null) {
		//$profiler = new Profiler;

    if ($param3) {
      list($param3, $returntype) = $this->action_info($param3);
    } else if ($param2) {
      list($param2, $returntype) = $this->action_info($param2);
    } else {
      list($param1, $returntype) = $this->action_info($param1);
    }

    if (!$param1) {
      throw new Kohana_404_Exception('Unknown API action');
    }

    $db = $this->get_db();

    if (eregi('^[a-z]+$', $param1) && eregi('^[0-9]+[a-z]*$', $param2)) {
      if ($param3 == 'prereqs') {
        $result = $db->
          from('courses')->
          select(array(
            'prereqs',
            'prereq_desc'
          ))->
          like('faculty_acronym', $param1)->
          where('course_number', $param2)->
          get();

        if (count($result)) {
          foreach ($result as $row) {
            $course = $row;
          }
          if ($returntype == 'json') {
            $prereqs = json_decode($course->prereqs, TRUE);
          } else {
            $prereqs = array('prereqs' => array('json' => $course->prereqs));
          }
        } else {
          $prereqs = array('error' => array('text' => "Unknown course"));
        }

        $this->echo_formatted_data($prereqs, $returntype);

      } else {
        $result = $db->
          from('courses')->
          select()->
          like('faculty_acronym', $param1)->
          where('course_number', $param2)->
          get();

        if (count($result)) {
          foreach ($result as $row) {
            $course = array('course' => $row);
          }
        } else {
          $course = array('error' => array('text' => "Unknown course"));
        }

        $this->echo_formatted_data($course, $returntype);
      }

    } else if (eregi('^[0-9]+$', $param1)) {
      $result = $db->
        from('courses')->
        select()->
        where('cid', $param1)->
        get();

      if (count($result)) {
        foreach ($result as $row) {
          $course = array('course' => $row);
        }
      } else {
        $course = array('error' => array('text' => "Unknown course"));
      }

      $this->echo_formatted_data($course, $returntype);

    } else {
      throw new Kohana_404_Exception();
    }
	}

	public function prof($param1, $param2 = null, $param3 = null) {
		$profiler = new Profiler;

    if ($param3) {
      list($param3, $returntype) = $this->action_info($param3);
    } else if ($param2) {
      list($param2, $returntype) = $this->action_info($param2);
    } else {
      list($param1, $returntype) = $this->action_info($param1);
    }

    if (!$param1) {
      throw new Kohana_404_Exception('Unknown API action');
    }

    $db = Database::instance('uwdata_schedule');

    $term = $this->input->get('term');
    if (!$term) {
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
      return null;
    }


    if ($param1 == 'search') {
      $query = $this->input->get('q');
      if ($query) {
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
      $this->echo_formatted_data($result, $returntype);

    } else if (ereg('^[0-9]+$', $param1) && $param1 != 0) {
      if ($param2 == 'classes') {
        $results = $db->
          from('classes')->
          select()->
          where('instructor_id', $param1)->
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

        $this->echo_formatted_data($result, $returntype);

      } else if (!$param2) {
        $results = $db->
          from('instructors')->
          select()->
          where('id', $param1)->
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

        $this->echo_formatted_data($result, $returntype);
      }
    } else {
      throw new Kohana_404_Exception();
    }
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
        throw new Kohana_404_Exception('Unknown return type');
        break;
      }
    }
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

}