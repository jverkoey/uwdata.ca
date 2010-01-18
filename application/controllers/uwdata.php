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
class Uwdata_Controller extends Template_Controller {

	const ALLOW_PRODUCTION = FALSE;

	// Set the name of the template to use
	public $template = 'template';
	public $auto_render = FALSE;

  public function __construct() {
    parent::__construct();

		$this->template->js_foot_files = array();
  }

  protected function add_js_foot_file($file) {
    $this->template->js_foot_files []= $file;
  }
}