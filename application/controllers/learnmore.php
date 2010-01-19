<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    uwdata
 * @author     Jeff Verkoeyen
 * @copyright  (c) 2010 Jeff Verkoeyen
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
class Learnmore_Controller extends Uwdata_Controller {

	const ALLOW_PRODUCTION = TRUE;

	public function index() {
	  if (!IN_PRODUCTION) {
		  $profiler = new Profiler;
		}

		$content = new View('learn_more_content');
		$this->prepend_title("Learn More");
    $this->add_css_file('css/learnmore.css');

    $this->render_markdown_template($content);
	}

}