<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    uwdata
 * @author     Jeff Verkoeyen
 * @copyright  (c) 2010 Jeff Verkoeyen
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
class Welcome_Controller extends Controller {

	const ALLOW_PRODUCTION = TRUE;

  public function __construct() {
    parent::__construct();

    if ($this->input->ip_address() == '67.159.14.85') {
      echo 'Your access to uwdata has been revoked on account of excessive API usage.<br/>';
      echo 'Cheers.';
      exit;
    }
  }

	public function index() {
	  url::redirect('http://uwdata.ca/');
	}

} // End Welcome Controller