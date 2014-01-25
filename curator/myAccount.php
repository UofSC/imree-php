<?php
require_once '../../config.php';
$string = "";
$page = new page("", "My IMREE");

if(logged_in()) {
     if(isset($_POST['action']) AND $_POST['action'] === 'user_change_password') {
        if(filter_input(INPUT_POST, 'new_password1') === filter_input(INPUT_POST, 'new_password2')) {
            $ulogin = new uLogin();
            $ulogin->SetPassword($_SESSION['uid'], filter_input(INPUT_POST,'new_password1'));
            $page->add_message("Your password has been updated. You'll need it the next time you log in.");
        } else {
            $page->add_message("The passwords you enetered did not match.",'error');
        }
    }
     
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
