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

	public function faculty($action) {
		$profiler = new Profiler;

    list($action, $returntype) = $this->action_info($action);

    if (!$action) {
      throw new Kohana_404_Exception('Unknown API action');
    }

    if ($action == 'list') {
  	  $db = Database::instance();
      $result = $db->from('faculties')->select(array('acronym', 'name'))->get();

      $faculties = array();
      foreach ($result as $row) {
        $faculties []= $row;
      }

      $this->echo_formatted_data($faculties, $returntype, 'faculties', 'faculty');

    } else {
  	  $db = Database::instance();
  	  $result = $db->from('faculties')->select(array('acronym', 'name'))->like('acronym', $action)->limit(1)->get();

      $faculty = array();
      foreach ($result as $row) {
        $faculty = $row;
      }

      $this->echo_formatted_data($faculty, $returntype, null, 'faculty');
    }
	}

  private function echo_formatted_data($data, $datatype, $multitypename = 'objects', $singletypename = 'object') {
    switch (strtolower($datatype)) {
      case 'json': {
        echo json_encode($data);
        break;
      }

      case 'xml': {
        if (is_array($data)) {
    		  $xml = '<?xml version="1.0" encoding="UTF-8"?><result><'.$multitypename.' type="array"></'.$multitypename.'></result>';
    		} else {
    		  $xml = '<?xml version="1.0" encoding="UTF-8"?><result><'.$singletypename.'></'.$singletypename.'></result>';
    		}
    		$xml = simplexml_load_string($xml);

        if (is_array($data)) {
      		foreach ($data as $item) {
      			$row = $xml->$multitypename->addChild($singletypename);
      			foreach ($item as $key => $value) {
      				$row->addChild($key, htmlentities($value));
      			}
          }
        } else {
    			foreach ($data as $key => $value) {
    				$xml->$singletypename->addChild($key, htmlentities($value));
    			}
        }

    		echo $xml->asXML();
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