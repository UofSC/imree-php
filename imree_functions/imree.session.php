<?php
/** 
 * imree.session.php
 * @author Cole Mendes <mendesc@email.sc.edu>
 */

class imree_session
{
    public $session_key;

    /**
     * function build_session
     * Creates a new session
     * to be used where the session_key is currently generated in API command 'signage_mode'
     * @return $session_key - unique session_key for that ip_address of user
    */
   public function __construct() { 
       
   }
    
   public function build_session(){
        $conn = db_connect();
        $ip = $this->get_client_IP();
        $session_key = session_id();
        $insert = "INSERT INTO sessions (session_key, ip_address, date_time, is_logged_in) VALUES (".db_escape($session_key).",".db_escape($ip).",".db_escape($this->session_date_time()).", 0)";

        db_exec($conn, $insert);

        $this->session_key = $session_key;
    }

    /**
     * function is_logged_in
     * to be used with the api command "login"
     * instead of using a session key finds the most recent session at the users ip address
     * @param type $logged
     */
    public function log_in($logged){
        $conn = db_connect();
        if($logged){
           $log_query = "SELECT session_id FROM sessions WHERE ip_address = ". db_escape($this->get_client_IP()) . " ORDER BY date_time DESC LIMIT 1";
           $sesh = db_query($conn, $log_query);
           $q = build_update_query($conn, 'sessions', array('is_logged_in' => 1), "session_id = ".db_escape($sesh[0]['session_id']));
           db_exec($conn, $q);
        }
    }

    public function is_logged_in()
    {
        $conn = db_connect();
        $log_query = "SELECT is_logged_in FROM sessions WHERE ip_address = ". db_escape($this->get_client_IP()) . " ORDER BY date_time DESC LIMIT 1";
        $sesh = db_query($conn, $log_query);
        if($sesh[0]['is_logged_in'] == 1)
        {
            return true;
        }else{
            return false;
        }
    }
    /**
     * function home_page_visits
     * to be used when the user closes the application (deactivate in AIR)
     * @param - total home visits
     */
    public function home_page_visits($visits, $session_key){
        $conn = db_connect();
        db_exec($conn, "UPDATE sessions SET home_returns = '". db_escape($visits) ."' WHERE `session_key` = '". db_escape($session_key) ."'");
    }

    /**
     * function session_searches
     * to be called with the api command 'search'
     * @param - $search
     * @param - $session_key
     */
    public function session_searches($search, $session_key){
        $conn = db_connect();
        db_exec($conn, "UPDATE sessions SET searches = CONCAT(searches, ' \"". db_escape($search) ."\"') WHERE session_key = '". db_escape($session_key) ."'");
    }

    /**
     * function session_date_time
     * @return - current date and time
     */
    private function session_date_time(){
        return date("Y-m-d H:i:s");
    }

    /**
     * function time_on_page
     */
    public function time_on_page(){
        //@todo be able to log time user spends on a page/asset/img/etc.

    }

    /**
     * function get_client_ip
     * @return $ip
     */
    private function get_client_IP(){ //not entirely reliable
        $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }
}
?>
