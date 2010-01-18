<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<? if (!IN_PRODUCTION) { ?>
Stop scraping. <em>Start building.</em>
=======================================

<div class="pitch" markdown="1">

The University of Waterloo's public data now available through a RESTful API.

Go ahead, give it a try.
------------------------

<div class="big_form">
<input type="text" id="course_search" placeholder="Search for courses" autocomplete="off" />
</div>
</div>

<div id="search" style="display: none" markdown="1">
Search Results
--------------
<div class="results">
</div>

</div>
<? } else { ?>
Coming soon...
==============
  
<? } ?>
