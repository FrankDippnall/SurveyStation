<?php

// Things to notice:
// This script holds the sanitisation function that we pass all our user data to
// This script holds the validation functions that double-check our user data is valid
// You can add new PHP functions to validate different kinds of user data (e.g., emails, dates) by following the same convention:
// if the data is valid return an empty string, if the data is invalid return a help message



// function to sanitise (clean) user data:
function sanitise($str, $connection)
{
	if (get_magic_quotes_gpc())
	{
		// just in case server is running an old version of PHP with "magic quotes" running:
		$str = stripslashes($str);
	}
	// escape any dangerous characters, e.g. quotes:
	$str = mysqli_real_escape_string($connection, $str);
	// ensure any html code is safe by converting reserved characters to entities:
	$str = htmlentities($str);
	// return the cleaned string:
	return $str;
}





// if the data is valid return an empty string, if the data is invalid return a help message
function validateString($field, $minlength, $maxlength) 
{
    if (strlen($field)<$minlength) {
		// wasn't a valid length, return a help message:		
        return "Minimum length: " . $minlength; 
    }
	elseif (strlen($field)>$maxlength) { 
		// wasn't a valid length, return a help message:
        return "Maximum length: " . $maxlength; 
    }
	// data was valid, return an empty string:
    return ""; 
}





// if the data is valid return an empty string, if the data is invalid return a help message
function validateInt($field, $min, $max) 
{ 
	// see PHP manual for more info on the options: http://php.net/manual/en/function.filter-var.php
	$options = array("options" => array("min_range"=>$min,"max_range"=>$max));
    
	if (!filter_var($field, FILTER_VALIDATE_INT, $options)) 
    { 
		// wasn't a valid integer, return a help message:
        return "Not a valid number (must be whole and in the range: " . $min . " to " . $max . ")"; 
    }
	// data was valid, return an empty string:
    return ""; 
}

// all other validation functions should follow the same rule:
// if the data is valid return an empty string, if the data is invalid return a help message
// ...

function salt($string){
	//salt the given string.
	return "sA_lt".$string."sAL_t";
}

function validateEmail($field){
	// validate an email using regex. valid email example is " blah@blah.bh "
	$isEmail = preg_match("#^.+@.+\..+$#",$field);
	if ($isEmail){
		return "";
	} else return "Not a valid email.";
}

function validateDate($field){
	// validate a date using regex. valid date example is " 1996-03-23 "
	$isDate = preg_match("#^\d{4}-\d{1,2}-\d{1,2}$#",$field);
	if ($isDate){
		$date = explode('-',$field);
		if (checkdate($date[1],$date[2],$date[0])===FALSE){
			return "Not a valid date.";
		} else return "";
	} else return "Not a valid date. Must be in yyyy-mm-dd format.";
}

function validateBirthDate($field){
	//validate a Birth Date. if date is valid, this also checks if date is not in the future.
	$result = validateDate($field);
	if ($result === ""){
		//check date is not in future
		$date = new DateTime($field);
		$today = new DateTime();
		if ($date < $today){
			return "";
		} else return "You were born in the future?";
	} else return $result;
}

function validatePhoneNum($field){
	// validate a phone num using regex. number must consist only of numerics and must be between 1 and 15 characters
	if (strlen($field)>15) return "Invalid Phone Number (Max length 15)";
	if (strlen($field)==0) return "Input a Phone Number";
	if (preg_match("#^\d+$#",$field) == FALSE) {return "Invalid Phone Number (numbers only)";}
	else{
		return "";
	}
	
}

function validateCountry($field){
	return "";
	//TODO
}

function validateQType($field){
	//validates a Question Type. it can only be one of the set below.
	if ($field === "") return "Please select a question type.";
	$qtypes = array("textinput", "numberinput", "percentageinput", "yesno", "yesnounsure", "rate10", "likert");
	for ($i=0; $i<count($qtypes);$i++){
		if($field === $qtypes[$i]) return "";
	}
	return "Invalid question type.";
}

function validateGender($field){
	//validate gender input. this is mainly to check for formatting, as the DB only accepts 1 or 0 characters for gender.
	if ($field === "M" or $field === "F" or $field === "O") return "";
	else return "Invalid gender.";
}

function create_table(mysqli_result $result, $destination = "", $field = ""){
	//move read caret to first row.
	mysqli_data_seek($result, 0); 
	// creates an html table from the $result.
	if ($result === FALSE){
		return "connection error.";
	} else {
		$clickable = true;
		if ($destination === "" or $field === ""){
			$clickable = false;
		} 
		$rows = "";
		$rowcount = mysqli_num_rows($result);
		$headcount = mysqli_num_fields($result);
		$data = mysqli_fetch_assoc($result);
		$colheaders = array_keys($data);
		//create heading row
		$currentrow = "";
		for ($col = 0; $col < $headcount; $col++){
			$currentrow .= "<th>".$colheaders[$col]."</th>";
		}
		$rows .= "<tr>".$currentrow."</tr>";
		//create data rows
		for ($row = 1; $row <= $rowcount; $row++){
			$currentrow = "";
			for ($col = 0; $col < $headcount; $col++){
				if (strtolower($colheaders[$col]) === "url"){
					$currentcell = "<a href=".$data[$colheaders[$col]].">".$data[$colheaders[$col]]."</a>";
				} else {
					$currentcell = $data[$colheaders[$col]];
				}
				
				//replace empty dates with ""
				if (strtolower($colheaders[$col]) === "dob") {
					if ($currentcell === "0000-00-00") $currentcell = "";
				}
				if ($clickable){
					$currentrow .= "<td style='cursor:pointer;'>".$currentcell."</td>";
				} else{
					$currentrow .= "<td style='cursor:default'>".$currentcell."</td>";
				}
				
			}
			$rows .= "<tr>".$currentrow."</tr>";
			if ($row != $rowcount) $data = mysqli_fetch_assoc($result);
		}
		if ($clickable){
			return "<table class='clickable data_table' data-destination='$destination' data-field='$field'>".$rows."</table>";
		} else {
			return "<table class='data_table'>".$rows."</table>";
		}
		
	}
	

}

function user_string($username){
	$class = "'user'";
	if ($username == "admin"){
		$class="'admin'";
	}
	return "<span class=$class>$username</span>";
}

function alert_box($message){
	return "<div id='alert_box'hidden>$message<button class='hide' title='close' onclick='$(this).parent().fadeOut(200);'></button></div>";
}

function setDefaultSelect($select, $matchvalue, $ucwords = false /*is the value in uc words?*/){
	//normalise $matchvalue
	if ($ucwords) $matchvalue = ucwords($matchvalue);
	//sets the default value of the combo box to be the $value specified.
	$count = substr_count($select, '<option value="');
	$offset = 0;
	for ($i=0;$i<=$count;$i++){
		
		$search = strpos($select, '<option value="', $offset);
		$end = strpos($select, '">', $offset);
		if ($search === FALSE or $end === FALSE){
			break;
		} else {
			//read option value
			$readpoint = $search+strlen('<option value="');
			$readlen = $end-$readpoint;
			$value = substr($select, $readpoint, $readlen);
			if ($value === $matchvalue){
				//match found, remove old option
				//construct old option to replace
				$old_option = '<option value="'.$value.'">';
				$new_option = '<option selected="selected" value="'.$value.'">';
				return str_replace($old_option, $new_option, $select);

			}
		}
		//update the offset to search multiple
		$offset = $end +1;
	}
	return $select;
}

function setDefaultRadio($radio, $matchvalue, $ucwords = false){
	if ($ucwords) $matchvalue = ucwords($matchvalue);
	//sets the default value of the radio buttons to be the $value specified.
	$count = substr_count($radio, "value='");
	$offset = 0;
	for ($i=0;$i<$count;$i++){
		$search = strpos($radio, "value='", $offset);
		$offset = $search+1;
		$end = strpos($radio, "'>", $offset);
		if ($search === FALSE or $end === FALSE){
			break;
		} else {
			//read radio value
			$readpoint = $search+strlen("value='");
			$readlen = $end-$readpoint;
			$value = substr($radio, $readpoint, $readlen);
			if ($value === $matchvalue){
				//match found, remove old radio
				//construct old radio to replace
				$old_radio = "value='$value'>";
				$new_radio = "value='$value' checked>";
				return str_replace($old_radio, $new_radio, $radio);

			}
		}
		//update the offset to search multiple
		$offset = $end +1;
	}
	return $radio;
}
function q_type_col($q_type){
	//returns the column that the given question type's response is stored in.
	switch ($q_type){
		case "textinput":
			$storage_column = "response_long";
			break;
		case "numberinput":
			$storage_column = "response_int";
			break;
		case "percentageinput":
			$storage_column = "response_int";
			break;
		case "yesno":
			$storage_column = "response_short";
			break;
		case "yesnounsure":
			$storage_column = "response_short";
			break;
		case "rate10":
			$storage_column = "response_int";
			break;
		case "likert":
			$storage_column = "response_short";
			break;
		default:
			$storage_column = "";//returns "" if invalid q type.
			break;
	}
	return $storage_column; 
}

function format_tel($tel){
	return substr($tel,0,4)." ".substr($tel,4,3)." ".substr($tel,7);
}

function google_datastring($indices, $data){
	//returns the data array string for Google charts
	$data_string = "";
	if (count($indices) == count($data)){
		for ($i=0;$i<count($data);$i++){
			if (is_numeric($data[$i])) $data_string .= "['$indices[$i]', $data[$i]], ";
		}
		return "[$data_string]";
	} else {
		echo "error: ".implode($indices)."   ".implode($data);
	}
	
}

$COUNTRIES = array("UK", "USA", "Germany", "Sweden", "France");
$COUNTRY_LABELS = array("United Kingdom (UK)", "United States of America (USA)", "Germany (DE)", "Sweden (SE)", "France (FR)");


function create_options($options, $labels, $rather_not_say = true){
	if (count($options) == count($labels)){
		if ($rather_not_say) $option = '<option value="">rather not say...</option>';
		else $option = "";
		for ($i = 0; $i < count($labels); $i ++){
			$option .= '<option value="'.$options[$i].'">'.$labels[$i].'</option>';
		}
		return $option;
	}
	return "ERROR: option list invalid.";
}

?>