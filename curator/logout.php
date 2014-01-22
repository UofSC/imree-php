<?php

require_once('../../config.php');
if(isset($_SESSION['uid'])) {
    $ulogin = new uLogin();
    $ulogin->Logout($_SESSION['uid']);
    $ulogin->SetAutologin($_SESSION['username'], false);
    unset($_SESSION['uid']);
    unset($_SESSION['username']);
    unset($_SESSION['loggedIn']);
}

$page = new page("",'Logged Out');
$page->add_message("You have been logged out.");
echo $page;