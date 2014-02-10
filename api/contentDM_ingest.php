<?php
require_once('../../config.php');

/**
 * contentDM_ingest
 * Searches CONTENTdm... more to come
 * @Author: Cole Mendes
 * @Date: 02/10/2014
 */

/**
 * make search url
 *      creates a url for traversing contentDM
 * @param type $search_string
 * @param type $fields 
 * @param type $sort
 * @param type $max_recs
 * @param type $start_num
 * @param type $suppress
 * @param type $docptr
 * @param type $suggest
 * @param type $facets
 * @param type $showunpub
 * @param type $denormalizeFacets
 * @param type $format
 * @return type $url
 */
function make_search_url($search_string, $fields, $sort, $max_recs, $start_num, $suppress=0, $docptr=0, $suggest=0, $facets=0, $showunpub=0, $denormalizeFacets=0, $format='xml'){
   //$content_dm_address
    $url = $content_dm_address;
    
    return $url;
}

if(logged_in()) {
    $conn = db_connect();
    $errors = Array();
    $results = Array();
    $target = htmlspecialchars($_SERVER["PHP_SELF"]);
    $search_limit = Array("img" => "image only", "vid" => "video only","doc" => "document only","aud" => "audio only");
    $search_string;
    $fields;
    $sort; 
    $max_recs;
    $start_num;

//cleanup stuff  
}else{
    echo 'You must be logged in to use this feature. ';
}
?>