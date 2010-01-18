<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    uwdata
 * @author     Jeff Verkoeyen
 * @copyright  (c) 2010 Jeff Verkoeyen
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
class Welcome_Controller extends Uwdata_Controller {

	const ALLOW_PRODUCTION = TRUE;

	public function index() {
	  if (IN_PRODUCTION) {
		  $profiler = new Profiler;
		}

    $this->add_js_foot_file('js/jquery-1.4.min.js');
    $this->add_js_foot_file('js/jquery.placeholder.js');
    $this->add_js_foot_file('js/demo.js');

		$content = new View('welcome_content');

		$this->template->title = 'uwdata.ca';

		require Kohana::find_file('vendor', 'Markdown');
    $this->template->content = $content->render(FALSE, 'Markdown');

    $this->template->render(TRUE);
	}

} // End Welcome Controller