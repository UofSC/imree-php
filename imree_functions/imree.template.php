<?php

/**
 * This file handles basic template things required to make the admin site of 
 * imree.
 */

/**
 * 
 * @todo finish this dumb thing
 */
class page {
    public $javascript_files_array;
    public $javascript_raw_string;
    public $css_files_array;
    public $css_raw_string;
    public $extra_head_raw;
    private $html;
    public function __construct($body_content, $page_title='Admin') {
        
    }
    public function __toString() {
        $string = "";
        return $string;
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
        <title></title>
        <meta name='description' content=''>
        <meta name='viewport' content='width=device-width'>

        <link rel='stylesheet' href='css/normalize.min.css'>
        <link rel='stylesheet' href='css/main.css'>

        <script src='js/vendor/modernizr-2.6.2.min.js'></script>
    </head>";
    return $string;
    }
}