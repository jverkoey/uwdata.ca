<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Oh noes! | uwdata.ca</title>
<?
  echo html::stylesheet(array('css/reset', 'css/common'), null, FALSE);
?>

<? if (!IN_PRODUCTION) { ?>
<style type="text/css">
<?php include Kohana::find_file('views', 'kohana_errors', FALSE, 'css') ?>
</style>
<? } ?>
</head>
<body>
<div id="page-wrapper">
<div id="page">
<div id="pageheader">
  <div class="fixedwidth">
    <div class="title"><a href="/"><span class="uwcolor">uw</span>data<span class="subtitle">.ca</span></a></div>
    <ul>
      <li><a href="/learnmore">Learn more</a></li>
      <li><a href="/signup" class="standout">Request an API key</a></li>
    </ul>
  </div>
</div>
<div id="pageheadershadow"></div>

<div class="fixedwidth">
<div id="content-frame-top"></div>
<div id="content-frame">
<div id="content">
  
<h1>Uh oh.</h1>
<h2><?php echo $message ?></h2>
<?php if ( !IN_PRODUCTION AND ! empty($line) AND ! empty($file)): ?>
<p><?php echo Kohana::lang('core.error_file_line', $file, $line) ?></p>
<?php endif ?>
<?php if ( !IN_PRODUCTION AND ! empty($trace)): ?>
<div id="framework_error" style="width:42em;margin:20px auto;">
<h3><?php echo Kohana::lang('core.stack_trace') ?></h3>
<?php echo $trace ?>
</div>
<?php endif ?>

</div><!-- content -->
</div><!-- content-frame -->
<div class="clearfix"></div>
<div id="content-frame-bottom"></div>

<div class="clearfix"></div>

</div> <!-- fixedwidth -->

<div class="clearfix"></div>
<div id="page-footer"></div>
</div> <!-- page -->

</div> <!-- pagewrapper -->

<div id="footer">
  <div class="fixedwidth">
    <div class="copyright">
      © Copyright 2010<br/>All content licensed under the <a href="http://www.apache.org/licenses/LICENSE-2.0">Apache License</a>, unless otherwise noted.<br/><br/>
      All data is gathered from the University of Waterloo's public information sets.<br/><br/>
      Yet another project by <a href="http://JeffVerkoeyen.com/">Jeff Verkoeyen</a>.
    </div>
    <div class="attribution">
<? if (!IN_PRODUCTION) { ?>
      Rendered in {execution_time} seconds, using {memory_usage} of memory.<br/>
<? } ?>
      Source hosted on <a href="http://github.com/jverkoey/uwdata.ca">github</a>.<br/>
      Made with the <a href="http://kohanaphp.com/">Kohana</a> framework.
    </div>
  </div>
</div>

<? if (IN_PRODUCTION) { ?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-12528590-1");
pageTracker._setDomainName(".uwdata.ca");
pageTracker._trackPageview();
} catch(err) {}</script>
<? } ?>

</body>
</html>