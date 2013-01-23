<?php
/*
  Plugin Name: WP CoffeeScript by Se7enSky
  Plugin URI: http://github.com/Se7enSky/wp-coffeescript
  Description: Wordpress plugin adding CoffeeScript support. Rendering is done using free Se7enSky SAAS Render service using standart fresh Node.js CoffeeScript.
  Tags: coffeescript, coffee, javascript, js, script, scripting
  Author: Se7enSky studio
  Author URI: http://github.com/Se7enSky
  Version: 1.1
  License: The MIT License
  License file: LICENSE
 */

namespace se7ensky\coffeescript;

define('CACHE', __DIR__ . '/cache');

function pipeRender($url, $source) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $source);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$output = curl_exec($ch);

	curl_close($ch);
	return $output;
}

function coffee2js($source) {
	return pipeRender('http://render.se7ensky.com/coffeescript', $source);
}

function host() {
	$uri = $_SERVER['REQUEST_URI'];
	if (preg_match('/^(.*)\.js$/', $uri, $matches)) {
		$name = $matches[1];
		$srcFile = __DIR__ . '/../../..' . $name . '.coffee';
		
		$cachedFile = CACHE . $uri;
		if (file_exists($srcFile)) {
			if (!file_exists($cachedFile) || filemtime($cachedFile) < filemtime($srcFile)) {
				@mkdir(dirname($cachedFile), 0770, true);
				$source = file_get_contents($srcFile);
				$compiled = coffee2js($source);
				file_put_contents($cachedFile, $compiled);
			}
			header("Content-Type: text/javascript");
			readfile($cachedFile);
			die; // stop further request handling
		}
	}
}

add_filter('init', 'se7ensky\\coffeescript\\host');
