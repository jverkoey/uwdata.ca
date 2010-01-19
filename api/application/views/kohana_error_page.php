<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<style type="text/css">
<?php include Kohana::find_file('views', 'kohana_errors', FALSE, 'css') ?>
</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>There was an error with your request | uwdata api</title>
<base href="http://php.net/" />
</head>
<body>
<div id="framework_error" style="width:42em;margin:20px auto;">
<h3>d'oh</h3>
<p><code class="block"><?php echo $message ?></code></p>
<?php if ( !IN_PRODUCTION AND ! empty($line) AND ! empty($file)): ?>
<p><?php echo Kohana::lang('core.error_file_line', $file, $line) ?></p>
<?php endif ?>
<?php if ( ! empty($trace)): ?>
<h3><?php echo Kohana::lang('core.stack_trace') ?></h3>
<?php echo $trace ?>
<?php endif ?>
<p class="stats"><?php echo Kohana::lang('core.stats_footer') ?></p>
</div>
</body>
</html>