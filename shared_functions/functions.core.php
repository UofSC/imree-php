<?php 
/**
 * This file contains functions that are considered core functions
 * @author Craig Spurrier <spurriec@mailbox.sc.edu>
 * @package shared_functions
 * @copyright University of South Carolina/Craig Spurrier 2010 (unless otherwise marked)
 * @license The MIT License http://opensource.org/comment/935
 */ 

/** 
* This function does the basic start-up things that everything else requires 
* @author Craig Spurrier 
* @version 0.3 Jan 2 2011 12:21EDT 
*/ 
function init(){ 
    global $session_name; 
    
    if(session_id() === "") {
        session_start();  
    }
    $GLOBALS['errors'] = Array(); //Create a global array in which we can store error messages 
    $GLOBALS['login_errors'] = Array(); //Create a global array in which we can store login related error messages 

    if(!isset($_SESSION[$session_name])){  
        $_SESSION[$session_name] = Array(); //Create an array within the session array to store seesion data. Using the session name (must be defined before this is run such as in config.php) to force a unqiue sesssion
    } 
} 

/** 
* This function returns the requested superglobal variable. If it does not exist, it returns NULL. Can also ensure that the result is always an array This helps ensure that we always get a predictable response
* @author Craig Spurrier 
* @version 0.8 Jan 2 2011 12:12EDT 
* @param string $var The name of the variable that we are attempting to fetch 
* @param string $method The superglobal the variable is stored in. 
* @param bool $should_be_an_array (FALSE|TRUE) Should we force the result to be an array 
* @return mixed  
*/ 
function get_var($var, $method = 'POST',$should_be_an_array=0){ 
    switch(strtoupper($method)){ // Set the superglobal array that should be used  
        case 'POST': 
            $array=$_POST; 
        break; 
        case 'REQUEST': 
            $array=$_REQUEST; 
        break; 
        case 'GET': 
            $array=$_GET; 
            break; 
        case 'COOKIE': 
            $array=$_COOKIE; 
        break; 
        case 'SESSION': 
            if(isset($_SESSION)){ 
                $array=$_SESSION[$GLOBALS['session_name']]; 
            } 
        break; 
        case 'SERVER': 
            $array=$_SERVER; 
        break; 
        default: 
            $array=$GLOBALS[$method]; 
    } 
    if(isset($array[$var])){ // If that variable exists in that superglobal return it, else return the not found response 
        $return = $array[$var]; 
    }else{ 
        $return = NULL; 
    } 
    if($should_be_an_array){ 
        if(is_array($return)){ 
            return $return; 
        }else{ 
            return Array($return); 
        } 
    }else{ 
        return $return; 
    } 

 } 

/** 
* This function checks if a variable is empty without triggering "Can't use function return value in write context" when used with get_var 
* @author Craig Spurrier 
* @version 0.4 Nov 3 2010 16:06EDT 
* @param string $var The name of the variable that we are to check 
* @return bool (TRUE|FALSE) Returns TRUE if $var is empty 
*/ 
function is_empty($var) { 
    return empty($var); 
} 

/** 
* This function checks to see if there are any errors (except login errors), by checking to see if the error array contains anything  
* @author Craig Spurrier 
* @version 0.1 Nov 5 2010 10:47EDT 
* @return bool (TRUE|FALSE) Returns TRUE if errors exist 
*/ 
function errors() { 
    if(count($GLOBALS['errors']) == 0){ 
        return 0; 
    }else{ 
        return 1; 
    } 
} 

/** 
* This function sets the array key to be the same as the value 
* @author Craig Spurrier 
* @version 0.1 Nov 11 2010 15:58EDT  
* @param array $array The array to start with 
* @return array Returns an array with keys and values the same 
**/ 
function key_equals_value($array) { 
    return array_combine($array,$array); 
} 

/** 
* This function returns the url of the current page 
* @author Craig Spurrier 
* @version 0.1 Nov 11 2010 15:54EDT  
* @return string Returns the url of the current page 
**/ 
function self_url() { 
    if(isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on'){  
        $url = "https://"; 
    }else{ 
        $url = "http://"; 
    } 
    $url .= $_SERVER['HTTP_HOST']; 
    $url .= $_SERVER['SCRIPT_NAME']; 
    return $url; 
} 

/** 
* This function returns a MySQL style NOW() string 
* @author Craig Spurrier 
* @version 1.1 Oct 22 2010 17:03EDT  
* @return string Returns a MySQL style NOW() string 
**/ 
function now(){ 
    return date("Y-m-d H:i:s"); 
} 

/** 
* This function takes a US format date and returns a MySQL style date replacing missing data with 00 
* @author Craig Spurrier 
* @version 0.5 Oct 18 2011 17:03EDT  
* @param string $input a US format date 
* @return string Returns a MySQL style date replacing missing data with 00 
**/ 
function mysql_date($input){ 
    $input = str_replace('-','/',$input); 
    $input = explode('/',$input); 
    if(count($input) == 1){ 
        return $input[0]."-00-00"; 
    }elseif(count($input) == 2){ 
        return $input[1]."-".$input[0]."-00"; 

    }elseif(count($input) == 3){ 
        return $input[2]."-".$input[0]."-".$input[1]; 

    } 
} 

/** 
* This function returns the appropriate inflection for grammatical number, so as to avoid ugly things like (s) 
* @author Craig Spurrier 
* @version 0.4 Jan 2 2011 16:03EDT  
* @param int $count the count to base the decision on 
* @param string $singular the singular form of the word 
* @param string $plural the plural form of the word 
* @return string Returns the appropriate inflection for grammatical number 
**/ 
function plural_format($count,$singular,$plural){ 
    if($count == 1){ 
        return $singular; 
    }else{ 
        return $plural; 
    } 
} 

/** 
* This function takes a number and if the number is below 20 returns words 
* @author Craig Spurrier 
* @version 0.4 Jan 2 2011 16:03EDT  
* @param int $number the number to format 
* @return string Returns the number word or if over 19 a formatted number  
**/ 
function numbers_to_words($number){ 
$numbers = array("Zero", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen", "Nineteen");
    if($number < 20){ 
        return $numbers[$number]; 
    }else{ 
        return number_format($number); 
    } 

} 

/** 
* This function takes a multi line string and condenses it to one line to prepare it for Javascript 
* @author Craig Spurrier 
* @version 0.5 Nov 8 2011 16:16EDT  
* @param string $input the string to format 
* @return string Returns a single line 
**/ 
function javascript_multi_line($input){ 
    return str_replace(Array("\n",'&quot;',"\r"),Array(" ",'"'," "),$input); 
} 

/** 
* This function takes a file name and returns the extension 
* @author Craig Spurrier 
* @version 0.4 Sep 24 2011 16:16EDT  
* @param string $file_name the file name 
* @return string Returns the file extension 
**/ 
function file_extension($file_name){ 
    $path_info = pathinfo($file_name); 
    if(isset($path_info['extension'])){ 
        return strtolower($path_info['extension']); 
    }else{ 
        return ''; 
    } 
} 


/** 
* This function takes a file extension and provides an image  
* @author Craig Spurrier 
* @param string $ext the file extension 
* @return string An HTML img tag 
**/ 
function file_icon($ext){ 
            if($ext == 'pdf'){ 
                $icon = 'pdf.png'; 
            }elseif($ext == 'txt'){ 
                $icon = 'txt.png'; 
            }elseif($ext == 'doc' OR $ext == 'docx' OR $ext == 'rtf'){ 
                $icon = 'doc.png'; 
            }elseif($ext == 'html' OR $ext == 'htm'){ 
                $icon = 'html.png'; 
            } 
            echo "<img src='icons/mime_types/$icon' alt='$ext' width='48' height='48' />"; 
} 

/** 
* This function sends an e-mail with swift_mailer 
* @author Craig Spurrier 
**/ 
function send_email($to,$from,$subject='',$text='',$html=''){ 
    global $swift_mailer_path;
    require_once($swift_mailer_path); // Mailer library 
    if(is_array($to)){ 
        foreach($to AS $key=>$value){ 
            if(is_empty($value)){ 
                unset($to[$key]); 
            } 
        }     
    }else{ 
        if(!is_empty($to)){ 
            $to = Array($to); 
        } 
    } 
     
    if(count($to) > 0){ 
        $transport = Swift_SendmailTransport::newInstance(); 
                     
        $mailer = Swift_Mailer::newInstance($transport); 
        $message = Swift_Message::newInstance(); 
        $message->setSubject($subject); 
        $message->setFrom($from); 
        $message->setTo($to); 
        $message->setBody($text); 
        if(is_empty($html)){ 
            $html = $text; 
        } 
        $message->addPart( 
            "<?xml version='1.0' encoding='utf-8'?> 
            <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'> 
            <html xmlns='http://www.w3.org/1999/xhtml'> 
            <head> 
            <title>$subject</title> 
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' /> 
            </head> 
            <body>".$html."</body> 
            </html>", 
            'text/html'); 
         
        $result = $mailer->send($message); 
    } 
}

/**
 * Sends a message with swiftmailer through google mail
 * @param type $to
 * @param type $subject
 * @param type $message
 */
function send_gmail($to, $subject, $message) {
    global $google_username, $google_password, $swift_mailer_path, $email_signature;
    require_once($swift_mailer_path); // Mailer library 
    
    $transporter = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
        ->setUsername($google_username)
        ->setPassword($google_password);

    $mailer = Swift_Mailer::newInstance($transporter);
        $msg = Swift_Message::newInstance(); 
        $msg->setSubject($subject); 
        $msg->setFrom($google_username . "@gmail.com"); 
        $msg->setTo($to); 
        $msg->setBody($message . "<p>$email_signature</p>", 'text/html'); 
         
     $mailer->send($msg);
}

/** 
* This function fetches a URL using CURL 
* @author Craig Spurrier 
**/
function fetch_url($url,$cookies=0) { 
        $ch = curl_init ($url) ; 
        if($cookies){ 
                curl_setopt ($ch, CURLOPT_COOKIEFILE, cookie_path());  
              curl_setopt ($ch, CURLOPT_COOKIEJAR, cookie_path());  

        } 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ; 
        $res = curl_exec ($ch) ; 
        curl_close ($ch) ; 
        return ($res) ; 
}
/** 
* This function builds a sensible cookiepath and keeps track of it 
* @author Craig Spurrier 
**/ 
function cookie_path(){ 
    if(!is_empty(get_var('cookie_path','session'))){ 
        return get_var('cookie_path','session'); 
    }else{ 
        $ckfile = tempnam ("/tmp", "CURLCOOKIE"); 
        $_SESSION[$GLOBALS['session_name']]['cookie_path'] = $ckfile; 
        return $ckfile; 
    } 
}
/** 
* This function posts to a URL using CURL 
* @author Craig Spurrier 
**/ 
function post_to_url($url,$fields,$cookies=0) { 
    $fields_string = ''; 
    foreach($fields as $key=>$value) { 
        $fields_string .= $key.'='.$value.'&';  
    } 
    rtrim($fields_string,'&'); 
        $ch = curl_init ($url) ; 
        if($cookies){ 
                  curl_setopt ($ch, CURLOPT_COOKIEFILE, cookie_path());  
              curl_setopt ($ch, CURLOPT_COOKIEJAR, cookie_path());  
        } 
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ; 
        curl_setopt($ch,CURLOPT_POST,count($fields)); 
    curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  
        $res = curl_exec ($ch) ; 
        curl_close ($ch) ; 
        return ($res) ; 
} 

/**  
 * xml_to_array() will convert the given XML text to an array in the XML structure.  
 * Link: http://www.bin-co.com/php/scripts/xml2array/  
 * Arguments : $contents - The XML text  
 *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value. 
 *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance. 
 * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.  
 * Examples: $array =  xml2array(file_get_contents('feed.xml'));  
 *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));  
 */  
function xml_to_array($contents, $get_attributes=1, $priority = 'tag') {  
    if(!$contents) return array();  

    if(!function_exists('xml_parser_create')) {  
        //print "'xml_parser_create()' function not found!";  
        return array();  
    }  

    //Get the XML parser of PHP - PHP must have this module for the parser to work  
    $parser = xml_parser_create('');  
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss  
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);  
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);  
    xml_parse_into_struct($parser, trim($contents), $xml_values);  
    xml_parser_free($parser);  

    if(!$xml_values) return;//Hmm...  

    //Initializations  
    $xml_array = array();  
    $parents = array();  
    $opened_tags = array();  
    $arr = array();  

    $current = &$xml_array; //Refference  

    //Go through the tags.  
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array  
    foreach($xml_values as $data) {  
        unset($attributes,$value);//Remove existing values, or there will be trouble  

        //This command will extract these variables into the foreach scope  
        // tag(string), type(string), level(int), attributes(array).  
        extract($data);//We could use the array by itself, but this cooler.  

        $result = array();  
        $attributes_data = array();  
          
        if(isset($value)) {  
            if($priority == 'tag') $result = $value;  
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode  
        }  

        //Set the attributes too.  
        if(isset($attributes) and $get_attributes) {  
            foreach($attributes as $attr => $val) {  
                if($priority == 'tag') $attributes_data[$attr] = $val;  
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'  
            }  
        }  

        //See tag status and do the needed.  
        if($type == "open") {//The starting of the tag '<tag>'  
            $parent[$level-1] = &$current;  
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag  
                $current[$tag] = $result;  
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;  
                $repeated_tag_index[$tag.'_'.$level] = 1;  

                $current = &$current[$tag];  

            } else { //There was another element with the same tag name  

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array  
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;  
                    $repeated_tag_index[$tag.'_'.$level]++;  
                } else {//This section will make the value an array if multiple tags with the same name appear together  
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array  
                    $repeated_tag_index[$tag.'_'.$level] = 2;  
                      
                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well  
                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];  
                        unset($current[$tag.'_attr']);  
                    }  

                }  
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;  
                $current = &$current[$tag][$last_item_index];  
            }  

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'  
            //See if the key is already taken.  
            if(!isset($current[$tag])) { //New Key  
                $current[$tag] = $result;  
                $repeated_tag_index[$tag.'_'.$level] = 1;  
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;  

            } else { //If taken, put all things inside a list(array)  
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...  

                    // ...push the new element into that array.  
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;  
                      
                    if($priority == 'tag' and $get_attributes and $attributes_data) {  
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;  
                    }  
                    $repeated_tag_index[$tag.'_'.$level]++;  

                } else { //If it is not an array...  
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value  
                    $repeated_tag_index[$tag.'_'.$level] = 1;  
                    if($priority == 'tag' and $get_attributes) {  
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well  
                              
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];  
                            unset($current[$tag.'_attr']);  
                        }  
                          
                        if($attributes_data) {  
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;  
                        }  
                    }  
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken  
                }  
            }  

        } elseif($type == 'close') { //End of tag '</tag>'  
            $current = &$parent[$level-1];  
        }  
    }  
      
    return($xml_array);  
} 

/** 
* This function will provide a sub-string up to a desired length without breaking up words
* "..." is added if result do not reach original string length
* From http://www.php.net/manual/en/function.substr.php#93963
**/

function substr_words($str, $length, $minword = 3){ 
    $sub = ''; 
    $len = 0; 
     
    foreach (explode(' ', $str) as $word){ 
        $part = (($sub != '') ? ' ' : '') . $word; 
        $sub .= $part; 
        $len += strlen($part); 
         
        if (strlen($word) > $minword && strlen($sub) >= $length){ 
            break; 
        } 
    } 
     
    return $sub . (($len < strlen($str)) ? ' ...' : ''); 
} 

/** 
* This function does a recursive in_array search
**/ 
function in_array_recursive($needle, $haystack) {  
         foreach ($haystack as $v=>$e) {  
                 if ($needle == $v){ 
                     return true;  
                 }elseif (is_array($e)){ 
                     return in_array_recursive($needle, $e); 
                 }  
         }  
         return false;  
 }
  
/** 
* This function sorts an array by columns
* From http://www.php.net/manual/en/function.array-multisort.php#105115
* @ Copyright Ichier2003
**/ 
 function multi_sort() {  
     $i=0; 
     $args = func_get_args();  
     $marray = array_shift($args);  
     $msortline = 'return(array_multisort(';  
     foreach ($args as $arg) {  
         $i++;  
         if (is_string($arg)) {  
             foreach ($marray as $row) {  
                 $sortarr[$i][] = $row[$arg];  
             }  
         } else {  
             $sortarr[$i] = $arg;  
         }  
         $msortline .= '$sortarr['.$i.'],';  
     }  
     $msortline .= '$marray));';  
     eval($msortline);  
     return $marray; 

 } 

/** 
* This function converts smart qoutes 
* @author Craig Spurrier 
**/ 
function convert_smart_quotes($string){  
    return  str_replace( 
    array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6",chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
    array("'", "'", '"', '"', '-', '--', '...',"'", "'", '"', '"', '-', '--', '...'), 
    $string);  
}  

/** 
* This function sets null/empty to 0 string 
* @author Craig Spurrier 
**/ 
function default_zero($input){ 
    if(is_empty($input)){ 
        $input = '0'; 
    } 
    return $input; 
} 


/**
* This function gets url data between a start and end string and checks for certain contents. We are assuming that start and end strings are unique in the target file
* @author Jason Steelman
* @param string $url The url to fetch
* @param string $start the unique html string to search for to start the region
* @param string $end the unique html string to search for to end region region
* @param array $checks an array of unique html strings that we'll look for to make sure we got the right region
* @return string|false
*/
function screen_scrape($url, $start, $end, $checks, $operate_on_html_special_chars=true, $inclusive=true) {
	$raw = file_get_contents($url);
	$newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
	if($operate_on_html_special_chars) {
		$test = str_replace($newlines, "", html_entity_decode($raw));
		$start_pos = strpos($test, $start);
		$end_pos = strpos($test, $end, $start_pos);
		$content = substr($test, $start_pos, $end_pos - $start_pos);
	} else {
		$test = str_replace($newlines, "",  htmlentities($raw));
		$start_pos = strpos($test, $start);
		$end_pos = strpos($test, $end, $start_pos);
		$content = html_entity_decode(substr($test, $start_pos, $end_pos - $start_pos));
	}
	
	foreach ($checks as $string) {
		if(!strpos($content, $string)) return false;
	}
	if(!$inclusive) {
		if($operate_on_html_special_chars) {
			$strlen = strlen($start);
		} else {
			$strlen = strlen(html_entity_decode($start));
		}
		$content = substr($content, $strlen);
	}
	
	return $content;
}

/**
* This function rewites relative URLS to absolute urls 
* @author Jason Steelman
* @param string $str the string to operate on
* @param string $domain the domain we're going to make the urls absolute to (e.g. "sc.edu")
* @return string
*/
function rerwite_relative_urls_in_str($str, $domain) {
	$i = 0;
	while($i < strlen($str)) {
		$href_start = strpos($str, "href", $i);
		if($href_start) {
			$href_start += 6;
			$href_end = strpos($str, "\"", $href_start);
			$href = substr($str, $href_start, $href_end - $href_start);
			if(substr($href, 0, 4) != "http" && substr($href, 0, 4) != "feed") {
				if(substr($href, 0,1) != "/") $href = "/" . $href;
				$str = substr($str, 0, $href_start-6) . "href=\"http://". $domain . $href . substr($str, $href_end);
			}
			flush();
			$i = $href_start + 1;
		} else {
			break;
		}
	}
	$i = 0;
	while($i < strlen($str)) {
		$href_start = strpos($str, "src", $i);
		if($href_start) {
			$href_start += 6;
			$href_end = strpos($str, "\"", $href_start);
			$href = substr($str, $href_start, $href_end - $href_start);
			if(substr($href, 0, 4) != "http" && substr($href, 0, 4) != "feed") {
				if(substr($href, 0,1) != "/") $href = "/" . $href;
				$str = substr($str, 0, $href_start-6) . "src=\"http://". $domain . $href . substr($str, $href_end);
			}
			flush();
			$i = $href_start + 1;
		} else {
			break;
		}
	}
	return $str;
}

/**
 * This is (poor) random string generator. Poor in that its using some 
 * cryptographic functions and is pretty ungodly efficient. For the sake of 
 * being bizzare, $random1 and $random2 function source strings.
 * @author Jason Steelman <uscart@gmail.com>
 * @param int $length the length of random string to return
 * @return string A string of length $length
 */
function random_string($length = 256) {
	if($length == 30) return str_shuffle(MD5(microtime()));
	$random1 = "somethingfeelsrandomtomehereabcdefghijklmonpqrstuvqxyz";
	$random2 = "fortheloveofallthatsgoodandevilabcdefghijklmonpqurstuv";
	$string = str_shuffle(md5(microtime().str_shuffle($random1)) . md5(str_shuffle($random2).time()) . sha1(str_shuffle($random1.microtime())).sha1(str_shuffle($random2).time()).md5(str_shuffle($random1).time()).sha1(str_shuffle($random2).microtime()).sha1(str_shuffle($random1.microtime())).sha1(str_shuffle($random2).time()));
	return substr($string, 0, min($length, strlen($string)));
}

/**
 * A super strict string sterilizing procudure useful pretty much ONLY for 
 * making a string consistantly pure for html ids and classes
 * @author Jason Steelman <uscart@gmail.com>
 * @param string $string The string to be steralized
 * @return string The steralized string
 */
function classy($string) {
	$valid_chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9');
	$new_string = "";
	for($i = 0; $i<strlen($string); $i++) {
		if(in_array(strtolower(substr($string, $i, 1)), $valid_chars)) {
			$new_string .= substr($string, $i, 1);
		} 
	}
	return $new_string;
}

/**
 * Creates very short, very inconsistant file type names from a handful of 
 * mimetypes
 * @param string $file_type The mime-type to be looked up
 * @return string The resulting human-readible filetype if one was found.
 */
function pretty_filetypes($file_type) {
	$file_types = array(
	    "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => 'Word',
	    "application/vnd.ms-excel" => 'Excel 2010',
	    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "Excel 2007",
	    "application/vnd.ms-powerpoint" => "Powerpoint",
	    "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "Powerpoint 2007",
	    "application/pdf"=>"PDF",
	    "application/msexcel" => "Excel",
	    "application/mspowerpoint" => "Powerpoint",
	    "application/msword" => "Word",
	);
	if(isset($file_types[strtolower($file_type)])) {
		return $file_types[strtolower($file_type)];
	} else if(strpos ($file_type, 'wordprocessing')) {
		return "Word 2010+";
	} else {
		$parts = explode('/',$file_type);
		if($parts[0]=='image') {
			return $parts[1];
		}
		return $file_type;
	}
}