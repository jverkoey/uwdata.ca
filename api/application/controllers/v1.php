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
    } if ($param2) {
      list($param2, $returntype) = $this->action_info($param2);
    } else {
      list($param1, $returntype) = $this->action_info($param1);
    }

    if (!$param1) {
      throw new Kohana_404_Exception('Unknown API action');
    }

    $db = $this->get_db();

    if (eregi('^[a-z]+$', $param1) && eregi('^[0-9]+[a-z]*$', $param2)) {
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

        foreach ($data as $name => $objectdata) {
          $object = $xml->addChild($name);
      		foreach ($objectdata as $key => $item) {
      		  if (is_array($item)) {
        		  foreach ($item as $itemname => $itemdata) {
          			$row = $object->addChild($itemname);
          			foreach ($itemdata as $key => $value) {
          				$row->addChild($key, htmlentities($value));
          			}
          		}
          	} else {
          	  $object->addChild($key, htmlentities($item));
          	}
          }
        }

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