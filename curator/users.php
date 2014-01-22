<?php


require_once('../../config.php'); //assumes the config file is placed immediately outside the imree-php folder


$string = "
    <form action='#' method='POST'>
        <fieldset><legend>New User</legend>
            <input type='hidden' name='action' value='user_create'>
            <label for='new_username'>New Email</label><input type='text' name='new_username' >
            <button type='submit'>Create new user</button>
        </fieldset>
    </form>  
    ";

$page = new page($string, "Users");
echo $page;
