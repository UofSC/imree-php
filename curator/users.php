<?php


require_once('../../config.php'); //assumes the config file is placed immediately outside the imree-php folder
$page = new page("", "Users");


if(logged_in()) {
    /**
     * This page needs to hook into the page class instead of using the f_data functions.
     */
    $string = "
        <form action='#' method='POST'>
            <fieldset><legend>New User</legend>
                <input type='hidden' name='action' value='user_create'>
                <label for='new_username'>New Email</label><input type='text' name='new_username' >
                <button type='submit'>Create new user</button>
            </fieldset>
        </form>  
        ";
} else {
    $string = "You need to log in to use this feature.";
}


$page->append_content($string);
echo $page;
