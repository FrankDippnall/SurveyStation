<?php


//this page lets a user create a new question for the currently active survey, and edit an available question.

// execute the header script:
require_once "header.php";


if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
    echo "You must be logged in to edit surveys.<br>";
}
else {
    //default values
    $q_text = "";
    $q_text_val = "";
    $q_type = "";
    $q_type_val = "";
    $message = "";
    $q_num = "";
    $request_type = "";
    $additional_info = "";
    $q_required = false;
    //display form?
    $show_form = true;
    $survey_id = 0;
    //force info renewal?
    $force_renew = true;
    if (isset($_GET['s'])){
        //connect
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        $survey_id = sanitise($_GET['s'],$conn);
        //survey id found, check for authorization.
        $username = $_SESSION['username'];
        $continue = false;
        if ($_SESSION['username']==='admin') $continue=true;
        else {
            $sql = "SELECT survey_id FROM surveys WHERE survey_id = $survey_id AND author = '$username';";
            $result = mysqli_query($conn, $sql);
            if ($result !== FALSE and mysqli_num_rows($result)>0){
                $continue = true;
            }
        }
        if ($continue){
            if(isset($_POST['question_text'])){
                //form submitted
                $q_text = sanitise($_POST['question_text'], $conn);
                $q_type = sanitise($_POST['question_type'], $conn);
                $q_required = isset($_POST['question_required']) ? "true" : "false";
                $q_text_val = validateString($q_text, 1, 128);
                $q_type_val = validateQType($q_type);
                if ($q_text_val.$q_type_val === ""){
                    if (isset($_POST['question_num'])){
                        //edit question request
                        $request_type = "Edit";
                        $q_num = sanitise($_POST['question_num'], $conn);
                        if (is_numeric($q_num)){
                            //update question
                            $sql = "UPDATE questions SET question_text = '$q_text', question_type = '$q_type', isrequired = $q_required WHERE survey_id = $survey_id AND question_num = $q_num;";
                            $result = mysqli_query($conn, $sql);
                            if ($result === FALSE){
                                $message = "Error occured while updating question: <br>".mysqli_error($conn);
                            } else {
                                //create question success
                                //attempt js redirect
			                    echo "<script> window.location.assign('edit.php?s=$survey_id&q=$q_num&q_edit=true'); </script>";
                                $show_form = false;
                                echo "Success! click <a href='edit.php?s=$survey_id&q=$q_num'>here</a> to view the question.";
                            }
                        } else echo "ERROR: invalid q_num in POST.";
                    }
                    else {
                        //create question request
                        $request_type = "Create";
                        //get new q num
                        $sql = 'SELECT COUNT(*) as "num_questions" FROM questions WHERE survey_id = '.$survey_id.';';
                        $result = mysqli_query($conn, $sql);
                        if ($result === FALSE){
                            echo "Error encountered while looking for question data: <br>".mysqli_error($conn);
                        } else {
                            $newq_num = mysqli_fetch_assoc($result)['num_questions'] + 1; //number of q plus one.
                            $sql = "INSERT INTO questions(survey_id, question_num, question_text, question_type, isrequired) VALUES ($survey_id, $newq_num, '$q_text', '$q_type',$q_required)";
                            $result = mysqli_query($conn, $sql);
                            if ($result === FALSE){
                                $message = "Error occured while adding question: <br>".mysqli_error($conn);
                            } else {
                                //create question success
                                //attempt js redirect
			                    echo "<script> window.location.assign('edit.php?s=$survey_id&q=$newq_num&q_create=true'); </script>";
                                $show_form = false;
                                echo "Success! click <a href='edit.php?s=$survey_id&q=$newq_num'>here</a> to view the question.";
                            }
                        }
                    }
                } else {
                    $show_form = true;
                    $force_renew = false;
                    //fix header
                    if (isset($_POST['question_num'])) {
                        $request_type = "Edit";
                        $q_num = sanitise($_POST['question_num'], $conn);
                    }
                    else $request_type = "Create";
                }
                
            }
            if (isset($_GET['addq'])){
                //add question request
                $request_type = "Create";
                $additional_info = "";
            }
            else if (isset($_GET['q'])){
                $q_num = sanitise($_GET['q'], $conn);
                //edit question request
                $request_type = "Edit";
                $additional_info = "<input type='hidden' name='question_num' value='$q_num'>";
                //get question info
                if ($force_renew){
                    if (is_numeric($q_num)){
                        $sql = "SELECT question_text, question_type, isrequired FROM questions WHERE survey_id = $survey_id AND question_num = $q_num;";
                        $result = mysqli_query($conn,$sql);
                        if ($result === FALSE or mysqli_num_rows($result) == 0){
                            $message = "error encountered while getting question info.";
                        }
                        else {
                            $question = mysqli_fetch_assoc($result);
                            $q_text = $question['question_text'];
                            $q_type = $question['question_type'];
                            $q_required = $question['isrequired'];
                        }
                    } else $message = "invalid q_num in GET.";
                }
            }
            //TODO this
        } else {
            echo "You do not have authorisation to edit this survey, or it doesn't exist.";
            $show_form = false;
        }
        //close connection
        mysqli_close($conn);
    }
    else {
        echo "error occurred (GET not recieved)";
        $show_form = false;
    }
    if ($show_form){
        $questiontype_select = <<<_END
<select name="question_type">
    <option value="">please select...</option>	
    <option value="textinput">Text Input</option>
    <option value="numberinput">Number Input</option>
    <option value="percentageinput">Percentage Input</option>
    <option value="yesno">Yes/No</option>
    <option value="yesnounsure">Yes/No/Unsure</option>
    <option value="rate10">Rate Out Of 10</option>
    <option value="likert">Likert Scale</option>
</select>
_END;
    $questiontype_select = setDefaultSelect($questiontype_select, $q_type);
    if ($request_type === "Create") $cancelhref = "edit.php?s=$survey_id";
    else $cancelhref = "edit.php?s=$survey_id&q=$q_num";
    if ($q_required) $q_req_selected = "checked";
    else $q_req_selected = "";

    echo <<<_END
<h2>$request_type Question $q_num</h2>
<form action="" method="post" class="large">
<div class="fields">
<p>
<label>Question Text </label><input name="question_text" max="128" min="1" value="$q_text" required><span class="error">$q_text_val</span>
<br>
</p>
<p>
<label>Question Type </label>$questiontype_select<span class="error">$q_type_val</span>
<br>
</p>
<p>
<label>Required? </label><label class='check_box' id='required_checkbox'><input class='check' type='checkbox' name='question_required' $q_req_selected><span class='checkbox'></span></label>
<br>
</p>


</div>
<br>
<input class="submit" type="submit" value="Submit & Edit">
<input type="hidden" name="survey_id" value="$survey_id">
$additional_info
<a class='large normal' href='$cancelhref'>Cancel</a>
</form>	

_END;
    }
echo $message;
}

// finish off the HTML for this page:
require_once "footer.php";

?>