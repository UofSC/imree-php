<?php

require_once '../../config.php';
$page = new page("", "People Roles");

if(logged_in()) {
    
   
    $elements = array(
        new f_data_element('Subject Title','role_title','text'),
    );
    
    if(isset($_GET['row_id'])) {
        $string = f_data($elements, db_connect(), "roles", "role_id", $_GET['row_id']);
    } else {
        $string = f_data_list(db_connect(), "roles", "role_id", "role_title");
        $string .= f_data($elements, db_connect(), "roles", "role_id", false);
    }
    
    
} else {
    $string = "You need to log in to use this feature.";
}
$page->append_content($string);
echo $page;
