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
function QUERY_make_search_url($alias, $search_string, $fields, $sort, $max_recs, $start_num=1, $suppress=0, $docptr=0, $suggest=0, $facets=0, $showunpub=0, $denormalizeFacets=0, $format='xml'){
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

/**
 * ITEM_make_search_url
 * 
 * @param type $collection
 * @param type $pointer
 * @param type $format
 * @return string
 */    
function ITEM_make_search_url($collection = "all", $pointer, $format = "xml"){
    
    $url = "http://digital.tcl.sc.edu:81/dmwebservices/index.php?q=dmGetItemInfo";
    $url .= "/" . $collection;
    $url .= "/" . $pointer;
    $url .= "/" . $format;
    $alias = Array();
    
    return $url;
    
}
    
    $conn = db_connect();
    $errors = Array();
    $results = Array();
    $search_limit = Array("img" => "image only", "vid" => "video only","doc" => "document only","aud" => "audio only");
    
  
    
    
    $url = QUERY_make_search_url("all", "all", "find", "collection", 50);
  //  var_dump($url);
    
    
    $accessURL = fopen($url, "r");
  //  var_dump($accessURL);
    
    $pointer = fgets($accessURL, 9999);
    while(!(feof($accessURL))) //until url is finished
    {
      if(strpos($pointer, 'find'))
      {  
        $pointer = str_replace('<find><![CDATA[', "", $pointer);
        $pointer = str_replace(']]></find>', "", $pointer);
        $pointer = substr("$pointer", 0, -5);   
        $results .= $pointer; 
        $pointer = trim($pointer);  
      }  
    }


echo      "<html>
            <head>
                <title>PHP Test</title>
            </head>
            <body>";
                echo var_dump($results),
                "\n",
                "http://digital.tcl.sc.edu/81/dmwebservices/index.php?q=dmGetCollectionList/xml";
                echo "
            </body>
           </html>";



?>