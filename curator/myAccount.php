<?php
require_once '../../config.php';
$string = "";
$page = new page("", "My IMREE");

if(logged_in()) {
    /**
     * This page needs to hook into the page class instead of using the f_data functions.
     */
    $string .= "
    <form action='#' method='POST'>
        <fieldset><legend>Change Password</legend>
            <input type='hidden' name='action' value='user_change_password' >
            <label for='new_password1'>New Password</label><input type='password' name='new_password1' ><br>
            <label for='new_password2'>ReEnter Password</label><input type='password' name='new_password2' ><br>
            <button type='submit'>Change Password</button>
        </fieldset>
    </form>
    ";
} else {
    $string .= "<div>You need to login to use this page.";
}

$page->append_content($string);

echo $page;
