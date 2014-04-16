<?php
/** 
 * imree.session.php
 * @author Cole Mendes <mendesc@email.sc.edu>
 */





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
    $insert = "INSERT INTO sessions (session_key, ip_address, date_time) VALUES (".db_escape($session_key).",".db_escape($ip).",".db_escape(session_date_time()).")";
    
    db_exec($conn, $insert);
    
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
       db_exec($conn, "UPDATE sessions SET is_logged_in = 1 WHERE session_key = ". db_escape($session_key));
    } else { 
       db_exec($conn, "UPDATE sessions SET is_logged_in = 0 WHERE session_key = ". db_escape($session_key));
    } 
}

/**
 * function home_page_visits
 * to be used when the user closes the application (deactivate in AIR)
 * @param - total home visits
 */
function home_page_visits($visits, $session_key){
    $conn = db_connect();
    db_exec($conn, "UPDATE sessions SET home_returns = '". db_escape($visits) ."' WHERE `session_key` = '". db_escape($session_key) ."'");
}

/**
 * function session_searches
 * to be called with the api command 'search'
 * @param - $search
 * @param - $session_key
 */
function session_searches($search, $session_key){
    $conn = db_connect();
    db_exec($conn, "UPDATE sessions SET searches = CONCAT(searches, ' \"". db_escape($search) ."\"') WHERE session_key = '". db_escape($session_key) ."'");
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
