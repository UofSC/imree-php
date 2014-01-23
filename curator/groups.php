<?php

require_once '../../config.php';
$page = new page("", "People");

if(logged_in()) {
   
    $elements = array(
        new f_data_element('First Name','person_name_first','text'),
        new f_data_element('Last Name','person_name_last','text'),
        new f_data_element('Title','person_title','text'),
    );
    
    if(isset($_GET['row_id'])) {
        $string = f_data($elements, db_connect(), "people", "person_id", $_GET['row_id']);
    } else {
        $string = f_data_list(db_connect(), "people", "person_id", array('person_name_last','person_name_first'));
        $string .= f_data($elements, db_connect(), "people", "person_id", false);
    }
    
    
} else {
    $string = "You need to log in to use this feature.";
}
$page->append_content($string);
echo $page;
