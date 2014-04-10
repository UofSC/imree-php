<?php
/** 
 * imree.session.php
 * @author Cole Mendes
 */

require_once('../../config.php');

function build_session(){
    $session_id = get_session_id();
    $ip = get_client_IP();
    //STORE IP AND SESSION ID db_exec($conn, "UPDATE 'sessions' SET ...");
}

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
 * function is_logged_in
 * 
 * @param type $logged
 */
function is_logged_in($logged=false){
    if($logged){
       //db_exec($conn, "UPDATE 'sessions' SET 'is_logged_in' = 'Yes' WHERE 'session_id' = " . get_session_id());
    } else { 
       //db_exec($conn, "UPDATE 'sessions' SET 'is_logged_in' = 'No' WHERE 'session_id' = " . get_session_id()) not logged in
    } 
}

/**
 * function home_page_visits
 */
function home_page_visits(){
    //@todo count home page visits per session
    
}

/**
 * function session_date_time
 */
function session_date_time(){
    //@todo log date and time of session start
    //db_exe() date("Y-m-d H:i:s");
}

/**
 * function time_on_page
 */
function time_on_page(){
    //@todo be able to log time user spends on a page/asset/img/etc.
    
}

function get_client_IP(){
    $ip = $_SERVER['REMOTE_ADDR'];
    return $ip;
}
 

//@todo more functions for session tracking

/**
 * Some Notes
 * Usability study:
 *  IsLoggedIn?
 *  Asset views
 *  Date
 *  Time
 *  Time per page
 *  Page views
 *  Search Tracking
 *  Home page returns  
 */
?>
