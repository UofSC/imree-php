<?php
/** 
 * imree.session.php
 * @author Cole Mendes <mendesc@email.sc.edu>
 */

require_once('../../config.php');

/******************************************
 * @todo                                  *
 * implement functions into API and AIR   *
 * create sessions table on live database *
 *****************************************/


/**
 * function build_session
 * Creates a new session
 * to be used where the session_key is currently generated in API command 'signage_mode'
 * @return $session_key - unique session_key for that ip_address of user
 */
function build_session(){
    $conn = db_connect();
    $ip = get_client_IP();
    $session_key = session_id();
    
    db_exec($conn, "INSERT INTO sessions (session_key, ip_address, date_time) 
                    VALUES ('".$session_key."',
                            '".$ip."', 
                            '".session_date_time()."')"
            );
    
    return $session_key;
}

/**
 * function is_logged_in
 * to be used with the api command "login"
 * @param type $logged
 */
function is_logged_in($logged=false, $session_key){
    $conn = db_connect();
    if($logged){
       db_exec($conn, "UPDATE `sessions` SET `is_logged_in` = 'Yes' WHERE `session_key` = '". $session_key ."'");
    } else { 
       db_exec($conn, "UPDATE `sessions` SET `is_logged_in` = 'No' WHERE `session_key` = '". $session_key ."'");
    } 
}

/**
 * function home_page_visits
 * to be used when the user closes the application (deactivate in AIR)
 * @param - total home visits
 */
function home_page_visits($visits, $session_key){
    $conn = db_connect();
    db_exec($conn, "UPDATE `sessions` SET `home_returns` = '". $visits ."' WHERE `session_key` = '". $session_key ."'");
}

/**
 * function session_searches
 * to be called with the api command 'search'
 * @param - $search
 * @param - $session_key
 */
function session_searches($search, $session_key){
    $conn = db_connect();
    db_exec($conn, "UPDATE `sessions` SET `searches` = CONCAT(searches, ' \"". $search ."\"') WHERE `session_key` = '". $session_key ."'");
}

/**
 * function session_date_time
 * @return - current date and time
 */
function session_date_time(){
    return date("Y-m-d H:i:s");
}

/**
 * function time_on_page
 */
function time_on_page(){
    //@todo be able to log time user spends on a page/asset/img/etc.
    
}

/**
 * function get_client_ip
 * @return $ip
 */
function get_client_IP(){
    $ip = $_SERVER['REMOTE_ADDR'];
    return $ip;
}

?>
