<?php
require_once('../../config.php');

/*******************************************************************************
 * contentDM_ingest
 * Searches ContentDM
 * @Author: Cole Mendes
 * @Date: 02/17/2014
 */

/*******************************************************************************
 * CDM_INGEST_query function
 * 
 * @param type $query - actual search query
 * @param type $collection
 * @param type $records
 * @return type
 */
function CDM_INGEST_query($query='0', $collection='all', $records=20)
{
    $url = CDM_INGEST_QUERY_make_search_url($collection, $query, "find!subjec", "pointer", $records);
    
    $items = CDM_INGEST_get_pointers($query, $collection, $records);
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
function CDM_INGEST_QUERY_make_search_url($alias, $search_string, $fields, $sort, $max_recs=20, $start_num=1, $suppress=0, $docptr=0, $suggest=0, $facets=0, $showunpub=0, $denormalizeFacets=0, $format='xml'){
    global $content_dm_address;
    $url = "http://" . $content_dm_address . "/dmwebservices/index.php?q=dmQuery";
    
    $query = $search_string;
    $search_string = "title!subjec^";
    $query = str_replace(" ", "+", $query);
    $search_string .= $query . "^all^and";
    $new_fields = str_replace(" ", "!", $fields);
    $url .= "/" . $alias;
    $url .= "/" . $search_string;
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
    $item_info['Size'] = $size[1][0] . " Bytes";
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
function CDM_INGEST_get_pointers($query, $alias='all', $maxrecs=20)
{
    $pointers = Array();
    $url = CDM_INGEST_QUERY_make_search_url($alias, $query, "find!subjec", "pointer", $maxrecs);
    $collection = Array();
    $results = Array();
    
    $stream = file_get_contents($url);
    preg_match_all("|<pointer><!\[CDATA\[(.*)\]\]></pointer>|", $stream, $pointers);
    preg_match_all("|<collection><!\[CDATA\[\/(.*)\]\]></collection>|", $stream, $collection);

    $pointers = $pointers[1];
    $collection = $collection[1];
    
    for($i = 0; $i < count($pointers); $i++)
    {
        $results[$pointers[$i]] = $collection[$i];
    }
    
    return $results;
}

/*******************************************************************************
 * CDM_INGEST_ingest
 * 
 * @param type $alias
 * @param type $pointer
 * @return string
 */
function CDM_INGEST_ingest($alias, $pointer) {
        global $content_dm_address;
        $url = "http://" . $content_dm_address . "/dmwebservices/index.php?q=dmGetItemInfo";
        $url .= "/" . $alias;
        $url .= "/" . $pointer;
        $url .= "/xml";
        
        $stream = file_get_contents($url);
        preg_match_all("|<title>(.*)</title>|", $stream, $title);
        preg_match_all("|<format>(.*)</format>|", $stream, $format);
        preg_match_all("|<type>(.*)</type>|", $stream, $type);
        preg_match_all("|<cdmfilesize>(.*)</cdmfilesize>|", $stream, $size);
        preg_match_all("|<web>(.*)</web>|", $stream, $web);
        preg_match_all("|<subjec>(.*)</subjec>|", $stream, $subject);
        
	$response = Array(
            'asset_title' => $title[1][0],
	    'asset_data' => $subject[1][0],
            'asset_url' => $web[1][0],
            'asset_format' => $format[1][0],
	    'asset_mimetype' => $type[1][0],
	    'asset_size' => $size[1][0] . ' Bytes'
	);
        
	return $response;
}
?>