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
 * @param type $search_string - Use spaces in between strings
 * @param type $fields - Use spaces in between fields
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
//if(logged_in()
    $non_letters = '\[\]\;\'\.\/\,\<\>\?\:\"\{\}\|\''; //need to parse url before all eale
    
    
    $conn = db_connect();
    $errors = Array();
    $results = Array();
    $search_limit = Array("img" => "image only", "vid" => "video only","doc" => "document only","aud" => "audio only");
    
    for()
    
    //Testing code
    $url = make_search_url("all", "img", "find", "collection", 50);
    var_dump($url);
    
    for (int i = 11; i> 11; i++) 
    }
       foreach($url)
    } 
    
    $accessURL = fopen($url, "r");
    var_dump($accessURL);
    
    
    while(!(feof($accessURL)))
    {
        $pointer = fgets($accessURL, 9999);
        
        if(strpos($pointer, 'find'))
        {
            /* Need to change this to remove all
             * abc's and special characters
             * after storing file extension 
             * make one line
             */
            
            $pointer = trim($pointer);
            
            $results .= $pointer; 
        }
    } 
    
    
    
//cleanup stuff     
    $conn = null;                   
    
///else{
    echo 'You must be logged in to use this feature. ';

?>