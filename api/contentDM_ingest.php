<?php
require_once('../../config.php');

/*******************************************************************************
 * contentDM_ingest
 * Searches CONTENTdm... more to come
 * @Author: Cole Mendes
 * @Date: 02/17/2014
 */

/*******************************************************************************
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

/*******************************************************************************
 * ITEM_make_search_url
 * 
 * @param type $collection
 * @param type $pointer
 * @param type $format
 * @return string
 */    
//doesnt work yet
function get_item($collection, $pointer, $format = "xml"){
    
    $url = "http://digital.tcl.sc.edu:81/dmwebservices/index.php?q=dmGetItemInfo";
    $url .= "/" . $collection;
    $url .= "/" . $pointer;
    $url .= "/" . $format;
    $alias = Array();
    
    $accessURL = fopen($url, "r");
    
    
    
}

/*******************************************************************************
 * function get_collection_list
 * 
 * Returns a string array of all of the collections in CDM
 * 
 * @param type $collection
 * @return $collections - and array of the collections
 */
function get_collection_list(){
    
    $collections = Array();
    $url = "http://digital.tcl.sc.edu:81/dmwebservices/index.php?q=dmGetCollectionList/xml";
    $accessURL = fopen($url, "r");
    while(!(feof($accessURL))) //until url is finished
    {
        $collection = fgets($accessURL, 9999);
          if(strpos($collection, 'alias'))
          { 
            $collection = strip_tags($collection);
            $collection = str_replace('/', "", $collection);
            $collection = trim($collection);
            $collections[$collection] = $collection;
            
          }
    }
    return $collections;
}

/*******************************************************************************
 * function get_all_items
 * 
 * @param $collection
 * @return
 */
function get_pointers($alias)
{
    $pointers = Array();
    $url = QUERY_make_search_url($alias, "all", "find", "collection", 200);
    echo $url;
    $accessURL = fopen($url, "r");
    while(!(feof($accessURL))) //until url is finished
    {
       $stream = fgets($accessURL, 9999);
      if(strpos($stream, 'collection'))
      {    
        
        $collection = str_replace('<collection><![CDATA[', "", $stream); 
        $collection = str_replace(']]></collection>', "", $collection);
        $collection = str_replace('/', "", $collection);
        $collection = trim($collection);
       // var_dump($collection);
        
      }  
      if(strpos($stream, 'pointer'))
      { 
        $pointer = str_replace('<pointer><![CDATA[', "", $stream); 
        $pointer = str_replace(']]></pointer>', "", $pointer);
        $pointer = str_replace('/', "", $pointer);
        $pointer = trim($pointer);
        $pointers[$pointer] = $collection;
      }  
    } //$pointers now hold all pointer data for a collection based on search params
    
    return $pointers;
}

//doesnt work yet
function get_items($records=50)
{
    $url = QUERY_make_search_url("all", "all", "find", "collection", $records);
    $items = get_pointers('all');
    $Everything = Array();
    
    $count = 0;
    foreach($items as $point => $col){
        $Everything[$count] = get_item($col, $point);
        $count++;
    }
   
    return $Everything; 
}
echo var_dump(get_items($records=50));


//$temp = get_collection_list();

   /* 
    $conn = db_connect();
    $errors = Array();
    $results = Array();
    $search_limit = Array("img" => "image only", "vid" => "video only","doc" => "document only","aud" => "audio only"); */
    
  
 /*   
    
    $url = QUERY_make_search_url("all", "all", "find", "collection", 50);
    $accessURL = fopen($url, "r");
    while(!(feof($accessURL))) //until url is finished
    {
      $pointer = fgets($accessURL, 9999);
      if(strpos($pointer, 'find'))
      {  
        $pointer = str_replace('<find><![CDATA[', "", $pointer);
        $pointer = str_replace(']]></find>', "", $pointer);
        $pointer = substr("$pointer", 0, -5);   
        $results .= $pointer; 
        $pointer = trim($pointer);  
      }  
    }*/


