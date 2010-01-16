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
class Welcome_Controller extends Template_Controller {

	// Disable this controller when Kohana is set to production mode.
	// See http://docs.kohanaphp.com/installation/deployment for more details.
	const ALLOW_PRODUCTION = FALSE;

	// Set the name of the template to use
	public $template = 'template';
	public $auto_render = FALSE;

	public function index()
	{
		$profiler = new Profiler;

		$content = new View('welcome_content');

		$this->template->title = 'Welcome to Kohana!';

		require Kohana::find_file('vendor', 'Markdown');
    $this->template->content = $content->render(FALSE, 'Markdown');

    $this->template->render(TRUE);
	}

} // End Welcome Controller