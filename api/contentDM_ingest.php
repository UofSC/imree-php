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
    global $content_dm_address, $content_dm_utils_address;
    $url = "http://digital.tcl.sc.edu:81/dmwebservices/index.php?q=dmGetItemInfo";
    $url .= "/" . $collection;
    $url .= "/" . $pointer;
    $url .= "/" . $format;
   
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
    
    
     $item_info = Array(
         'id' => $pointer,
         'collection' => $collection,
         'title' => $title[1][0],
         'thumbnail_url' => "http://".$content_dm_utils_address."/utils/getthumbnail/collection/$collection/id/$pointer",
         'repository' => "CDM",
         'type' => $type[1][0],
         'metadata' => $description[1][0] . " " . $subject[1][0],
         'children' => array() ,
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
        
        $cpd_url = "http://" . $content_dm_address . "/dmwebservices/index.php?q=dmGetCompoundObjectInfo";
        $cpd_url .= "/" . $alias;
        $cpd_url .= "/" . $pointer;
        $cpd_url .= "/xml";
        
        $cpd_stream = file_get_contents($cpd_url);
        $stream = file_get_contents($url);
        
        if(preg_match_all("|<code>(.*)</code>|", $cpd_stream, $check)){ //single
            $image = "http://digital.tcl.sc.edu/utils/ajaxhelper/?CISOROOT="
                    . $alias
                    . "&CISOPTR="
                    . $pointer
                    . "&action=2&DMSCALE=20&DMWIDTH=512&DMHEIGHT=512&DMX=0&DMY=0&DMTEXT=&DMROTATE=0"; 
        }else{ //compound
            preg_match_all("|<find>(.*).cpd<\/find>|", $stream, $image);
            $pointer = $image[1][0];
            $image = "http://digital.tcl.sc.edu/utils/ajaxhelper/?CISOROOT="
                    . $alias
                    . "&CISOPTR="
                    . $pointer
                    . "&action=2&DMSCALE=20&DMWIDTH=512&DMHEIGHT=512&DMX=0&DMY=0&DMTEXT=&DMROTATE=0"; 
        }
        
        preg_match_all("|<title>(.*)</title>|", $stream, $title);
        preg_match_all("|<type>(.*)</type>|", $stream, $type);
        preg_match_all("|<cdmfilesize>(.*)</cdmfilesize>|", $stream, $size);
        preg_match_all("|<web>(.*)</web>|", $stream, $web);
        preg_match_all("|<subjec>(.*)</subjec>|", $stream, $subject);
        
        //change size to integer
        $size = $size[1][0] + 0;
        
        $image_data = file_get_contents($image);
	$response = Array(
            'asset_title' => $title[1][0],
	    'asset_data' => $subject[1][0],
            'asset_url' => $stream,
            'asset_source_url' => $image_data,
	    'asset_mimetype' => $type[1][0],
	    'asset_size' => $size,
	);
        
	return $response;
}
?>