<?php
require_once('../../config.php');

/**
 * contentDM_ingest
 * Searches CONTENTdm... more to come
 * @Author: Cole Mendes
 * @Date: 02/17/2014
 */

/**
 * make search url
 *      creates a url for traversing contentDM
 * @param type $alias
 * @param type $search_string - /word^word^word/
 * @param type $fields - field!otherField
 * @param type $sort - sort field
 * @param type $max_recs - number of records returned
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
//function works
function make_search_url($alias, $search_string, $fields, $sort, $max_recs, $start_num=1, $suppress=0, $docptr=0, $suggest=0, $facets=0, $showunpub=0, $denormalizeFacets=0, $format='xml'){
    global $content_dm_address;
    $url = $content_dm_address;
    $strings = str_replace(" ", "^", $search_string);
    $new_fields = str_replace(" ", "!", $fields);
    
    $url .= "/" . $alias;
    $url .= "/" . $strings;
    $url .= "/" . $new_fields;
    $url .= "/" . $sort;
    $url .= "/" . $max_recs;
    $url .= "/" . $start_num;
    $url .= "/" . $suppress;
    $url .= "/" . $docptr;
    $url .= "/" . $suggest;
    $url .= "/" . $facets;
    $url .= "/" . $showunpub;
    $url .= "/" . $denormalizeFacets;
    $url .= "/" . $format;
       
    return $url;
}

if(logged_in()) {
    $conn = db_connect();
    $errors = Array();
    $results = Array();
    $search_limit = Array("img" => "image only", "vid" => "video only","doc" => "document only","aud" => "audio only");
    
    //Testing code
    $url = make_search_url("seeger", "all", "pointer", "title", 5);
    var_dump($url);
    
    $accessURL = fopen($url, "r");
    var_dump($accessURL);
    
    while(!(feof($accessURL)))
    {
        $results .= fgets($accessURL, 9999);
        
    }
    var_dump($results);
    
//cleanup stuff     
    $conn = null;                   
    
}else{
    echo 'You must be logged in to use this feature. ';
}
?>