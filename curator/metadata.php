<?php
  

require_once '../../config.php';
$page = new page("", "Metadata");

if(logged_in()) {
    
   
    $elements = array(
        new f_data_element('Metadata type','metadata_type','text'),
        new f_data_element('Metadata value','metadata_value','textarea'),
    );
    
    if(isset($_GET['row_id'])) {
        $string = f_data($elements, db_connect(), "metadata", "metadata_id", $_GET['row_id']);
    } else {
        $string = f_data_list(db_connect(), "metadata", "metadata_id", "metadata_value");
        $string .= f_data($elements, db_connect(), "metadata", "metadata_id", false);
    }
    
    
} else {
    $string = "You need to log in to use this feature.";
}
$page->append_content($string);
echo $page;


?>
