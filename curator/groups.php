<?php

require_once '../../config.php';
$page = new page("", "Group");

if(logged_in()) {
    
    $group_parent_options = new f_data_element_options();
		$group_parent_options->normalized_table = "groups";
		$group_parent_options->normalized_table_none_label = "none";
		$group_parent_options->normalized_table_none_value = "";
		$group_parent_options->normalized_table_primary_key_column = "group_id";
		$group_parent_options->normalized_table_primary_label_column = "group_name";
	
    $elements = array(
        new f_data_element('Group Name','group_name','text'),
        new f_data_element('Type','group_type','select',array('gallery'=>'gallery','grid'=>'grid','list'=>'list','narrative'=>'narrative','linear'=>'linear','timeline'=>'timeline','unset'=>'unset'),'unset'),
        new f_data_element('Parent','group_parent_id','select','','0','',$group_parent_options),
    );
    
    if(isset($_GET['row_id'])) {
        $string = f_data($elements, db_connect(), "groups", "group_id", $_GET['row_id']);
    } else {
        $string = f_data_list(db_connect(), "groups", "group_id", "group_name");
        $string .= f_data($elements, db_connect(), "groups", "group_id", false);
    }
    
    
} else {
    $string = "You need to log in to use this feature.";
}
$page->append_content($string);
echo $page;
