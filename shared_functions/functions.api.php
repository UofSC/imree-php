<?php
/**
 * This file contains functions for preparing data for export
 * @author Craig Spurrier <spurriec@mailbox.sc.edu>
 * @package shared_functions
 * @copyright University of South Carolina/Craig Spurrier 2010
 * @license The MIT License http://opensource.org/comment/935
 */
 
/** 
* Converts an array to a table
* @author Craig Spurrier 
* @param array $array array of data to process 
* @return string  
*/
function array_to_table($array){
        echo "<table style='border: #808080 outset 1px; border-collapse: collapse; border-spacing: 0px;'>\n";
    foreach($array AS $key=>$value){
        echo "<tr style='vertical-align:text-top;'>\n
            <td style='padding: 2px; border: #808080 inset 1px;'>",htmlspecialchars($key),"</td>
            <td style='padding: 2px; border: #808080 inset 1px;'>";
            if(is_array($value)){
                array_to_table($value);
            }else{
                echo htmlspecialchars($value);
            }
            
        echo "</td></tr>\n";
    }
        echo "</table>";

}

/** 
* Converts an array to xml
* @author Craig Spurrier 
* @param array $array array of data to process 
* @return string  
*/
function array_to_xml($array){
    foreach($array AS $key=>$value){
        if (is_numeric($key)){ //$keys can not be numeric
            $key = "item_$key";
        }
        $key = htmlentities(str_replace(' ', '_', $key));
        echo "<",htmlspecialchars($key),">\n";
            if(is_array($value)){
                array_to_xml($value);
            }else{
                echo htmlspecialchars($value)."\n";
            }
            
        echo "</",htmlspecialchars($key),">\n";
    }

}

/** 
* Converts an array to json
* @author Craig Spurrier 
* @param array $array array of data to process 
* @return string  
*/
function array_to_json($array){
    $new_array = Array();
    foreach($array AS $key=>$value){
        if (is_numeric($key)){ //fix for chrome bug 883
            $key = "item_$key";
        }
        $new_array[$key] = $value;
    }
    return json_encode($new_array);
}

/** 
* Converts an array to csv
* @author Craig Spurrier 
* @param array $array array of data to process 
* @return string  
*/
function array_to_csv($array){
    $new_array = array();
    foreach($array['results'] AS $row){
        $values = Array();
        foreach($row AS $value){
            $value = str_replace('"','""',$value);
            $values[] = "\"$value\"";
        }
        $new_array[] = implode(',',$values);
    }
    echo implode("\n",$new_array);
}

/** 
* Converts an array to tab delmited data
* @author Craig Spurrier 
* @param array $array array of data to process 
* @return string  
*/
function array_to_tab($array){
    $new_array = array();
    foreach($array['results'] AS $row){
        $values = Array();
        foreach($row AS $value){
            $values[] = str_replace(Array("\n","\r","\t"),Array('','',''),$value);
        }
        $new_array[] = implode("\t",$values);
    }
    echo implode("\n",$new_array);
}