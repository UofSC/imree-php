<?php
require_once '../../config.php';
$string = "
    <form action='#' method='POST'>
        <fieldset><legend>Change Password</legend>
            <input type='hidden' name='action' value='user_change_password' >
            <label for='new_password1'>New Password</label><input type='password' name='new_password1' ><br>
            <label for='new_password2'>ReEnter Password</label><input type='password' name='new_password2' ><br>
            <button type='submit'>Change Password</button>
        </fieldset>
    </form>
    ";
$page = new page($string, "My IMREE");
echo $page;
