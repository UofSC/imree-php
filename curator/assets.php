<?php

require_once '../../config.php';
$page = new page("", "Assets");
$string = "";

if(logged_in()) {
    
    $asset_parent_options = new f_data_element_options();
		$asset_parent_options->normalized_table = 'assets';
		$asset_parent_options->normalized_table_none_label = 'no parent item';
		$asset_parent_options->normalized_table_none_value = '';
		$asset_parent_options->normalized_table_primary_key_column = 'asset_id';
		$asset_parent_options->normalized_table_primary_label_column = 'asset_name';
	
    $asset_file_options = new f_data_element_options();
		$asset_file_options->data_table = "asset_data";
		$asset_file_options->data_table_contents = "asset_data_contents";
		$asset_file_options->data_table_key_column = "asset_data_id";
		$asset_file_options->data_table_name = "asset_data_name";
		$asset_file_options->data_table_restricted = "asset_data_access_restricted";
		$asset_file_options->data_table_size = "asset_data_size";
		$asset_file_options->data_table_title = "asset_data_title";
		$asset_file_options->data_table_type = "asset_data_type";
		$asset_file_options->data_table_updated = "asset_data_contents_date";
		$asset_file_options->data_table_user = "asset_data_user";
		
    
    $elements = array(
        new f_data_element('Asset Name','asset_name','text'),
	   new f_data_element('Asset Type','asset_type','select',array('image'=>'image','video'=>'video','audio'=>'audio','text'=>'text')),
	   new f_data_element('Asset URL','asset_media_url','file','','0','',$asset_file_options),
	   new f_data_element('Asset Thumb URL','asset_thumb_url','text'),
	   new f_data_element('Asset Parent','asset_parent_id','select','','0','',$asset_parent_options),
	   new f_data_element('Date Added','asset_date_added','hidden',now()),
	   new f_data_element('Date Created','asset_date_created','date',now()),
    );
    
    if(isset($_GET['row_id'])) {
        $conn = db_connect();
        $string .= f_data($elements, $conn, "assets", "asset_id", $_GET['row_id']);
        $string .= f_data_assignments_one2many($conn, "asset_event_assignments", "asset_id", "event_id", $_GET['row_id'], "events", "event_id", "event_name", "event_assignments");
        $string .= f_data_assignments_one2many($conn, "asset_group_assignments", "asset_id", "group_id", $_GET['row_id'], "groups", "group_id", "group_name", "group_assignments");
        $string .= f_data_assignments_one2many($conn, "asset_subject_assignments", "asset_id", "subject_id", $_GET['row_id'], "subjects", "subject_id", "subject_title", "subject_assignments");
    } else {
        $string .= f_data_list(db_connect(), "assets", "asset_id", "asset_name");
        $string .= f_data($elements, db_connect(), "assets", "asset_id", false);
    }
    
    
} else {
    $string = "You need to log in to use this feature.";
}
$page->append_content($string);
echo $page;
