<?php

if(!isset($_GET['file_id']) OR !$_GET['file_id']) {
    header("location: http://google.com/");
}

//This is an injection to test if mod_rewrite is working for imree-php/file/abc in the setup.php process
if($_GET['file_id'] === 'abc') {
    die('1');
}

require_once('../config.php');
$original_request = explode("?",$_SERVER['REQUEST_URI']);

/** Isn't really used since the only assets we care about are images */
if(count($original_request) > 1) {
    $original_vars = explode("&", urldecode($original_request[1]));
    foreach($original_vars as $var) {
            $parts = explode("=", $var);
            if(isset($parts[1])) {
                    $_GET[$parts[0]] = $parts[1];
            } else {
                    $_GET[$parts[0]] = "";
            }
    }
}

//Allows requests as .../file/200/1234.jpg  where 1234 is the asset id and 200 is the requested size
$size = false;
$size_as_folders = explode("/", substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "/file/") + 6));
if(count($size_as_folders) === 2) {
    $_GET['file_id'] = intval($size_as_folders[1]);
    $size = $size_as_folders[0];
}


if(isset($_GET['size'])) {
	$size = $_GET['size'];
	if(strpos($size, 'gsCacheBusterID') !== false) {
		$size = substr($size, 0, strpos($size, 'gsCacheBusterID'));
	}
}

imree_file($_GET['file_id'], $size);
