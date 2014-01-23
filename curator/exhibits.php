<?php

require_once '../../config.php';
$page = new page("", "Exhibits");

if(logged_in()) {
    $elements = array(
        new f_data_element('Exhibit Name/Title','exhibit_name','text'),
        new f_data_element('Date Start','exhibit_date_start','date'),
        new f_data_element('Date End','exhibit_date_end','date'),
    );
    $string = f_data($elements, db_connect(), "exhibits", "exhibit_id", false);
} else {
    $string = "You need to log in to use this feature.";
}
$page->append_content($string);
echo $page;
