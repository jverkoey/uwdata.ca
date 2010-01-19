<?php

define('CACHE_EXPIRY_TIMESPAN', 60*60*24);

ini_set("memory_limit","50M");

/**
 * Fetch the given url by first checking the local cache and then the web.
 */
function fetch_url($url, $expiry = CACHE_EXPIRY_TIMESPAN, $post_params = null) {
  global $cookie;
  global $calendar_url;

  if ($post_params) {
    $post = array();
    foreach ($post_params as $key => $value) {
      $post []= $key.'='.htmlentities($value);
    }
    $post = implode('&', $post);
  } else {
    $post = '';
  }

  $cache_path = CACHE_PATH.md5($calendar_url).md5($url.$post);
  echo 'Fetching '.$url.'...memory: '.memory_get_usage().'...';
  if (file_exists($cache_path) &&
      filemtime($cache_path) >= time() - $expiry) {
    echo 'from cache.'."\n";
    $data = file_get_contents($cache_path);
  } else {
    echo 'from web...';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_USERAGENT, 'UWDataSpider/1.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1.7)');
    if ($post_params) {
      curl_setopt($ch, CURLOPT_POST, count($post_params));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    $data = curl_exec($ch);

/*
    $context = stream_context_create(array(
      'http' => array(
        'method'=>"GET",
        'header'=>"Accept-language: en-us,en;q=0.5\r\n".
                  "Referrer: http://uwdata.ca\r\n".
                  "User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7\r\n"
      )
    ));
    $data = file_get_contents($url, 0, $context);*/
    if ($data) {
      file_put_contents($cache_path, $data);
    }
    echo 'done.'."\n";
  }
  return $data;
}

// Magic from http://w-shadow.com/blog/2008/07/05/how-to-get-redirect-url-in-php/

/**
 * get_redirect_url()
 * Gets the address that the provided URL redirects to,
 * or FALSE if there's no redirect. 
 *
 * @param string $url
 * @return string
 */
function get_redirect_url($url){
  global $cookie;
	$redirect_url = null; 
 
	$url_parts = @parse_url($url);
	if (!$url_parts) return false;
	if (!isset($url_parts['host'])) return false; //can't process relative URLs
	if (!isset($url_parts['path'])) $url_parts['path'] = '/';
 
	$sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
	if (!$sock) return false;
 
	$request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n"; 
	$request .= 'Host: ' . $url_parts['host'] . "\r\n"; 
	$request .= 'Cookie: ' . $cookie . "\r\n"; 
	$request .= "Connection: Close\r\n\r\n"; 
	fwrite($sock, $request);
	$response = '';
	while(!feof($sock)) $response .= fread($sock, 8192);
	fclose($sock);

	if (preg_match('/^Location: (.+?)$/m', $response, $matches)){
		if ( substr($matches[1], 0, 1) == "/" )
			return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
		else
			return trim($matches[1]);
 
	} else {
		return $url;
	}
 
}

/**
 * get_all_redirects()
 * Follows and collects all redirects, in order, for the given URL. 
 *
 * @param string $url
 * @return array
 */
function get_all_redirects($url){
	$redirects = array();
	while ($newurl = get_redirect_url($url)){
		if (in_array($newurl, $redirects)){
			break;
		}
		$redirects[] = $newurl;
		$url = $newurl;
	}
	return $redirects;
}
 
/**
 * get_final_url()
 * Gets the address that the URL ultimately leads to. 
 * Returns $url itself if it isn't a redirect.
 *
 * @param string $url
 * @return string
 */
function get_final_url($url){
	$redirects = get_all_redirects($url);
	if (count($redirects)>0){
		return array_pop($redirects);
	} else {
		return $url;
	}
}