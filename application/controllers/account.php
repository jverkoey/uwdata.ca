<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    uwdata
 * @author     Jeff Verkoeyen
 * @copyright  (c) 2010 Jeff Verkoeyen
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
class Account_Controller extends Uwdata_Controller {

	const ALLOW_PRODUCTION = TRUE;

	public function validate($validation_key) {
	  if (!IN_PRODUCTION) {
		  $profiler = new Profiler;
		}

    // TODO: Figure out where to store magic constants.
    if ($validation_key && strlen($validation_key) == 32) {
      $db = Database::instance();
      $result_set = $db->
        select('email')->
        from('email_users')->
        where('validation_key', $validation_key)->
        get();

      if (!count($result_set)) {
    		$this->render_activation_failed_view(
    		  Kohana::lang('account_error_messages.activation.key_not_found'));
      }
      echo $validation_key;

    } else {
  		$this->render_activation_failed_view(
  		  Kohana::lang('account_error_messages.activation.key_not_found'));
    }
	}

  private function render_activation_failed_view($reason) {
		$content = new View('account_activation_failed');
		$content->reason = $reason;
		$this->template->title = 'Unable to activate account | uwdata.ca';

    $this->render_markdown_template($content);
  }

} // End Welcome Controller