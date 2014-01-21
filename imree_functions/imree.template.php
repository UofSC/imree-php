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
    private $google_uid;
    public function __construct($body_content, $page_title='Admin') {
        global $google_analytics_account;
        $this->body_content = $body_content;
        $this->page_title = $page_title;
        $this->javascript_files_array = array();
        $this->javascript_files_array[] = 'js/vendor/modernizr-2.6.2.min.js';
        $this->css_files_array = array();
        $this->css_files_array[] = "css/normalize.min.css";
        $this->css_files_array[] = "css/main.css";
        $this->google_uid = $google_analytics_account;
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
                $string .= "\n\t\t\t<link rel='stylesheet' href='$url'>";
            }
            if(isset($this->css_raw_string) AND strlen($this->css_raw_string)) {
                $string .= "\n\t\t\t<style>
                    ".$this->css_raw_string."
                </style>";
            }
            foreach($this->javascript_files_array as $url) {
                $string .= "\n\t\t\t<script src='$url'></script>";
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
        $string = "
        <body>
            <!--[if lt IE 7]>
                <p class='chromeframe'>You are using an <strong>outdated</strong> browser. Please <a href='http://browsehappy.com/'>upgrade your browser</a> or <a href='http://www.google.com/chromeframe/?redirect=true'>activate Google Chrome Frame</a> to improve your experience.</p>
            <![endif]-->
            <navigation>
                <ul>
                    <li><a href='index.php'>Home</a></li>
                </ul>
            </navigation>
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
            <script src='//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js'></script>
            <script>window.jQuery || document.write('<script src='js/vendor/jquery-1.10.1.min.js'><\/script>')</script>

            <script src='js/plugins.js'></script>
            <script src='js/main.js'></script>
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
        $string .= " 
        </body>\n</html>";
        return $string;
    }
}