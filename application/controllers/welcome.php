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
class Welcome_Controller extends Uwdata_Controller {

	const ALLOW_PRODUCTION = TRUE;

	public function index() {
		$profiler = new Profiler;

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