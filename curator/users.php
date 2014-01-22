<?php


require_once('../../config.php'); //assumes the config file is placed immediately outside the imree-php folder


$string = "
    <form action='#' method='POST'>
        <input type='hidden' name='action' value='user_create'>
        <label for='new_username'>New Email</label><input type='text' name='new_username' >
    </form>  
    ";

$page = new page($string, "Users");
echo $page;