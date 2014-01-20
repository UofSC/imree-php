<?php
/**
 * This file contains functions for forms
 * @author Craig Spurrier <craig@hawaiihosting.com>
 * @package simms
 */

/**
* This function checks if any forms have been submitted by checking for post variables
* @author Craig Spurrier
* @version 0.4 Jan 2 2010 15:42EDT
* @return bool 
*/
function form_submitted(){
	if(count($_POST) > 0 OR count($_FILES) > 0){
		return TRUE;
	}else{
		return FALSE;
	}
}

/**
* This function builds the value portion of an input field
* @author Craig Spurrier
* @version 0.2 Nov 4 2010 11:30EDT
* @param string $id The name/id of the field
* @param string $value The default value. This will be overridden by any submitted user data
* @return string
*/
function f_value($id,$default_value=''){
	if(!is_empty(get_var($id,'request'))){
		$default_value = get_var($id,'request');
	}
	if(!is_empty($default_value)){
		return "value='".htmlspecialchars($default_value, ENT_QUOTES)."'";
	}
}

/**
* This function builds an input error area
* @author Craig Spurrier
* @version 0.3 Nov 24 2010 17:05EDT
* @param string $id The id of the input field
*/
function f_input_error($id,$output='echo'){

	$return = "<div id='".$id."_error' class='input_error'";
	if(!isset($GLOBALS['errors'][$id])){
		$return .= " style='display:none;'";
	}
	$return .= ">";
	if(isset($GLOBALS['errors'][$id])){
		$return .=  $GLOBALS['errors'][$id];
	}
	$return .= "</div>";
	
	if($output == 'return'){
		return $return;
	}else{
		echo $return;
	}
}
function form_input($id,$type,$value='',$opts=array()){//wrapper for legacy support
	if(isset($opts['selected'])){
		$selected = $opts['selected'];
	}else{
		$selected = NULL;	
	}
	if(isset($opts['class'])){
		$class = $opts['class'];
	}else{
		$class = NULL;	
	}
	if(isset($opts['output'])){
		$output = $opts['output'];
	}else{
		$output = NULL;	
	}
  f_input($id,$type,$value,$selected,$class,$output,$opts);
  f_input_error($id,$output);
}


/**
* This function builds input fields
* @author Craig Spurrier
* @version 0.6 May 3 2012
* @param string $id The name and id of the field
* @param string $type The type of field. Supported types include: text, password, checkbox, select, multi_select, radio, hidden, wysiwyg, small_textarea, tiny_num, file
* @param string $value The default value. This will be overridden by any submitted user data
* @param bool $selected (FALSE|TRUE)
* @param string $class 
* @param string $output
* @return mixed Output is determined by the last param
*/
function f_input($id,$type,$value='',$selected='0',$class='',$output='echo',$opts=array()){
	global $require_return_over_echo, $files_url;
	$name = $id;
	if(isset($require_return_over_echo) AND $require_return_over_echo === true) {
		$output = "return";
	}
	
	$id = str_replace(Array('[',']'),Array('_',''),$id);
	switch($type){ 
		case 'text':
			$return = "<input type='text' name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' ".f_value($id,$value);
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			if(isset($opts['placeholder'])){
				$return .= " placeholder='".htmlspecialchars($opts['placeholder'], ENT_QUOTES)."'";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= "/>\n";
		break;
		case 'password':
			$return = "<input type='password' name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' ";
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			if(isset($opts['placeholder'])){
				$return .= " placeholder='".htmlspecialchars($opts['placeholder'], ENT_QUOTES)."'";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= "/>\n";
		break;
		case 'checkbox':
			$return = "";
			if(is_array($value)){
				
				foreach($value AS $value=>$option){
						$return .= "<label for='".htmlspecialchars($id."_".$value, ENT_QUOTES)."'>".htmlspecialchars($option)."</label><input type='checkbox'  name='".htmlspecialchars($name, ENT_QUOTES)."[]' id='".htmlspecialchars($id."_".$value, ENT_QUOTES)."' value='".htmlspecialchars($value, ENT_QUOTES)."'"; 
					if(is_array($selected) AND isset($selected[$value])){
							$return .= " checked='checked'";
					}else{					
						if($selected == $value){
							$return .= " checked='checked'";
						}
					}
					$return .= " class='".htmlspecialchars($id, ENT_QUOTES)." ";
					if(!is_empty($class)){
						$return .= $class;
					}
					$return .= "'";
					if(isset($opts['required'])){
					    $return .= " required='required'";
					}
					$return .= ">";
				}
			}else{

					$return .= "<input type='checkbox'  name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' value='".htmlspecialchars($value, ENT_QUOTES)."'"; 
					if($selected == $value){
						$return .= " checked='checked'";
					}
					$return .= " class='".htmlspecialchars($id, ENT_QUOTES)." ";
					if(!is_empty($class)){
						$return .= $class;
					}
					$return .= "'";
					if(isset($opts['required'])){
					  $return .= " required='required'";
					}
					$return .= ">";
			}
			$return .= "\n";
		break;
		case 'select':
			if(!is_empty(get_var($id,'request'))){
				$selected = get_var($id,'request');
			}
			$return = "<select name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."'";
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= ">";
          		foreach($value AS $value1=>$option){
          			if(is_array($option)){
          				$return .=  "<optgroup label='".htmlspecialchars($value1, ENT_QUOTES)."'>";
          				foreach($option AS $valueA=>$optionA){
          					$return .= "<option value='".htmlspecialchars($valueA, ENT_QUOTES)."'"; 
						if($selected == $valueA){
							$return .= " selected='selected'";
						}
						$return .= ">".htmlspecialchars($optionA)."</option>\n";
          				}
          				$return .=  " </optgroup>";
          			}else{
					$return .= "<option value='".htmlspecialchars($value1, ENT_QUOTES)."'"; 
					if($selected == $value1){
						$return .= " selected='selected'";
					}
					$return .= ">".htmlspecialchars($option)."</option>\n";
				}
			}
           		$return .= "</select>";
       
			$return .= "\n";
		break;
		case 'multi_select':
			if(!is_empty(get_var($id,'request'))){
				$selected = get_var($id,'request');
			}
			$return = "<select name='".htmlspecialchars($name, ENT_QUOTES)."[]' id='".htmlspecialchars($id, ENT_QUOTES)."' multiple='multiple'";
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= ">";
			$values = $value;
             
             foreach($values AS $value=>$option){
				$return .= "<option value='".htmlspecialchars($value, ENT_QUOTES)."'"; 
				if(array_key_exists($value,$selected)){
					$return .= "selected='selected'";
				}
				$return .= ">".htmlspecialchars((string) $option)."</option>\n";
			}
           		$return .= "</select>";
			$return .= "\n";

		break;
		case 'radio':
			if(!is_empty(get_var($id,'request'))){
				$selected = get_var($id,'request');
			}
			$return = "";
                	foreach($value AS $value=>$option){
					$return .= "<label for='".htmlspecialchars($id."_".$value, ENT_QUOTES)."'><input type='radio'  name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id."_".$value, ENT_QUOTES)."' value='".htmlspecialchars($value, ENT_QUOTES)."'"; 
				if($selected == $value){
					$return .= " checked='checked'";
				}
				$return .= " class='".htmlspecialchars($id, ENT_QUOTES)." ";
				if(!is_empty($class)){
					$return .= $class;
				}
				$return .= "'";
				$return .= ">".htmlspecialchars($option)."</label>";
			}
			$return .= "\n";
		break;
		case 'hidden':
			$return = "<input type='hidden' name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' ".f_value($id,$value);
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			$return .= "/>\n";
		break;
		case 'textarea':
			if(!is_empty(get_var($id,'request'))){
				$value= get_var($id,'request');
			}
			$return = "<textarea name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' rows='12' cols='72'";
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= ">\n";
			$return .= htmlspecialchars($value);
			$return .= "</textarea>\n";

		break;
		case 'wysiwyg':
			if(!is_empty(get_var($id,'request'))){
				$value= get_var($id,'request');
			}
			$return = "<textarea name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' rows='12' cols='35'";
			if(!strpos($_SERVER['HTTP_USER_AGENT'],"iPhone") AND !strpos($_SERVER['HTTP_USER_AGENT'],"iPad")){
				$return .= " class='wysiwyg";
			}
			if(!is_empty($class)){
				$return .= " $class";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= "'>\n";
			$return .= htmlspecialchars($value);
			$return .= "</textarea>\n";

		break;
		case 'small_textarea':
			if(!is_empty(get_var($id,'request'))){
				$value= get_var($id,'request');
			}
			$return = "<textarea name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' rows='5' cols='30'";
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= ">\n";
			$return .= htmlspecialchars($value);
			$return .= "</textarea>\n";

		break;
		case 'tiny_num':
			$return = "<input type='text' name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' ".f_value($id,$value)." class='tiny_num";
			if(!is_empty($class)){
				$return .= " $class";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= "' maxlength='4'/>\n";
		break;
		case 'file':
			if($value) {
				$return = "<span id='original-value-for-".htmlspecialchars($id, ENT_QUOTES)."'>
					<input type='hidden' name='original-value-of-".htmlspecialchars($id, ENT_QUOTES)."' value=\"$value\" />";
				
				if(substr($value, 0, strlen($files_url)) == $files_url OR (is_numeric($value) AND $value > 0)) {
					
					if(substr($value, 0, strlen($files_url)) == $files_url) {
						$file_id = substr($value, 27);
						$url = $value;
					} else {
						$file_id = $value; //enables direct access to files tables for forms that edit files
						$url = $files_url . $value;
					}
					$file = tcl_file_properties($file_id);
					capitans_log("image file id: ".$file_id);
					if(substr($file['page_file_type'], 0, 5) == "image") {
						$return .= "<a href='$url' target='_blank' class='img-scaleable'><img src='$url?size=150' /></a>";
					} else {
						$return .= $value;
					}
				} else {
					$return .= $value;
				}
				
				$return .= "
					</span>
					<button id='change-image-for-".htmlspecialchars($id, ENT_QUOTES)."'>Change</button>
					<input type='file' name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' />
					<script type='text/javascript'>
						$(document).ready(function () {
							
							$('#".htmlspecialchars($id, ENT_QUOTES)."').hide();
							$('#change-image-for-".htmlspecialchars($id, ENT_QUOTES)."').focus(function (e) {
								e.preventDefault();
								$('#change-image-for-".htmlspecialchars($id, ENT_QUOTES)."').hide();
								$('#original-value-for-".htmlspecialchars($id, ENT_QUOTES)."').hide();
								$('input#".htmlspecialchars($id, ENT_QUOTES)."').show();
								$('input#".htmlspecialchars($id, ENT_QUOTES)."').trigger('click');
								return false;
							});
							
						});
					</script>
				";
			} else {
				$return = "<input type='file' name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' ".f_value($id,$value);
				if(!is_empty($class)){
					$return .= " class='$class'";
				}
				if(isset($opts['required'])){
					$return .= " required='required'";
				}
				$return .= "/>\n";
			}
		break;
		case 'datetime':
			if(!$value) {
				$value = date('m/d/Y h:i a');
			}
			$return = "<input type='text' name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' value='".date("m/d/Y h:i a",strtotime($value))."' ";
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= "/>
				<script type='text/javascript'>
                                    if(typeof($('#".htmlspecialchars($id, ENT_QUOTES)."').datetimepicker) != 'undefined') {
                                        $('#".htmlspecialchars($id, ENT_QUOTES)."').datetimepicker({
                                                timeFormat: 'hh:mm TT',	
                                                stepMinute: 15,
                                                showSecond: false
                                        });
                                    }
				</script>";
		break;
		case 'date':
			if(!$value) {
				$value = date('m/d/Y');
			}
			$return = "<input type='text' name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' value='".date("m/d/Y",strtotime($value))."' ";
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= "/>
				<script type='text/javascript'> 
                                    if(typeof($('#".htmlspecialchars($id, ENT_QUOTES)."').datepicker) != 'undefined') { 
                                        $('#".htmlspecialchars($id, ENT_QUOTES)."').datepicker({ numberOfMonths: 3 });
                                    }
                                </script>";
		break;
		
		case 'time':
			$return = "<input type='text' name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' ".f_value($id,$value);
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			$return .= "/>
				<script type='text/javascript'>
                                    if(typeof($('#".htmlspecialchars($id, ENT_QUOTES)."').timepicker) != 'undefined') { 
                                        $('#".htmlspecialchars($id, ENT_QUOTES)."').timepicker({
                                                timeFormat: 'hh:mm tt',	
                                                stepMinute: 15
                                        });
                                    }
				</script>";
		break;
		default:
			$return = "<input type='$type' name='".htmlspecialchars($name, ENT_QUOTES)."' id='".htmlspecialchars($id, ENT_QUOTES)."' ".f_value($id,$value);
			if(!is_empty($class)){
				$return .= " class='$class'";
			}
			if(isset($opts['required'])){
				$return .= " required='required'";
			}
			if(isset($opts['step'])){
				$return .= " step='".htmlspecialchars($opts['step'], ENT_QUOTES)."'";
			}
			if(isset($opts['placeholder'])){
				$return .= " placeholder='".htmlspecialchars($opts['placeholder'], ENT_QUOTES)."'";
			}
			$return .= "/>\n";
		break;
	}
	if($output == 'return'){
		return $return;
	}else{
		echo $return;
	}
}

/**
* This function builds the list part of a multi select list
* @author Craig Spurrier
* @version 0.3 Nov 24 2010 17:05EDT
* @param string $id
* @param array $values
*/
function f_multi_select_list($id,$values,$hidden_x = 0){
	echo "<ul id='",$id,"_multi_select_list'>";
	foreach($values AS $key=>$value){
		if(is_array($value)){
			if(isset($value['vtip'])){
				echo "<li class='vtip' title='".htmlspecialchars($value['vtip'])."'";
			}else{
				echo "<li";
			}
			if(isset($value['id'])) {
				echo " id=\"".rawurlencode($value['id'])."\" ";
			}
			echo ">";
			if(isset($value['url'])){
				echo "<a href='".$value['url']."'";
				if(isset($value['url_class'])){
					echo " class='".htmlspecialchars($value['url_class'])."'";
				}
				echo ">".htmlspecialchars($value['value'])."</a>";
			}else{
				echo htmlspecialchars($value['value']);
			}
			if(isset($value['icon_url'])){
				echo " <a href='".$value['icon_url']."'";
				if(isset($value['icon_url_class'])){
					echo " class='".htmlspecialchars($value['icon_url_class'])."'";
				}
				echo "><img src='".$value['icon']."' width='16' height='16' /></a>";
			}
			echo "<img src='images/remove.png' alt='remove' width='16' height='16' id='",$id,"_builder_",$key,"_remove' class='remove_button";
			if($hidden_x){
				echo " hidden";
			}
			echo "'/>";	
		}else{
			echo "<li>";
			echo htmlspecialchars($value);
			echo "<img src='images/remove.png' alt='remove' width='16' height='16' id='",$id,"_builder_",$key,"_remove' class='remove_button";
			if($hidden_x){
				echo " hidden";
			}
			echo "'/>";
		}
		echo "</li>";
	}
	echo "</ul>";
}

/**
* This function checks that all fields in the array are not empty and returns an error via the error array if they are
* @author Craig Spurrier
* @version 0.2 Nov 5 2010 11:15EDT
* @param array $fields
*/
function required_fields($fields){
	global $errors;
	foreach($fields AS $field){
		if(is_empty(get_var($field,'request'))){
			$errors[$field] ="This field should not be empty";
		}
	}
}

/**
* This function validate an e-mail address via a regex and returns an error via the error array if it is incorrect
* @author Craig Spurrier
* @version 0.2 Nov 5 2010 11:30EDT
* @param string $email_field The name/id of the e-mail field
*/
function validate_email($email_field){
	global $errors;
	if (!preg_match('/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i', get_var($email_field,'request'))) {
		$errors[$email_field] ="This does not appear to be a vaild e-mail address";
	}
}

/**
* This function validates an e-mail address via a regex and returns true or false
* @author Craig Spurrier
* @version 0.2 Apr 5 2011 11:30EDT
* @param string $email_field The name/id of the e-mail field
*/
function valid_email($email){
	if (preg_match('/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i', $email)) {
		return TRUE;
	}else{
		return FALSE;
	}
}

/**
* This function compares two fields and returns an error via the error array if they do not match
* @author Craig Spurrier
* @version 0.2 Nov 5 2010 11:40EDT
* @param string $field1 The name/id of the field to match to
* @param string $field2 The name/id of the e-mail to match from
*/
function match_fields($field1,$field2){
	global $errors;
	if(get_var($field1,'request') != get_var($field2,'request')){
		$errors[$field2] ="$field1 does not match $field2";
	}
}

/**
* This function validate a recaptcha and returns an error via the error array if it is incorrect
* @author Craig Spurrier
* @version 0.2 Nov 5 2010 11:45EDT
*/
function validate_captcha(){
	global $errors,$recaptcha_private_key;
        $resp = recaptcha_check_answer ($recaptcha_private_key,
                                        $_SERVER["REMOTE_ADDR"],
                                        $_POST["recaptcha_challenge_field"],
                                        $_POST["recaptcha_response_field"]);
        if (!$resp->is_valid) {
                $errors['recaptcha_response_field'] = "The CAPTCHA solution was incorrect. Please try again.";
        }
}

/**
* This function builds an add normalized area
* @author Craig Spurrier
* @version 0.3 Nov 24 2010 17:05EDT
* @param string $id The id of normalized field
* @param string $name The display name of this area
* @param array $fields An array of fields to include in the add area, each array should contain an array with the [0] as the id,[1] as the type, [2] as the default value/array of select options
* @param bool $hidden_button (0|1) Determines if the add button should be visible
*/
function f_input_add($id,$name,$fields,$hidden_button=0){
	if(user_can('Add normalized')){
		echo "
		<img src='images/add.png' alt='Add' width='16' height='16' id='add_",$id,"_button' class='add_button";
		if ($hidden_button){echo " hidden";}
		echo "'/>
		<fieldset id='add_",$id,"' class='add_area'>
		<legend>New $name</legend>";
			foreach($fields AS $name=>$field_opts){
				echo "	<p>
						<label for='add_",$id,"_",$field_opts[0],"'>$name</label><br/>";
	
						if(isset($field_opts[2])){
							f_input("add_".$id."_".$field_opts[0],$field_opts[1],$field_opts[2]);
						}else{
							f_input("add_".$id."_".$field_opts[0],$field_opts[1]);
						}
					echo 	f_input_error("add_".$id."_",$field_opts[0]),
				"	</p>";
			}
		echo "	<input type='button' value='Add'  id='add_",$id,"_action' />
		</fieldset>";
	}
}

/**
 * Creates a form that enables a one-to-many relationship to be created. If the
 * form is submited to a file that also calls this function with the same 
 * parameters, this function will also process and insert/update/delete 
 * assignments based on user input. Unless in rare cases, this function will
 * target the same page that is calling it so that user data from the form 
 * created by this function so that this function can correctly process it. 
 * 
 * This function will prefill selected values (check off the children that 
 * are already assigned to the parent) if a $first_key_id is supplied
 * 
 * For Sanity's Sake: "Table 1" is the table that is being assigned to have many
 * children. The children are chosen from available rows of "Table 2"
 * 
 * @param PDO $conn a valid and connected PHP Database Object
 * @param string $assignment_table The name of the relational database table that associates row_ids from table 1 to row_ids of table 2
 * @param string $assignment_table_first_key_column The primary index column of table 1
 * @param string $assignment_table_second_key_column The primary index column of table 2
 * @param mixed $first_key_id The value of the key to assign as the "parent"
 * @param stirng $second_table The name of the second table
 * @param type $second_table_key_column The column in the second table to use as the key 
 * @param mixed $second_table_label_column The column in the second table to use as the human-readible "name". If this value is an enumerated array, we'll grab multiple columns as the label (i.e. array('name_last', 'name_first') )
 * @param string $form_id The id of form (default: one_to_many). A hidden value is created with the same name to test if the form was actually submitted.
 * @param string $second_table_where A string (included the " WHERE condition ") that is used filter the possible assignement values from the second table. Limits the possible children by some criteria.
 * @param array $extra_fields_array An array of extra values that can be used as children (i.e array('0' => 'none', 'a' => 'all') ). These values are not stored in the database in a way that can be re-edited with this function by the user.
 * @return string Returns either a string that is the HTML form with prefilled values.
 */
function f_data_assignments_one2many($conn, $assignment_table, $assignment_table_first_key_column, $assignment_table_second_key_column,
							  $first_key_id, 
							  $second_table, $second_table_key_column, $second_table_label_column, 
							  $form_id = 'one_to_many', $second_table_where = "",
							  $extra_fields_array=false) {
	$form_name = "f_data2_".$form_id;
	if(is_array($second_table_label_column)) {
		$second_table_labels = $second_table_label_column;
		$second_table_label_column = $second_table_labels[0];
	}
	$secondary_table_options = db_query($conn, "SELECT * FROM $second_table $second_table_where ORDER BY $second_table_label_column ASC ");
	
	
	if(isset($_POST[$form_name])) {
		db_exec($conn, "DELETE FROM $assignment_table WHERE $assignment_table_first_key_column = ".db_escape($first_key_id));
		foreach($_POST as $key=>$val) {
			if(!empty($val)) {
				$insert_array = array(
						$assignment_table_first_key_column => $first_key_id, 
						$assignment_table_second_key_column => (int)substr($key, 2)
				);
				if($extra_fields_array) {
					foreach($extra_fields_array as  $extra_field_label=>$extra_field_key) {
						if(isset($_POST['o_'.substr($key, 2).$extra_field_key])) {
							$insert_array[$extra_field_key] = $_POST['o_'.substr($key, 2).$extra_field_key];	
						}
					}
				}
				db_exec($conn, build_insert_query($conn, $assignment_table, $insert_array));
			}
		}
	}
	$assignment_ids = array();
	$assignments = db_query($conn, "SELECT * FROM $assignment_table WHERE $assignment_table_first_key_column = ".db_escape($first_key_id));
	foreach($assignments as $assignment) {
		$assignment_ids[$assignment[$assignment_table_second_key_column]] = $assignment;
	}
	$string = "<form name='$form_name' action='#' method='POST' id='$form_name'>";
	foreach($secondary_table_options as $option) {
		$string .= "\n<div class='form_row'>";
		if($extra_fields_array AND array_key_exists($option[$second_table_key_column], $assignment_ids)) {
			foreach($extra_fields_array as $extra_field_label=>$extra_field_key) {
				$string .= "<input type='text' name='o_".$option[$second_table_key_column].$extra_field_key."' id='o_".$option[$second_table_key_column].$extra_field_key."' value=\"".htmlspecialchars($assignment_ids[$option[$second_table_key_column]]['subject_assignment_description'])."\" placeholder='".$extra_field_label."' />";
			}
		}
		$string .= "<input type='checkbox' name='o_".$option[$second_table_key_column]."' id='o_".$option[$second_table_key_column]."' value='1' ";
		if(array_key_exists($option[$second_table_key_column], $assignment_ids)) {
			$string .= " checked='checked' ";
		}
		$string .= " /><label for='o_".$option[$second_table_key_column]."'>";
		if(isset($second_table_labels) AND is_array($second_table_labels)) {
			foreach($second_table_labels as $label) {
				$string .= $option[$label]. " ";
			}
		} else {
			$string .= $option[$second_table_label_column];
		}
		$string .= "</label><br />";
		
		$string .= "</div>";
	}
	return $string . "<input type='submit'><input type='hidden' name='$form_name' value='1' /></form>";
	
}


/**
 * Generates a form for file uploading. The result of this function, when the 
 * form is submitted, is passed to f_upload_file(). 
 * 
 * The table needs to have data columns for name (varchar), contents (blob), 
 * size (int), type (varchar), restricted (boolean), title (varchar), 
 * update (datetime), user (varchar)
 * 
 * If you're never going to use this function as part of user management system,
 * you can save remove the parts of this function that relate to restricted and 
 * user. The restricted field is only set to 1 or 0 in the mysql database, it
 * does not handle actual data restrictions, that needs to be handled by the 
 * file-display function.
 * 
 * File upload are limited by the restrictions from the filesystem, 
 * apache (et al) handler, and network protocol.
 * 
 * @param array $array array('name'=>'', 'contents'=>'', size'=>'', 'type'=>'', 'restricted'=>'', 'title'=>'', 'updated'=>'', 'user'=>'');
 * @param PDO $conn a pdo connect resource
 * @param string $data_table The table that is storing the data
 * @param string $primary_key The primary index column name
 * @param mixed $row_id (Usually an int) If this form is updating an existing data row, supply the primary index id value. Use FALSE to indicate you want to make a new row. 
 * @param string $form_id The id of the form. Used for CSS/jQuery targeting. A hidden input variable is created using the same value of the form's id to test when the form is actually submitted.
 */
function f_data_files($array, $conn, $data_table, $primary_key, $row_id=false, $form_id='someting_file_unique', $user=false) {
	if(!isset($array['name'])) {
		die('input_array must contain a database column for \'name\'');
	}
	
	global $errors;
	$string = "";
	if(isset($_FILES[$form_id.'file'])) {
		$results = f_upload_file($array, $_FILES[$form_id.'file'], $conn, $data_table, $primary_key, $row_id, $_POST[$form_id.'restrict'], $_POST[$form_id.'title'], $user);
		if($results) {
			$string .= "Uploaded";
		} else {
			$string .= "Failed";
			$errors[] = "Failed to upload file";
		}
	} else {
		$string .= "<form action='#' id='$form_id' method='POST' enctype='multipart/form-data'>";
					if(isset($array['title'])) {
						$string .= "<label for=\"".$form_id."title\">File Title</label><input type='text' id=\"".$form_id."title\" name=\"".$form_id."title\" />";
					}
					if(isset($array['restricted'])) {
						$string .= "<label for=\"".$form_id."restrict\">File Restriction</label><select id=\"".$form_id."restrict\" name=\"".$form_id."restrict\" /><option value='0'>No</option><option value='1'>Yes</option><select>";
					}
					$string .= "<label for=\"".$form_id."file\">File</label><input type='file' id=\"".$form_id."file\" name=\"".$form_id."file\" />
					<input name='upload' type='submit' value='Upload'>
				</form>";
	}
	return $string;
}


/**
 * Inserts file data into a database. Commonly called when a user has submitted
 * a form that contains a <input type='file' HTML element
 * 
 * @param array $upload_table_array array('name'=>'', 'contents'=>'', size'=>'', 'type'=>'', 'restricted'=>'', 'title'=>'', 'updated'=>'', 'user'=>'');
 * @param array $file_array_from_post The direct array from $_FILE['some_input_name']
 * @param PDO $conn A valid and connected PHP Database Object
 * @param string $data_table The name of the database table where values will be stored and edited
 * @param string $data_table_primary_key The primary index column of the $data_table
 * @param mixed $row_id When FALSE, we'll make a new data entry. Otherwise it will replace the values WHERE $data_table_primary_key = $row_id
 * @param Boolean $restricted Determines if the row should be marked as "restricted". This function does nothing more than sets the value of the column "restricted". It does not actually do any data access restrictions. That should be handled by the display-data function.
 * @param String $title An option file title supplied by the user when uploaded. Should be used as the 'name' of the file when displayed if the value is provided on ingest.
 * @param mixed $user The user_id of the user that submitted the file.
 * @return mixed array('last_insert_id' => int, 'rows_changed' => int) or FALSE if the file upload failed to reach PHP
 */
function f_upload_file($upload_table_array, $file_array_from_post, $conn, $data_table='page_files', $data_table_primary_key='page_file_id', $row_id=false, $restricted=false, $title='', $user=false){
	if($file_array_from_post['size'] > 0) {
		$fp = fopen($file_array_from_post['tmp_name'], 'r');
		$content = fread($fp, filesize($file_array_from_post['tmp_name']));
		$q = array(
		    $upload_table_array['name'] => $file_array_from_post['name'],
		    $upload_table_array['contents'] => $content
		);
		if(isset($upload_table_array['size']))			$q[$upload_table_array['size']] = $file_array_from_post['size'];
		if(isset($upload_table_array['type']))			$q[$upload_table_array['type']] = $file_array_from_post['type'];
		if(isset($upload_table_array['restricted']))		$q[$upload_table_array['restricted']] = $restricted ? '1' : '0';
		if(isset($upload_table_array['title']))			$q[$upload_table_array['title']] = $title;
		if(isset($upload_table_array['updated']))		$q[$upload_table_array['updated']] = date('Y-m-d H:i:s');
		if(isset($upload_table_array['user']) AND $user)	$q[$upload_table_array['user']] = $user;
		if($row_id) {
			$results = db_exec($conn, build_update_query($conn, $data_table, $q, " $data_table_primary_key = ".db_escape($row_id, $conn)));
		} else {
			$results = db_exec($conn, build_insert_query($conn, $data_table, $q));
		}
		return $results;
	} else {
		return false;
	}
}

/**
 * Makes a form with target='#' so that the form is submitted back to this 
 * function for processing and insertion into a database. If a row_id is 
 * provided, this form will prefill the input elements with the data from that
 * row.
 * 
 * @global string $errors 
 * @global boolean $require_return_over_echo Attacks f_input to always return string instead of immediately echo
 * @global string $re_captcha_library the path and file name of the 1.11 recaptcha library
 * @global string $re_captcha_key_private The private key for your recaptcha
 * @global string $re_captcha_key_public The private key for your recaptcha
 * @param array $input_array and array of f_data_element objects
 * @param PDO $conn a valid and open PHP database object
 * @param string $data_table The target database table where values should be stored from this form
 * @param string $primary_key The primary index column inside $data_table
 * @param mixed $row_id If FALSE, the form will insert a new entry; otherwise it will UPDATE ... WHERE $primary_key = $row_id
 * @param string $form_id The value of ID of the Form used for targeting CSS/jQuery. A hidden input element is created with the same name to test if the form has been submitted
 * @param string $method Default POST, optionally you can use GET, but I'm not sure why that's useful anymore. @todo Needs refactor
 * @param function $insert_callback If set, we'll run insert_callback($new_id) after insertion
 * @param function $update_callback If set, we'll run update_callback($id) after updating the datatables
 * @param string $form_action Default #
 * @param boolean $resource_var UNSUED, sorry @todo refactor
 * @param function $validator_function runs before insertion for custom validation
 * @param boolean $allow_delete default FALSE. To work, $conn needs to have DELETE privilges
 * @param string $delete_url The url to load when a user clicks "delete row" (commonly, #)
 * @param string $delete_callback_url A url to load AFTER deletion. Very useful to prevent reloading a page that's no longer valid
 * @param function $delete_callback A function to run AFTER deletion
 * @param boolean $use_captcha Default TRUE
 * @return string The HTML form. Needs to be echoed somehow. 
 */
function f_data($input_array, $conn, $data_table, $primary_key, $row_id=false,$form_id='something_unique', $method = "POST", $insert_callback = false, $update_callback = false, $form_action = "#", $resource_var=false, $validator_function=false, $allow_delete =false, $delete_url="", $delete_callback_url=false, $delete_callback=false, $use_captcha=false) {
	global $errors, $require_return_over_echo, $re_captcha_library, $re_captcha_key_private, $re_captcha_key_public;
	$require_return_over_echo = true;
	$string = "";
	$form_name = "f_data_".  rawurlencode($form_id);
	$confirmation = "";
	$query_method = "";
	if($use_captcha AND !logged_in()) {
		require_once($re_captcha_library);
	}
	//prevent adding a new entry if the callback is an update form 
	$inserted = false;
	if(isset($_POST[$form_name."_inserted"]) AND $_POST[$form_name."_inserted"]) {
		$row_id = $_POST[$form_name."_inserted"];
	}
	if($allow_delete AND isset($_GET['this_delete'], $_GET['this_delete_row']) AND $_GET['this_delete_row'] > 0) {
		db_exec($conn, "DELETE FROM $data_table WHERE $primary_key = ".db_escape($_GET['this_delete_row'],$conn)." LIMIT 1");
		if($delete_callback) {
			$delete_callback();
		}
		if($delete_callback_url) {
			header('location: '.$delete_callback_url);
		} else {
			$row_id = false;
		}
	}
	
	if(get_var($form_name, $method)) {
		if($use_captcha AND !logged_in()) {
			$response = recaptcha_check_answer($re_captcha_key_private, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
			if(!$response->is_valid) {
				$errors[] = "The recaptcha you submitted is invalid";
			}
		}
		foreach($input_array as $element) {
			if(is_a($element, 'f_data_element')) {
					
				if($element->options and $element->options->required) {
					required_fields($element->id);
				}
				if($element->type == 'datetime') {
					$element->value = date("Y-m-d H:i:s",strtotime(get_var($element->id, $method)));	
				} else {
					$element->value = get_var($element->id, $method);
				}
			} 
		}
		if($errors) {
			$string .= print_r($errors, true);
		} else {
			if($validator_function AND function_exists($validator_function) AND $validator_function() !== true) {
				$string .= "<div class='input-error'>".($validator_function())."</div>";
			} else {
				if($row_id !== false) {
					//update items
					$query_method = "update";
					foreach ($input_array as $element) {
						if(is_a($element, 'f_data_element')) {
							$element->set($conn, $primary_key, $row_id, $data_table);	
						}
					}
					$confirmation = "Updated";
					if($update_callback !== false AND function_exists($update_callback)) {
						$update_callback($row_id);
					}
				} else {
					//insert items
					$query_method = "insert";
					$arr = array();
					foreach ($input_array as $element) {
						if($element->type == 'file') {
							if(isset($element->options) AND is_object($element->options) AND $element->options->data_table) {
								$upload_table_array = array(
								    'name' => $element->options->data_table_name,
								    'title' => $element->options->data_table_title,
								    'type' => $element->options->data_table_type,
								    'contents' => $element->options->data_table_contents,
								    'updated' => $element->options->data_table_updated,
								    'restricted' => $element->options->data_table_restricted,
								    'size' => $element->options->data_table_size,
								    'user' => $element->options->data_table_user,
								);
								$result = f_upload_file($upload_table_array, $_FILES[$element->id], $conn, $element->options->data_table, $element->options->data_table_key_column);
							} else {
								$upload_table_array = array(
								    'name' => 'page_file_name',
								    'title' => 'page_file_title',
								    'type' => 'page_file_type',
								    'contents' => 'page_file_contents',
								    'updated' => 'page_file_contents_date',
								    'restricted' => 'page_file_access_restricted',
								    'size' => 'page_file_size',
								    'user' => 'page_file_user',
								);
								$result = f_upload_file($upload_table_array, $_FILES[$element->id], $conn, 'sitewide.page_files', 'page_file_id');
							}
							if($result) {
								$file_id = $conn->lastInsertId();
								ul_new_category_assignment($file_id, 11);
								$arr[$element->id] = 'http://library.sc.edu/file/'.$file_id;
							} else {
								$arr[$element->id] = "";
							}
						} else {
							$arr[$element->id] = $element->value;
						}	
					}
					$result = db_exec($conn, build_insert_query($conn, $data_table, $arr));
					$confirmation = "Inserted new record";
					if($insert_callback !== false) {
						$insert_callback($result['last_id']);
					}
					$row_id = $result['last_id'];
					$inserted = true;
				}
			}
		}
	} 
	if(!get_var($form_name, $method) OR ($insert_callback === false AND $query_method == "insert") OR ($update_callback === false AND $query_method == "update") ) {
		if(!$errors) {
			$fields = "";
			foreach($input_array as $element) {
				if(is_a($element, 'f_data_element') AND $element->type != "password") {
					$fields .= $element->id.", ";
				}
			}
			if($fields != "") {
				//having trouble here with rows who have an id of 0
				$p_key_query_value = ($row_id === false ? "'-1'" : db_escape($row_id, $conn));
				$q = "SELECT ".substr($fields, 0, -2)." FROM $data_table WHERE $primary_key = ".$p_key_query_value;
				$original_values = db_query($conn, $q);
				foreach($input_array as $element) {
					if(is_a($element, 'f_data_element') AND $original_values) {
						if($element->type != 'password' AND $element->type != 'select' AND $element->type != 'multi_select') {
							$element->value = $original_values[0][$element->id];
						}
						if($element->type == 'select') {
							$element->value = $element->values;
							$element->selected = $original_values[0][$element->id];
						}
						if( $element->type == 'multi_select') {
							$element->value = $element->values;
							$element->selected = array($original_values[0][$element->id]=>$original_values[0][$element->id]);
						}
					} 
					if(is_a($element, 'f_data_element')) {
						if($element->options AND $element->options->normalized_table_primary_label_column) {
							if(is_array($element->options->normalized_table_primary_label_column)){
								$select = "";
								foreach($element->options->normalized_table_primary_label_column as $label_part) {
									$select .= ", ".$label_part;
								}
								$order_by = $element->options->normalized_table_primary_label_column[0];
							} else {
								$order_by = $element->options->normalized_table_primary_label_column;
								$select = ", ".$element->options->normalized_table_primary_key_column . ", ".$element->options->normalized_table_primary_label_column;
							}
							$these_options = db_query($conn, "SELECT ".$element->options->normalized_table_primary_key_column." $select FROM ".$element->options->normalized_table." ".($element->options->normalized_table_limit_condition)." ORDER BY $order_by ASC ");
							$arr =array();
							if($element->options->normalized_table_none_label) {
								$arr[$element->options->normalized_table_none_value] = $element->options->normalized_table_none_label;
							}
							foreach($these_options as $option) {
								if(is_array($element->options->normalized_table_primary_label_column)) {
									$label_value = "";
									foreach($element->options->normalized_table_primary_label_column as $label_part) {
										$label_value .= $option[$label_part] . " ";
									}
									$arr[$option[$element->options->normalized_table_primary_key_column]] = $label_value;
								} else {
									$arr[$option[$element->options->normalized_table_primary_key_column]] = $option[$element->options->normalized_table_primary_label_column];
								}
							}
							$element->values = $arr;
						}
					}
				}
			}
		}
		
		$string .= "<form action='$form_action' method='POST' enctype='multipart/form-data' id='".classy($form_id)."'>";
		foreach($input_array as $element) {
			if(is_a($element, 'f_data_element')) {
				$string .= "\n".$element->say()."\n";
				if($element->type != "hidden") {
					$string .= "<br class='clearAll' />";
				}
			}
		}
		if($allow_delete and $row_id) {
			if(strpos($delete_url, '?')!==false) {
				$delete_url .= "&";
			} else {
				$delete_url .= "?";
			}
			$delete_url .= "this_delete=1&this_delete_row=".urlencode($row_id)."&row_id=".urlencode($row_id);
			$string .= "<label></label><a style='margin-bottom:5px;' class='delete-this' href='#'>Delete This</a> <br />&nbsp;
				<script type='text/javascript'>
					$(document).ready(function () {
						$('a.delete-this').click(function (e) {
							e.preventDefault();
							$('#main-content').append(\"<div id='this-modal'>You really want to delete this? <a class='button' href='$delete_url'>Yes</a></div>\");
							$('#this-modal a.button').button();
							$('#this-modal').dialog({
								title:'Delete Confirmation',
								modal:true,
								height:150,
								width:300,
								close:function (event, ui) ".'{$(ui).remove();}'."
							});
							return false;
						});
					});
				</script>
				<br />";
		}
		$string .= "
			<input type='hidden' name=\"$form_name\" value='1' />";
		if($use_captcha AND !logged_in()) {
			$string .= recaptcha_get_html($re_captcha_key_public);
		}
		if($inserted) {
			$string .= "<input type='hidden' name=\"".$form_name."_inserted\" value='$row_id' />";
		}
		$string .= "
			<button id='asubmitbutton' type='submit'>Submit</button> 
			</form>";
	} 
	if(count($errors) === 0) {
		$string = "<div class='f_data_confirmation'>$confirmation</div>".$string;
	}
	return $string;
}


/**
 * Identical to f_data EXCEPT it emails the result to $send_tos. As a result, 
 * there is no UPDATE method here because that wouldn't make any sense. 
 * 
 * @global string $errors
 * @global string $re_captcha_library the path and file name of the 1.11 recaptcha library
 * @global string $re_captcha_key_private The private key for your recaptcha
 * @global string $re_captcha_key_public The private key for your recaptcha
 * @global string $reply_email The default no-reply email address. The domain (@sample.com) should match the domain sending the email to avoid obvious spam blockers
 * @param array $input_array and array of f_data_element objects
 * @param mixed $send_tos Either a single email as a string; or, an enumerated array of email strings.
 * @param string $subject The subject of the email
 * @param string $form_name The value of ID of the Form used for targeting CSS/jQuery. A hidden input element is created with the same name to test if the form has been submitted
 * @param string $confirmation_message 
 * @param PDO $files_conn If one of the $input_elements is type=file then we'll need to upload those files to server. 
 * @param function $validator_function runs BEFORE emailing data. Stores the results in global $errors
 * @param boolean $use_captcha
 * @return string The HTML Form or the confirmation message. Needs to be echoed.
 */
function f_data_email_form($input_array, $send_tos, $subject, $form_name = 'form_to_email', $confirmation_message = 'Thank you. Your message has been sent.', $files_conn=null, $validator_function = false, $use_captcha=true) {
	global $errors, $re_captcha_library, $re_captcha_key_private, $re_captcha_key_public, $reply_email;
	if($use_captcha AND !logged_in()) {
		require_once($re_captcha_library);
	}
	$string = "";
	if(isset($_POST[$form_name]) AND $_POST[$form_name] === '1') {
		if($use_captcha AND !logged_in()) {
			$response = recaptcha_check_answer($re_captcha_key_private, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
			if(!$response->is_valid) {
				$errors[] = "The recaptcha you submitted is invalid";
			}
		}
		
		$email = "<p>This is an automated email from the University Libraries. The following information has been submitted in the Web form $form_name.<p><table>";
		foreach($input_array as $element) {
			if(is_a($element, 'f_data_element')) {
				if($element->options and $element->options->required) {
					required_fields(array($element->id));
				}
				if($element->type == 'datetime') {
					$element->value = date("Y-m-d H:i:s",strtotime(get_var($element->id, 'POST')));	
				} else {
					$element->value = get_var($element->id, "POST");
				}
				capitans_log($element->id.": ".$element->type.": ".var_export($element->value,1));
			} 
		}
		if($errors) {
			foreach($errors as $err) {
				$string .= "<div class='form-error'>".$err."</div>";
			}
		} else {
			if($validator_function AND function_exists($validator_function) AND $validator_function() !== true) {
				$string .= "<div class='input-error'>".($validator_function())."</div>";
			} else {
				foreach ($input_array as $element) {
					if($element->type == 'file') {
						if(isset($element->options) AND is_object($element->options) AND $element->options->data_table) {
							$upload_table_array = array(
							    'name' => $element->options->data_table_name,
							    'title' => $element->options->data_table_title,
							    'type' => $element->options->data_table_type,
							    'contents' => $element->options->data_table_contents,
							    'updated' => $element->options->data_table_updated,
							    'restricted' => $element->options->data_table_restricted,
							    'size' => $element->options->data_table_size,
							    'user' => $element->options->data_table_user,
							);
							$result = f_upload_file($upload_table_array, $_FILES[$element->id], $files_conn, $element->options->data_table, $element->options->data_table_key_column);
						} else {
							$upload_table_array = array(
							    'name' => 'page_file_name',
							    'title' => 'page_file_title',
							    'type' => 'page_file_type',
							    'contents' => 'page_file_contents',
							    'updated' => 'page_file_contents_date',
							    'restricted' => 'page_file_access_restricted',
							    'size' => 'page_file_size',
							    'user' => 'page_file_user',
							);
							$result = f_upload_file($upload_table_array, $_FILES[$element->id], $files_conn, 'sitewide.page_files', 'page_file_id',false,true);
						}
						if($result) {
							$file_id = $files_conn->lastInsertId();
							ul_new_category_assignment($file_id, 16);
							$email .= "<tr><td>".$element->label."</td><td>http://library.sc.edu/file/".$file_id."</td></tr>";
						} else {
							$email .= "<tr><td>".$element->label."</td><td>The file did not upload correctly. That's all we know.</td></tr>";
						}
					} else {
						$email .= "<tr><td>".$element->label."</td><td>".$element->value."</td></tr>";
					}
				}
				$email .= "</table>";
				if(is_string($send_tos)) {
					$send_tos = array($send_tos);
				} 
				foreach($send_tos as $to) {
					send_email($to, $reply_email, $subject, $email, $email);
				}
				
				$string = $confirmation_message;
			}
		}
	} 
	if(!isset($_POST[$form_name]) OR $errors) {
		$string .= "<form action='#' method='POST' enctype='multipart/form-data' id='$form_name'>";
		foreach($input_array as $element) {
			if(is_a($element, 'f_data_element')) {
				$string .= "\n".$element->say()."\n";
				if($element->type != "hidden") {
					$string .= "<br class='clearAll' />";
				}
			}
		}
		$string .= "
			<input type='hidden' name=\"$form_name\" value='1' />";
		if($use_captcha AND !logged_in()) {
			$string .= recaptcha_get_html($re_captcha_key_public);
		}
		$string .= "
			<button id='asubmitbutton' type='submit'>Submit</button>
			</form>";
	}
	return $string;
}



class f_data_element_options {
    	public $place_holder = false;
	public $required = false;
	public $step = false;
	public $normalized_table = false;
	public $normalized_table_primary_key_column = false;
	public $normalized_table_primary_label_column = false;
	public $normalized_table_none_value = false;
	public $normalized_table_none_label = false;
	public $normalized_table_limit_condition = false;
	public $override_val = false;
	public $data_table = false;
	public $data_table_key_column = false;
	public $data_table_name = false;
	public $data_table_contents = false;
	public $data_table_size = false;
	public $data_table_type = false;
	public $data_table_restricted = false;
	public $data_table_title = false;
	public $data_table_updated = false;
	public $data_table_user = false;
	public $data_table_delete_and_replace = false;
        /**
         * Stores the options for data_elements. Normalized_table options are useful 
         * for f_data_one_to_many. $data_table options are useful for f_data_files
         * @param array $array an array of options for fast assignments
         */
	function __construct($array=array()) {
		isset($array['placeholder'])		? $this->place_holder = $array['placeholder'] : 1;
		isset($array['required'])		? $this->required = $array['required'] : 1;
		isset($array['step'])			? $this->step = $array['step'] : 1;
		isset($array['normalized_table'])	? $this->normalized_table = $array['normalized_table'] : 1;
		isset($array['normalized_table_primary_key_column'])	? $this->normalized_table_primary_key_column = $array['normalized_table_primary_key_column'] : 1;
		isset($array['normalized_table_primary_label_column'])	? $this->normalized_table_primary_label_column = $array['normalized_table_primary_label_column'] : 1;
		isset($array['normalized_table_none_value'])	? $this->normalized_table_none_value = $array['normalized_table_none_value'] : 0;
		isset($array['normalized_table_none_label'])	? $this->normalized_table_none_label = $array['normalized_table_none_label'] : 0;
		isset($array['normalized_table_limit_condition'])	? $this->normalized_table_limit_condition = $array['normalized_table_limit_condition'] : "";
		isset($array['override_val'])	? $this->override_val = $array['override_val'] : false;
		isset($array['data_table'])	? $this->data_table = $array['data_table'] : 0;
		isset($array['data_table_key_column'])	? $this->data_table = $array['data_table'] : 0;
		isset($array['data_table_name'])	? $this->data_table_name = $array['data_table_name'] : 0;
		isset($array['data_table_contents'])	? $this->data_table_contents = $array['data_table_contents'] : 0;
		isset($array['data_table_size'])	? $this->data_table_size = $array['data_table_size'] : 0;
		isset($array['data_table_type'])	? $this->data_table_type = $array['data_table_type'] : 0;
		isset($array['data_table_restricted'])	? $this->data_table_restricted = $array['data_table_restricted'] : 0;
		isset($array['data_table_title'])	? $this->data_table_title = $array['data_table_title'] : 0;
		isset($array['data_table_updated'])	? $this->data_table_updated = $array['data_table_updated'] : 0;
		isset($array['data_table_user'])	? $this->data_table_user = $array['data_table_user'] : 0;
		isset($array['data_table_delete_and_replace'])	? $this->data_table_delete_and_replace = $array['data_table_delete_and_replace'] : false;
	}
        /**
         * Makes an array that can be accessed natively by f_input. I've done this so f_input can remain an abstract input writer and can exist outside f_data
         * @return array an array of 'options' that can be passed to f_input
         */
	public function generate() {
		$arr = array();
		$this->place_holder !== false		? $arr['placeholder'] = $this->place_holder : 1 ;
		$this->required !== false		? $arr['required'] = $this->required : 1 ;
		$this->step !== false			? $arr['step'] = $this->step : 1 ;
		return $arr;
	}
}



class f_data_element {
        /**
         * an already created/defined f_data_element_options object. Options are optional.
         * @var f_data_element_options  
         */
	public $options;
        
        /**
         * This is the HTML ID of the input element AND, more importantly, the NAME of the database COLUMN (when used with f_data or f_data_one_to_many) that should be used to prefil values from and save updated values to.
         * @var string
         */
	public $id;
        
        /**
         * The HTML input type. text, password, hidden, wysiwyg, select, multi_select, radio, textarea, small_textarea, tiny_num, file, datetime, date, time
         * @var string 
         */
	public $type;
        
        /**
         * The default value of the input element. If using with [f_data or f_data_one_to_many] AND we're grabbing an existing row of data; then, this value will be replaced by the value from the database
         * Depending on the $type of element this is, $value may mean different things. See f_input for more details on $value
         * @var mixed 
         */
	public $value;
        
        /**
         * Determines what should be the selected value for things like checkboxes or radios or selects or multi_selects
         * @var string
         */
	public $selected;
        
        /**
         * The HTML class used for targeting by CSS/jQuery
         * @var string 
         */
	public $class;
        
        /**
         * Creates a label element BEFORE the input element. 
         * A valuable CSS property would be form#form_id label {float:left; width:200px; }
         * @var string
         */
	public $label;
        
        /**
         * The value (string) or values (array) of the HTML element. Use an array for things like checkboxes, selects, and radios
         * See f_input for more on $value
         * @var mixed 
         */
	public $values;
        
        /**
         * An optional note about this html element to the user. Displayed to the right or below the input element wrapped inside a div with class = 'instructions'
         * @var string 
         */
	public $instructions;
        
        /**
         * Setup a html input element for use in f_data f_data_one_to_many and f_data_email.
         * @global boolean $require_return_over_echo Forces f_input to return string instead of echo
         * @param string $label Creates a label element BEFORE the input element. 
         * @param string $id This is the HTML ID of the input element AND, more importantly, the NAME of the database COLUMN (when used with f_data or f_data_one_to_many) that should be used to prefil values from and save updated values to.
         * @param string $type The HTML input type. text, password, hidden, wysiwyg, select, multi_select, radio, textarea, small_textarea, tiny_num, file, datetime, date, time
         * @param string $value The default value of the input element. If using with [f_data or f_data_one_to_many] AND we're grabbing an existing row of data; then, this value will be replaced by the value from the database. Depending on the $type of element this is, $value may mean different things. See f_input for more details on $value
         * @param string $selected  Determines what should be the selected value for things like checkboxes or radios or selects or multi_selects
         * @param string $class The HTML class used for targeting by CSS/jQuery
         * @param f_data_input_options $opts   an already created/defined f_data_element_options object. Options are optional.
         * @param type $instructions An optional note about this html element to the user. Displayed to the right or below the input element wrapped inside a div with class = 'instructions'
         */
	public function __construct($label, $id,$type,$value='',$selected='0',$class='', $opts=false, $instructions=false) {
		global $require_return_over_echo;
		$require_return_over_echo = true;
		$this->id = $id;
		$this->type = $type;
		$this->value = $value;
		$this->values = $value;
		$this->selected = $selected;
		$this->class = $class;
		$this->options = $opts;
		$this->label = $label;
		$this->instructions = $instructions;
		if(is_a($this->options,'f_data_element_options') AND $this->options->override_val) {
			$this->value = $this->options->override_val;
		}
	}
	
        /**
         * Implements f_input
         * Makes the HTML input element
         * @return string The HTML input element
         */
	public function say() {
		if(is_a($this->options,'f_data_element_options') AND $this->options->override_val) {
			$this->value = $this->options->override_val;
		}
		$str = "";
		if($this->type !== 'hidden') {
			$str .= "<label for='".$this->id."'>".$this->label."</label>";
		}
		if($this->type === 'wysiwyg') {
			$str .= "<br style='clear:both; display:block;'/>";
			$this->class .= " require-wysiwyg";
		}
		if($this->type == "select") {
			$this->value = $this->values;
		} 
		$str .= f_input($this->id, $this->type, $this->value, $this->selected, $this->class, 'return', ($this->options ? $this->options->generate(): array()));
		if($this->instructions) {
			$str .= "<span class='instructions'>".$this->instructions."</span>";
		}
		return $str;
	}
	
        /**
         * Used for updating existing records; 'insertion' is handled by f_data
         * @param PDO $conn A valid and already connected PHP database object
         * @param string $primary_key The primary index column name of $data_table
         * @param type $id The row to be updated
         * @param type $data_table The database table name to be updated
         * @return mixed Either array('rows_updated'=>VAL, 'last_inserted_id'=>null) OR Boolean for file upload updates
         */
	public function set($conn, $primary_key, $id, $data_table) {
		if(is_a($this->options, "f_data_element_options") AND $this->options->override_val) {
			$this->value = $this->options->override_val;
		}
		
		if($this->options AND $this->options->normalized_table) {
			$q = build_update_query($conn, $data_table, array($this->id => $this->value), " $primary_key = ".  db_escape($id, $conn));
		} elseif($this->type == 'file') {
			$q = false;
			if(isset($_FILES[$this->id]) AND $_FILES[$this->id]['error'] == 0 AND $_FILES[$this->id]['size'] > 0) {
				if(isset($_POST["original-value-of-".htmlspecialchars($this->id, ENT_QUOTES)])) {
					$file_url = $_POST["original-value-of-".htmlspecialchars($this->id, ENT_QUOTES)];
					if((substr($file_url, 0, 27) == "http://library.sc.edu/file/" OR (is_numeric($file_url) AND $file_url > 0)) AND isset($this->options) AND $this->options->data_table_delete_and_replace === true) {
						if(substr($file_url, 0, 27) == "http://library.sc.edu/file/") {
							$file_id = substr($file_url, 27);
						} else {
							$file_id = $file_url; //enables direct access to files table for form(s) that edit files
						}
					} else {
						$file_id = false;
					}
				} else {
					$file_id = false;
				}
				if(isset($this->options) AND isset($this->options->data_table) AND strlen($this->options->data_table) > 1) {
					$upload_table_array = array(
					    'name' => $this->options->data_table_name,
					    'title' => $this->options->data_table_title,
					    'type' => $this->options->data_table_type,
					    'contents' => $this->options->data_table_contents,
					    'updated' => $this->options->data_table_updated,
					    'restricted' => $this->options->data_table_restricted,
					    'size' => $this->options->data_table_size,
					    'user' => $this->options->data_table_user,
					);
					$result = f_upload_file($upload_table_array, $_FILES[$this->id], $conn, $this->options->data_table, $this->options->data_table_key_column, $file_id);
				} else {
					$upload_table_array = array(
					    'name' => 'page_file_name',
					    'title' => 'page_file_title',
					    'type' => 'page_file_type',
					    'contents' => 'page_file_contents',
					    'updated' => 'page_file_contents_date',
					    'restricted' => 'page_file_access_restricted',
					    'size' => 'page_file_size',
					    'user' => 'page_file_user',
					);
					$result = f_upload_file($upload_table_array, $_FILES[$this->id], $conn, 'sitewide.page_files', 'page_file_id', $file_id);
				}
				if($result['rows_changed'] > 0 AND $file_id === false) {
					$file_id = $result['last_id'];
					ul_new_category_assignment($file_id, 11);
					$q = build_update_query($conn, $data_table, array($this->id => "http://library.sc.edu/file/$file_id"), " $primary_key = ".  db_escape($id, $conn));
				}
			} 
			
		} else {
			$q = build_update_query($conn, $data_table, array($this->id => $this->value), " $primary_key = ".  db_escape($id, $conn));
		}
		
		if($this->type == 'password') {
			$this->value = "";
		}
		if($q) {
			return  db_exec($conn, $q);
		} else {
			return 1;
		}
		
	}
	
}

?>