<?php

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

function CDM_INGEST_query($query, $api_url, $api_url_supplemental = '', $api_key='', $limit=20, $start=0, $limit_by_asset_type=false) 
{
	$collection='all';
    //not used?
    //$url = CDM_INGEST_QUERY_make_search_url($collection, $query, "find!subjec", "pointer", $records);
    
    $items = CDM_INGEST_get_pointers($query, $collection, $api_url, $limit, $start);
    $Everything = Array();
    
    $count = 0;
    foreach($items as $point => $col){
        $Everything[$count] = CDM_INGEST_get_item($col, $point, $api_url, $api_url_supplemental);
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
function CDM_INGEST_QUERY_make_search_url($alias, $search_string, $fields, $sort, $max_recs, $start_num, $api_url, $suppress=0, $docptr=0, $suggest=0, $facets=0, $showunpub=0, $denormalizeFacets=0, $format='xml'){
    $url = $api_url . "/dmwebservices/index.php?q=dmQuery";
    
    $query = $search_string;
    $search_string = "CISOSEARCHALL^";
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
function CDM_INGEST_get_item($collection, $pointer, $api_url,  $api_url_supplemental){
    $format = "xml";
	$url = $api_url . "/dmwebservices/index.php?q=dmGetItemInfo" . 
		"/" . $collection . 
		"/" . $pointer . 
		"/" . $format;
   
    $title = Array();
    $type = Array();
    $size = Array();
    $description = Array();
    $subject = Array();
    
    $stream = file_get_contents($url);
    
    preg_match_all("|<title>(.*)</title>|", $stream, $title);
    preg_match_all("|<format>(.*)</format>|", $stream, $type);
    preg_match_all("|<cdmfilesize>(.*)</cdmfilesize>|", $stream, $size);
    preg_match_all("|<descri>(.*)</descri>|", $stream, $description);
    preg_match_all("|<subjec>(.*)</subjec>|", $stream, $subject);
    
    $children = array();
    $compound_obj_xml = simplexml_load_string(file_get_contents($api_url. "/dmwebservices/index.php?q=dmGetCompoundObjectInfo/$collection/$pointer/xml"));
    if(isset($compound_obj_xml->type)) {
	    foreach ($compound_obj_xml->page as $page) {
		    $child = CDM_INGEST_get_item($collection, $page->pageptr, $api_url, $api_url_supplemental);
		    $child['title'] = $page->pagetitle;
		    $children[] = $child;
	    }
    }
    
     $item_info = Array(
         'id' => $pointer,
         'collection' => $collection,
         'title' => $title[1][0],
         'thumbnail_url' => $api_url_supplemental ."/utils/getthumbnail/collection/$collection/id/$pointer",
         'repository' => "CDM", //overriding this with the source id once the array is passed back
         'type' => $type[1][0],
         'metadata' => $description[1][0] . " " . $subject[1][0],
         'children' => $children ,
     );
    
    return $item_info;
}

/*******************************************************************************
 * function get_collection_list
 * 
 * Returns a string array of all of the collections in CDM
 * 
 * @param type $collection
 * @return $collections - and array of the collections
 
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
*/


/*******************************************************************************
 * function get_all_items
 * 
 * Gets the pointers from as many objects it can
 * 
 * @param $collection
 * @return $pointers - array of pointers
 */

function CDM_INGEST_get_pointers($query, $alias='all', $api_url, $maxrecs=20, $start =0)
{
    $pointers = Array();
    $url = CDM_INGEST_QUERY_make_search_url($alias, $query, "find!subjec", "pointer", $maxrecs, $start, $api_url);
    $collection = Array();
    $results = Array();
    $stream = file_get_contents($url);
    $parents = Array();
    
    preg_match_all("|<pointer><!\[CDATA\[(.*)\]\]></pointer>|", $stream, $pointers);
    preg_match_all("|<collection><!\[CDATA\[\/(.*)\]\]></collection>|", $stream, $collection);
    preg_match_all("|<parentobject><!\[CDATA\[(.*)\]\]></parentobject>|", $stream, $parents);

    $pointers = $pointers[1];
    $collection = $collection[1];
    $parents = $parents[1];
    
    for($i = 0; $i < count($pointers); $i++)
    {
		if(trim($parents[$i]) == "-1") {
			$results[$pointers[$i]] = $collection[$i];
		}
    }
    
    return $results;
}

/*******************************************************************************
 * CDM_INGEST_ingest
 * 
 * @param type $pointer
 * @param type $alias
 * @return string
 */
function CDM_INGEST_ingest($pointer, $alias, $api_url, $api_url_supplemental, $api_key) {
        $url = $api_url . "/dmwebservices/index.php?q=dmGetItemInfo/".$alias . "/".$pointer."/xml";     
        $compound_object_info_url = $api_url . "/dmwebservices/index.php?q=dmGetCompoundObjectInfo/".$alias . "/".$pointer."/xml";
        
        $compound_object_xml = simplexml_load_string(file_get_contents($compound_object_info_url));
        $stream = file_get_contents($url);
        
        if(!isset($compound_object_xml->page)){ //single
            $image = $api_url_supplemental . "/utils/ajaxhelper/?CISOROOT=".$alias."&CISOPTR=".$pointer."&action=2&DMSCALE=100&DMWIDTH=99999&DMHEIGHT=99999&DMX=0&DMY=0&DMTEXT=&DMROTATE=0"; 
        }else{ //compound
		  $pointer = $compound_object_xml->page[0]->pageptr;
            $image = $api_url_supplemental . "/utils/ajaxhelper/?CISOROOT=". $alias."&CISOPTR=".$pointer."&action=2&DMSCALE=100&DMWIDTH=99999&DMHEIGHT=99999&DMX=0&DMY=0&DMTEXT=&DMROTATE=0"; 
        }
	   
        $xml = simplexml_load_string($stream);
        
	   
	   
	   $response = Array(
		'asset_title' => $xml->title,
		'asset_metadata' => $xml->subjec,  //not a type-o
		'asset_source' => $image,
		'asset_data' => file_get_contents($image),
		'asset_mimetype' => $xml->format,
		'asset_size' => $xml->size + 0,
		
		//optional, but nice
		'asset_date' => $xml->date,
	);
	return $response;
}
?>