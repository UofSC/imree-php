<?php

require_once '../../config.php';
$page = new page("", "Events");

if(logged_in()) {
    
    $event_parent_options = new f_data_element_options();
		$event_parent_options->normalized_table = "events";
		$event_parent_options->normalized_table_none_label = "none";
		$event_parent_options->normalized_table_none_value = "";
		$event_parent_options->normalized_table_primary_key_column = "event_id";
		$event_parent_options->normalized_table_primary_label_column = "event_name";
	
    $elements = array(
        new f_data_element('Event Name','event_name','text'),
        new f_data_element('Date Start','event_date_start','datetime'),
	   new f_data_element('Date Start is approximate','event_date_start_approx','checkbox','1'),
	   new f_data_element('Date End','event_date_end','datetime'),
	   new f_data_element('Date End is approximate','event_date_end_approx','checkbox','1'),
        new f_data_element('Parent','event_parent_id','select','','0','',$event_parent_options),
    );
    
    if(isset($_GET['row_id'])) {
        $string = f_data($elements, db_connect(), "events", "event_id", $_GET['row_id']);
    } else {
        $string = f_data_list(db_connect(), "events", "event_id", "event_name");
        $string .= f_data($elements, db_connect(), "events", "event_id", false);
    }
    
    
} else {
    $string = "You need to log in to use this feature.";
}
$page->append_content($string);
echo $page;
