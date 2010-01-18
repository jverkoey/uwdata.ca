<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    uwdata
 * @author     Jeff Verkoeyen
 * @copyright  (c) 2010 Jeff Verkoeyen
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
class Signup_Controller extends Uwdata_Controller {

	const ALLOW_PRODUCTION = TRUE;

	public function index() {
	  if (!IN_PRODUCTION) {
		  $profiler = new Profiler;
		}

    $content = $this->get_signup_content_view();

    $content->signup_email_error = $this->session->get('signup_email_error');

    $this->render_markdown_template($content);
	}

  private function send_email($email, $subject, $content) {
    $swift = email::connect();

    $from = 'no-reply@uwdata.ca';
    $subject = $subject;
    $message = $content;

    $recipients = new Swift_RecipientList;
    $recipients->addTo($email);

    // Build the HTML message
    $message = new Swift_Message($subject, $message, "text/html");

    $succeeded = !!$swift->send($message, $recipients, $from);

    $swift->disconnect();

    return $succeeded;
  }

  private function send_api_key_email($email) {
    return $this->send_email($email, 'Your uwdata API keys', <<<EMAIL
<div style="font-family: arial,helvetica,clean,sans-serif">
<h1>Welcome to the UW Data developer program!</h1>
</div>
EMAIL
    );
  }

  private function send_coming_soon_email($email) {
    return $this->send_email($email, 'Thanks for signing up!', <<<EMAIL
<div style="font-family: arial,helvetica,clean,sans-serif">
<h1>Thanks for your interest in the UW Data developer program!</h1>
</div>
EMAIL
    );
  }

  public function request() {
    $post = new Validation($_POST);
    $post->add_rules('email', 'required', array('valid','email'));

    if ($post->validate()) {
      $db = Database::instance();

      $email = trim($this->input->post('email'));

      $result_set = $db->
        from('email_users')->
        select('user_id', 'last_emailed_timestamp', 'is_validated')->
        where('email', $email)->
        get();

      if (count($result_set)) {
        

      } else {
        // What kind of email address is it?
        if (eregi('@([a-z0-9]+\.)*uwaterloo\.ca$', $email)) {
          $this->send_api_key_email($email);

        } else {
          $this->send_coming_soon_email($email);
        }
/*
        $result_set = $db->
          insert('email_users', array(
            'email' => $email
          ));*/
      }

    } else {
      $errors = $post->errors();
      $this->session->set_flash('signup_email_error', Kohana::lang('form_error_messages.email.'.$errors['email']));
      url::redirect('signup');
    }
  }

  private function get_signup_content_view() {
    $this->add_js_foot_file('js/jquery-1.4.min.js');
    $this->add_js_foot_file('js/jquery.placeholder.js');
    $this->add_js_foot_file('js/signup.js');
    $this->add_css_file('css/signup.css');

		$content = new View('signup_content');

		$this->template->title = 'Get an API key | uwdata.ca';

    return $content;
  }

  private function render_markdown_template($content) {
		require Kohana::find_file('vendor', 'Markdown');
    $this->template->content = $content->render(FALSE, 'Markdown');

    $this->template->render(TRUE);
  }

} // End Welcome Controller