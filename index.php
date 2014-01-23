<?php

/**
 * IMREE - PHP
 * 
 * Project Description et al...
 * 
 * 
 * 
*/

if(!file_exists("../config.php")) {
	die('IMREE is not setup. Please <a href="setup.php">Setup</a> IMREE first.');
} else {
    require_once('../config.php');
    if($config_version < 0.0002) {
        echo "<p>Your version of the config file is out of date. Please delete config.php and re-run setup.php</p>";
    }
}

echo "<p>Sorry. There's nothing here yet.<p>
    <p>@todo: We ought to create a project overview here with links to the api, AIR github, and curator directory.</p>";

//@todo make initilizing function
//imree.do();

