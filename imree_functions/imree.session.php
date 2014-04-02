<?php
/** 
 * ...
 * @author Cole Mendes
 */

/**
 * This is a (poor) random string generator. Poor in that its using some 
 * cryptographic functions and is pretty ungodly efficient. For the sake of 
 * being bizzare, $random1 and $random2 function source strings.
 * @author Jason Steelman <uscart@gmail.com>
 * @param int $length the length of random string to return
 * @return string A string of length $length
 */
function random_string_gen($length = 256) {
	if($length == 30) return str_shuffle(MD5(microtime()));
	$random1 = "somethingfeelsrandomtomehereabcdefghijklmonpqrstuvqxyz";
	$random2 = "fortheloveofallthatsgoodandevilabcdefghijklmonpqurstuv";
	$string = str_shuffle(md5(microtime().str_shuffle($random1)) . md5(str_shuffle($random2).time()) . sha1(str_shuffle($random1.microtime())).sha1(str_shuffle($random2).time()).md5(str_shuffle($random1).time()).sha1(str_shuffle($random2).microtime()).sha1(str_shuffle($random1.microtime())).sha1(str_shuffle($random2).time()));
	return substr($string, 0, min($length, strlen($string)));
}




/**
 * Some Notes
 * Usability study:
 *  Asset views
 *  Date
 *  Time
 *  Time per page
 *  Page views
 *  Search Tracking
 *  Home page returns 
 * 
 * Start at sign_mode_loader in Main
 * PHP AS interaction toturial 
 * http://www.flashwonderland.com/actionscript-3-php-interaction-tutorials.html
 * API WILL NEED TO LOG SESSION ID, TIME, AND DATE.
 */







?>
