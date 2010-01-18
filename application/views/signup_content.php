<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
Goodbye curl. <em>Hello API.</em>
=================================

We're currently rolling out the API strictly to University of Waterloo students. Please let us know
your Waterloo email address, and we'll shoot you an email to verify it.

What if I don't have a Waterloo email?
--------------------------------------

You can still sign up! We'll let you know as soon as the API is available to non-UW students.

<form id="signup_form" method="POST" action="/signup/request">

<input type="text" name="email" placeholder="Your email" />
<input type="submit" class="submit_button" value="Request an API key" />

</form>

<? if (isset($signup_email_error) && $signup_email_error) { ?>
<div id="form_error">
  <?= $signup_email_error ?>
</div>
<? } ?>
