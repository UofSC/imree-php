<?php
require_once('../../config.php');

/*******************************************************************************
 * contentDM_ingest
 * Searches CONTENTdm... more to come
 * @Author: Cole Mendes
 * @Date: 02/17/2014
 */

/*******************************************************************************
 * get_items function
 * 
 * @param $records - Number of items the user wants 
 */
function CDM_INGEST_query($query='all', $collection='all', $records=200)
{
    $url = CDM_INGEST_QUERY_make_search_url($collection, $query, "pointer", "collection", $records);
    
    $items = CDM_INGEST_get_pointers($collection, $records);
    $Everything = Array();
    
    $count = 0;
    foreach($items as $point => $col){
        $Everything[$count] = CDM_INGEST_get_item($col, $point);
        $count++;
    }
    
    return $Everything; 
}

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
function CDM_INGEST_QUERY_make_search_url($alias, $search_string, $fields, $sort, $max_recs, $start_num=1, $suppress=0, $docptr=0, $suggest=0, $facets=0, $showunpub=0, $denormalizeFacets=0, $format='xml'){
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
function CDM_INGEST_get_item($collection, $pointer, $format = "xml"){
    
    $url = "http://digital.tcl.sc.edu:81/dmwebservices/index.php?q=dmGetItemInfo";
    $url .= "/" . $collection;
    $url .= "/" . $pointer;
    $url .= "/" . $format;
    $item_info = Array();
    $title = Array();
    $type = Array();
    $size = Array();
    
    $stream = file_get_contents($url);
    preg_match_all("|<title>(.*)</title>|", $stream, $title);
    preg_match_all("|<format>(.*)</format>|", $stream, $type);
    preg_match_all("|<cdmfilesize>(.*)</cdmfilesize>|", $stream, $size);
    
    $item_info['Title'] = $title[1][0];
    $item_info['Type'] = $type[1][0];
    $item_info['Size'] = $size[1][0];
    $item_info['Repository'] = "CDM";
    $item_info['URL'] = $url;
    $item_info['ID'] = $pointer;
    $item_info['Collection'] = $collection;

    return $item_info;
}

/*******************************************************************************
 * function get_collection_list
 * 
 * Returns a string array of all of the collections in CDM
 * 
 * @param type $collection
 * @return $collections - and array of the collections
 */
function CDM_INGEST_get_collection_list(){
    
    $collections = Array();
    $url = "http://digital.tcl.sc.edu:81/dmwebservices/index.php?q=dmGetCollectionList/xml";
    $accessURL = file_get_contents($url);
    
    
        $collection = fgets($accessURL, 9999);
          if(strpos($collection, 'alias'))
          { 
            $collection = strip_tags($collection);
            $collection = str_replace('/', "", $collection);
            $collection = trim($collection);
            $collections[$collection] = $collection;
            
          }
    
    return $collections;
}

/*******************************************************************************
 * function get_all_items
 * 
 * Gets the pointers from as many objects it can
 * 
 * @param $collection
 * @return $pointers - array of pointers
 */
function CDM_INGEST_get_pointers($alias='all', $maxrecs=200)
{
    $pointers = Array();
    $url = CDM_INGEST_QUERY_make_search_url($alias, "all", "find", "collection", $maxrecs);
    $collection = Array();
    $results = Array();

    $stream = file_get_contents($url);
    preg_match_all("|<pointer><!\[CDATA\[(.*)\]\]></pointer>|", $stream, $pointers);
    preg_match_all("|<collection><!\[CDATA\[\/(.*)\]\]></collection>|", $stream, $collection);

    $pointers = $pointers[1];
    $collection = $collection[1];
    
    for($i = 0; $i < $maxrecs; $i++)
    {
        $results[$pointers[$i]] = $collection[$i];
    }
    
    return $results;
}
?>