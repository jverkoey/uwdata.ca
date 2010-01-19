<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    uwdata
 * @author     Jeff Verkoeyen
 * @copyright  (c) 2010 Jeff Verkoeyen
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
class Uwdata_Controller extends Template_Controller {

	const ALLOW_PRODUCTION = FALSE;

	// Set the name of the template to use
	public $template = 'template';
	public $auto_render = FALSE;

  public function __construct() {
    parent::__construct();

    $this->session = Session::instance();

		$this->template->js_foot_files = array();
		$this->template->css_files = array();
		$this->template->title = array('uwdata.ca');
  }

  protected function add_js_foot_file($file) {
    $this->template->js_foot_files []= $file;
  }

  protected function add_css_file($file) {
    $this->template->css_files []= $file;
  }

  protected function prepend_title($text) {
    array_unshift($this->template->title, $text);
  }

  protected function render_markdown_template($content) {
		require Kohana::find_file('vendor', 'Markdown');
    $this->template->content = $content->render(FALSE, 'Markdown');

    $this->template->title = implode(' | ', $this->template->title);
    $this->template->render(TRUE);
  }

}