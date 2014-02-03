<?php

/**
 * This file handles basic template things required to make the admin site of 
 * imree.
 */

class page {
    public $javascript_files_array;
    public $javascript_raw_string;
    public $css_files_array;
    public $css_raw_string;
    public $extra_head_raw;
    public $body_content;
    public $page_title;
    private $messages;
    private $google_uid;
    private $path;
    public function __construct($body_content, $page_title='Admin') {
        global $google_analytics_account, $imree_curator_absolute_path;
        $this->body_content = $body_content;
        $this->page_title = $page_title;
        $this->javascript_files_array = array();
        $this->javascript_files_array[] = 'js/vendor/modernizr-2.6.2.min.js';
        $this->javascript_files_array[] = 'js/vendor/jquery-1.10.2.js';
        $this->javascript_files_array[] = 'js/vendor/jquery-ui-1.10.4.custom.min.js';
        $this->javascript_files_array[] = 'js/vendor/jquery-ui.timepicker.js';
        $this->javascript_files_array[] = 'js/vendor/jquery-ui.datetime.js';
        $this->javascript_files_array[] = 'js/plugins.js';
        $this->javascript_files_array[] = 'js/main.js';
        $this->css_files_array = array();
        $this->css_files_array[] = "css/normalize.min.css";
        $this->css_files_array[] = "css/custom-theme/jquery-ui-1.10.4.custom.css";
        $this->css_files_array[] = "css/jquery-ui.timepicker.css";
        $this->css_files_array[] = "css/main.css";
        $this->google_uid = $google_analytics_account;
        $this->path = $imree_curator_absolute_path;
        $this->messages = array();
                
        if(filter_input(INPUT_POST, "action")){
            $this->process_user_action(filter_input(INPUT_POST, "action"));
        }
    }
    public function append_content($string) {
        $this->body_content .= $string;
    }
    public function say_html() {
        return $this->say_head() . $this->say_body_header() . $this->say_body_content() . $this->say_body_footer();
    }

    public function __toString() {
        return $this->say_html();
    }
    
    public function say_head() {
        $string = "<!DOCTYPE html>
        <!--[if lt IE 7]>      <html class='no-js lt-ie9 lt-ie8 lt-ie7'> <![endif]-->
        <!--[if IE 7]>         <html class='no-js lt-ie9 lt-ie8'> <![endif]-->
        <!--[if IE 8]>         <html class='no-js lt-ie9'> <![endif]-->
        <!--[if gt IE 8]><!--> <html class='no-js'> <!--<![endif]-->
        <head>
            <meta charset='utf-8'>
            <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>
            <title>".$this->page_title."</title>
            <meta name='description' content='IMREE Administrative/Cutorial Site'>
            <meta name='viewport' content='width=device-width'>
            ";
            foreach($this->css_files_array as $url) {
                $string .= "\n\t\t\t<link rel='stylesheet' href='".$this->path . '/' .$url . "'>";
            }
            if(isset($this->css_raw_string) AND strlen($this->css_raw_string)) {
                $string .= "\n\t\t\t<style>
                    ".$this->css_raw_string."
                </style>";
            }
            foreach($this->javascript_files_array as $url) {
                $string .= "\n\t\t\t<script src='".$this->path . $url . "'></script>";
            }
            if(isset($this->javascript_raw_string) AND strlen($this->javascript_raw_string)) {
                $string .= "\n\t\t\t<script>
                    ".$this->javascript_raw_string."
                </script>";
            }
            $string .= " 
        </head>
        ";
        return $string;
    }
    
    public function say_body_header() {
        global $imree_curator_absolute_path;
        $string = "
        <body>
            <!--[if lt IE 7]>
                <p class='chromeframe'>You are using an <strong>outdated</strong> browser. Please <a href='http://browsehappy.com/'>upgrade your browser</a> or <a href='http://www.google.com/chromeframe/?redirect=true'>activate Google Chrome Frame</a> to improve your experience.</p>
            <![endif]-->
            <navigation>
                <ul>
                    <li><a href='index.php'>Home</a></li>";
                   
                    if(isset($_SESSION['loggedIn']) AND $_SESSION['loggedIn'] === true) {
			$string .= "<li><a href='".$imree_curator_absolute_path."assets.php''>Assets</a></li>";
			$string .= "<li><a href='".$imree_curator_absolute_path."events.php''>Events</a></li>";
                        $string .= "<li><a href='".$imree_curator_absolute_path."exhibits.php''>Exhibits</a></li>";
                        $string .= "<li><a href='".$imree_curator_absolute_path."groups.php''>Groups</a></li>";
                        $string .= "<li><a href='".$imree_curator_absolute_path."metadata.php''>Metadata</a></li>";
			$string .= "<li><a href='".$imree_curator_absolute_path."people.php''>People</a></li>";
                        $string .= "<li><a href='".$imree_curator_absolute_path."roles.php''>Roles</a></li>";
                        $string .= "<li><a href='".$imree_curator_absolute_path."subjects.php''>Subjects</a></li>";
                        $string .= "<li><a href='".$imree_curator_absolute_path."myAccount.php''>My Account</a></li>";
                        $string .= "<li><a href='".$imree_curator_absolute_path."logout.php' class='logout-link'>Logout</a></li>";
                        
                    } else {
                        $string .= "<li><a href='#' class='login-link'>Login</a></li>";
                    }
                    $string .= "
                </ul>
            </navigation>";
            if(count($this->messages)) {
                $string .= "<section id='messages'>";
                foreach($this->messages as $message) {
                    $string .= "<div class='".$message['type']."'>".$message['content']."</div>";
                }
                $string .= "</section>";
            }
            $string .= "
            <section id='contents'>";
        return $string;
    }
    
    public function say_body_content() {
        return $this->body_content;
    }
    
    public function say_body_footer() {
        $string = "
            </section>
            <section id='footer'>
                Footer Information Here
            </section>
            ";
        if(strlen($this->google_uid)) {
            $string .= "
            <script>
                var _gaq=[['_setAccount','$this->google_uid'],['_trackPageview']];
                (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
                g.src='//www.google-analytics.com/ga.js';
                s.parentNode.insertBefore(g,s)}(document,'script'));
            </script>";
        }
        $string .= $this->login_form()." 
        </body>\n</html>";
        return $string;
    }
    
    public function login_form() {
        return "
        <form action='#' method='POST' id='login_form' class='hidden'>
	    <label for='user'>Email</label><input type='text' name='user'>
            <label for='pwd'>Password</label><input type='password' name='pwd'><br>
            <label for='autologin'>Remember Me</label><input type='checkbox' name='autologin' value='1'>
            <input type='hidden' name='action' value='login'>
            <input type='hidden' id='nonce' name='nonce' value='".ulNonce::Create('login')."'> 
        </form>
        ";
    }
    
    public function process_user_action($action) {
        switch ($action) {
            case "login":
                if(filter_input(INPUT_POST, 'user', FILTER_VALIDATE_EMAIL)) {
                    $ulogin = new uLogin();
                    if (isset($_POST['nonce']) && ulNonce::Verify('login', $_POST['nonce'])) {
                        if (isset($_POST['autologin'])) {
                            $_SESSION['appRememberMeRequested'] = true;
                        } else {
                            unset($_SESSION['appRememberMeRequested']);
                        }
                        $ulogin->Authenticate($_POST['user'], $_POST['pwd']);
                        if ($ulogin->IsAuthSuccess()) {
                            $_SESSION['uid'] = $ulogin->AuthResult;
                            $_SESSION['username'] = $_POST['user'];
                            $_SESSION['loggedIn'] = true;

                            if (isset($_SESSION['appRememberMeRequested']) && ($_SESSION['appRememberMeRequested'] === true)) {
                                // Enable remember-me
                                if ( !$ulogin->SetAutologin($_SESSION['username'], true)) {
                                    $this->add_message("The autologin feature is not working at this time.");
                                }
                                unset($_SESSION['appRememberMeRequested']);
                            } else {
                                // Disable remember-me
                                if ( !$ulogin->SetAutologin($_SESSION['username'], false)) {
                                    $this->add_message("The autologin feature is not working at this time.");
                                }
                            }
                        }
                    } else {
                        $this->add_message("You cannot refresh this page to log in again. Please retype your username and password to log in.");
                    }
                } else {
                    $this->add_message("The username you provided does not appear to be a vaild email address.", 'error');
                }
                break;
                
            default:
               break;
        }
    }
    
    public function add_message($content, $type='message') {
        $this->messages[] = array('content'=>$content, 'type'=>$type);
    }
    
    
}