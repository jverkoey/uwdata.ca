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

    if (!IN_PRODUCTION) {
      echo $content;
    }

    $recipients = new Swift_RecipientList;
    $recipients->addTo($email);

    // Build the HTML message
    $message = new Swift_Message($subject, $message, "text/html");

    $succeeded = !!$swift->send($message, $recipients, $from);

    $swift->disconnect();

    return $succeeded;
  }

  private function send_api_key_email($email, $validation_key, $public_key, $private_key) {
    return $this->send_email($email, 'Your uwdata API keys', <<<EMAIL
<div style="font-family: arial,helvetica,clean,sans-serif;color:#333">
<h1 style="font-size:2em;color:#999">Welcome to the UW Data developer program</h1>
<p>Your public API key: $public_key<br/>
Your private API key: $private_key</p>
<p>Keep this email around or save these keys somewhere. If you do lose the keys you'll be
able to request your API keys again from uwdata.ca.</p>
<p>Before you can use these keys you'll need to
<a href="http://uwdata.ca/account/activate/$validation_key">activate your account</a>.</p>
<p style="color: #999">- Jeff Verkoeyen</p>
</div>
EMAIL
    );
  }

  private function send_coming_soon_email($email, $validation_key) {
    return $this->send_email($email, 'Thanks for your interest in uwdata', <<<EMAIL
<div style="font-family: arial,helvetica,clean,sans-serif;color:#333">
<h1 style="font-size:2em;color:#999">We'll keep you posted</h1>
<p>Over the next few weeks we'll be rolling out uwdata for more and more people. As soon as the API
is available for general users, you'll be one of the first to know!</p>
<p>In the meantime, feel free to
<a href="http://uwdata.ca/account/activate/$validation_key">activate your account</a>.</p>
<p style="color: #999">- Jeff Verkoeyen</p>
</div>
EMAIL
    );
  }

  private function getUniqueCode($salt, $length = "") {	
  	$code = md5(uniqid(rand(), true).$salt.'uwdatumz');
  	if ($length != "") return substr($code, 0, $length);
  	else return $code;
  }

  public function completed() {
	  if (!IN_PRODUCTION) {
		  $profiler = new Profiler;
		}

    if ($this->session->get('just_completed')) {
  		$content = new View('account_signup_completed');
  		$this->prepend_title("Account signup completed");

      $this->render_markdown_template($content);
    } else {
      url::redirect('/');
    }
  }

  public function request() {
	  if (!IN_PRODUCTION) {
		  $profiler = new Profiler;
		}

    $post = new Validation($_POST);
    $post->add_rules('email', 'required', array('valid','email'));

    $is_resending = $this->input->get('resend', 0);
    if ($is_resending || $post->validate()) {
      $db = Database::instance();

      // Check for re-entry of the page here.
      if ($is_resending) {
        $email = trim($this->session->get('saved_email'));
        if (!$email || !valid::email($email)) {
          if (!$email) {
            $error_str_id = 'form_error_messages.email.required';
          } else {
            $error_str_id = 'form_error_messages.email.email';
          }
          $this->session->set_flash('signup_email_error', Kohana::lang($error_str_id));
          url::redirect('signup');
          return;
        }

      } else {
        $email = trim($this->input->post('email'));
      }

      if (!$is_resending) {
        $email_users_set = $db->
          from('email_users')->
          select('user_id', 'last_emailed_timestamp', 'is_validated')->
          where('email', $email)->
          limit(1)->
          get();
      }

      if (!$is_resending && count($email_users_set)) {
        foreach ($email_users_set as $row) {
          $email_user = $row;
        }
		    $this->prepend_title("Sign up");
  		  if ($email_user->is_validated) {
          $this->render_markdown_template(new View('account_already_active'));
  		  } else {
  		    $this->session->set_flash('saved_email', $email);
          $this->render_markdown_template(new View('account_resend_email'));
  		  }

      } else {
        $validation_key = $this->getUniqueCode($email, 32);

        // What kind of email address is it?
        if (eregi('@([a-z0-9]+\.)*uwaterloo\.ca$', $email)) {
          $public_api_key = $this->getUniqueCode($salt, 32);
          $private_api_key = $this->getUniqueCode($salt, 32);
          $successfully_sent_email = $this->send_api_key_email(
            $email, $validation_key, $public_api_key, $private_api_key);

        } else {
          $successfully_sent_email = $this->send_coming_soon_email($email, $validation_key);
        }

        if (!$is_resending) {
          $user_details_values = array(
            'primary_email' => $email
          );
          if (isset($public_api_key) && isset($private_api_key)) {
            $user_details_values['public_api_key'] = $public_api_key;
            $user_details_values['private_api_key'] = $private_api_key;
          }
          $user_details = $db->insert('user_details', $user_details_values);
        }

        if (!$is_resending) {
          $email_users_values = array(
            'email'           => $email,
            'validation_key'  => $validation_key,
            'user_id'         => $user_details->insert_id()
          );
          $db->insert('email_users', $email_users_values);

          if ($successfully_sent_email) {
            $db->
              from('email_users')->
              set('last_emailed_timestamp', 'CURRENT_TIMESTAMP', $disable_escaping = true)->
              where('email', $email)->
              update();
          }

        } else {
          if ($successfully_sent_email) {
            $db->
              from('email_users')->
              set('validation_key', $validation_key)->
              set('last_emailed_timestamp', 'CURRENT_TIMESTAMP', $disable_escaping = true)->
              where('email', $email)->
              update();
          }
        }

        $this->session->set_flash('just_completed', true);
        url::redirect('signup/completed');
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

    $this->prepend_title("Get an API key");

    return $content;
  }

} // End Welcome Controller