<?php

require_once '../../config.php';
$page = new page("", "Subjects");

if(logged_in()) {
    
   
    $elements = array(
        new f_data_element('Subject Title (internal)','subject_title','text'),
        new f_data_element('Subject Title (external)','subject_title_display','text'),
        new f_data_element('GeoLocation','subject_geolocation','text'),
    );
    
    if(isset($_GET['row_id'])) {
        $string = f_data($elements, db_connect(), "subjects", "subject_id", $_GET['row_id']);
    } else {
        $string = f_data_list(db_connect(), "subjects", "subject_id", "subject_title");
        $string .= f_data($elements, db_connect(), "subjects", "subject_id", false);
    }
    
    
} else {
    $string = "You need to log in to use this feature.";
}
$page->append_content($string);
echo $page;
