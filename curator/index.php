<?php


require_once('../../config.php'); //assumes the config file is placed immediately outside the imree-php folder



$elements = array(
    new f_data_element('Exhibit Name/Title','exhibit_name','text'),
    new f_data_element('Date Start','exhibit_date_start','date'),
    new f_data_element('Date End','exhibit_date_end','date'),
);

$string = f_data($elements, db_connect(), "exhibits", "exhibit_id", false);

$page = new page($string, "Some kind of test");
echo $page;
