<?php

	/**
	 * Simple example of YahooApi Class use
	 * @author Daniel Boorn - daniel.boorn@gmail.com
	 * @copyright Daniel Boorn - daniel.boorn@gmail.com
	 * @license Apaache 2.0 License, Use Code At Own Risk, All Rights Reserved
	 * @requires YahooApi Class
	 */

	ini_set('display_errors', '1');
	require('YahooApi.php');

	//settings supplied to class
	$settings = array(
		'debug'=>true,//debug output
		'key'=>'<your api key here>',//api key
		'secret'=>'<your api secret here>',//api secret
		'verifier'=>null,//api request token verifier, set to null first to obtain verifier (if needed)
	);
	
	//get yahoo api helper
	$y = new YahooApi($settings);
	
	//example 1 -- get request from api as object (stdclass)
	//http://developer.yahoo.com/fantasysports/guide/team-resource.html
	//you may want to extend the YahooApi with a class for the specific Yahoo Api you are working with. In this case it's the sports apii
	$response = $y->getJson('http://fantasysports.yahooapis.com/fantasy/v2/team/223.l.431.t.9');
	var_dump($response);
	
	
	