<?php


require_once('../../config.php'); //assumes the config file is placed immediately outside the imree-php folder

$string = "Welcome to IMREE.";

if(isset($_SESSION['loggedIn']) AND $_SESSION['loggedIn'] === true) {
    $string .= "You are logged in and ready to curate!";
} else {
    $string .= "Please <a href='#' class='login-link'>log in</a>.";
}

$page = new page($string, "IMREE");
echo $page;
