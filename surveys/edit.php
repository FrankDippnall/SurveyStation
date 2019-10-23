<?php

//this page lets the author of a survey or admin edit the survey.

// execute the header script:
require_once "header.php";


if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view surveys.<br>";
}
else
{
    //connect to db
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    //view specific survey, or view recommended surveys?
    if (isset($_GET['s'])){
        //GET current survey data
        $survey_id = sanitise($_GET['s'], $conn);
        //GET question data
        if (isset($_GET['q'])) $q_num = sanitise($_GET['q'],$conn);
        else $q_num = 0;
        $sql = 'SELECT author, survey_name, instruction, COUNT(question_num) AS "num_questions" FROM surveys INNER JOIN questions USING (survey_id) GROUP BY survey_id HAVING survey_id = '.$survey_id.';';
        $result = mysqli_query($conn, $sql);
        if ($result === FALSE){
            echo "Error retrieving survey data. Please try again.";
        }
        else {
            $continue = true;

            if (mysqli_num_rows($result)==0){
                //check for edge case (no questions)
                $sql = 'SELECT author, survey_name, instruction, 0 AS "num_questions" FROM surveys WHERE survey_id = '.$survey_id.';';
                $result = mysqli_query($conn, $sql);
                if ($result === FALSE or mysqli_num_rows($result) == 0){
                    echo "Survey ID ($survey_id) does not exist.";
                    $continue = false;
                } 
            }
            if ($continue and is_numeric($survey_id) and is_numeric($q_num)){
                $survey = mysqli_fetch_assoc($result);
                $author = $survey["author"];
                $survey_name = $survey["survey_name"];
                $survey_instr = $survey["instruction"];
                $num_questions = $survey["num_questions"];
                if ($num_questions==1) $qplural = "";
                else $qplural = "s";
                $surveyprefix = "Your";
                if ($author !== 'admin' && $_SESSION['username'] === 'admin') $surveyprefix = user_string($author)."'s";
                //check if current user has access:
                if ($_SESSION['username'] === $author or $_SESSION['username'] === 'admin'){
                    //user access granted
                    $force_display_instr = false;
                    $force_display_qdelete = false;
                    $display_survey_data = true;
                    $instr_val = "";
                    $instr = $survey_instr;
                    if (isset($_POST['instruction'])){
                        $display_survey_data = false;
                        //update survey instruction
                        $instr = sanitise($_POST['instruction'], $conn);
                        $instr_val = validateString($instr, 1, 256);
                        if ($instr_val === ''){
                            $sql = "UPDATE surveys SET instruction = '$instr' WHERE survey_id = $survey_id;";
                            $result = mysqli_query($conn, $sql);
                            if ($result === FALSE){
                                echo "request failed.";
                            } else {
                                //update success
                                //attempt js redirect
			                    echo "<script> window.location.assign('edit.php?s=$survey_id&instr_update=true'); </script>";
                                echo "Update instruction success! click <a href='edit.php?s=$survey_id'>here</a> to return to survey.";
                            }
                        } else {
                            $force_display_instr = true;
                        }
                    }
                    if (isset($_GET['instr']) or $force_display_instr){
                        $display_survey_data = false;
                        //edit instructions page
                        echo <<<_END
<h3>Edit Instructions</h3>
<form action='edit.php?s=$survey_id' method='post'>
<a class='normal' id='auto_generate_instr'>Auto Generate</a>
<textarea data-default='Complete the survey by answering the questions. Good Luck!' style='margin:0 auto 30px auto;display:block;' class='textinput' name='instruction'>$instr</textarea>
<p style='display:block;color:red;'>$instr_val</p>
<input type='submit' class='submit' value='Submit'>
</form>
<a class='normal'href='edit.php?s=$survey_id'>Cancel</a>
_END;
                    }
                    else if (isset($_POST['qdelete'])){
                        //delete question request recieved.
                        $display_survey_data = false;
                        //STEP 1: DELETE CHOSEN QUESTION
                        $sql = "DELETE FROM questions WHERE survey_id = $survey_id AND question_num = $q_num;";
                        $result = mysqli_query($conn, $sql);
                        $continue = true;
                        if ($result === FALSE){
                            echo "error encountered while deleting question.";
                            $continue = false;
                        } else {
                            //STEP 2: UPDATE ALL QUESTIONS AFTER
                            //fix gap between q nums in db
                            $sql = "UPDATE questions SET question_num=(question_num-1)  WHERE survey_id = $survey_id AND question_num > $q_num;";
                            $result = mysqli_query($conn, $sql);
                            if ($result === FALSE){
                                echo "CRITICAL!<br> error encountered while deleting question. there may be an inconsistency in the database. Contact administrator. <br>DETAILS: ".mysqli_error($conn);
                                $continue = false;
                            } else {
                                //STEP 3: DELETE RESPONSE
                                //delete responses associated with deleted q
                                $sql = "DELETE FROM responses WHERE result_id IN (SELECT result_id FROM results WHERE survey_id = $survey_id AND question_num = $q_num);";
                                $result = mysqli_query($conn, $sql);
                                if ($result === FALSE){
                                    echo "CRITICAL!<br> error encountered while deleting question. there may be an inconsistency in the database. Contact administrator. <br>DETAILS: ".mysqli_error($conn);
                                    $continue = false;
                                } else {
                                    //STEP 4: UPDATE ALL RESPONSES TO QUESTIONS AFTER
                                    //fix gap in responses table
                                    $sql = "UPDATE responses SET question_num=(question_num-1) WHERE result_id IN (SELECT result_id FROM results WHERE survey_id = $survey_id) AND question_num > $q_num;";
                                    $result = mysqli_query($conn, $sql);
                                    if ($result === FALSE){
                                        echo "CRITICAL!<br> error encountered while deleting question. there may be an inconsistency in the database. Contact administrator. <br>DETAILS: ".mysqli_error($conn);
                                        $continue = false;
                                    } else {
                                        //update success thus operation success!
                                        //attempt js redirect
                                        echo "<script> window.location.assign('edit.php?s=$survey_id&q=$q_num&qdeleted=$q_num'); </script>";
                                        echo "Success! click <a href='edit.php?s=$survey_id&q=$q_num'>here</a> to return to the survey.";
                                    }
                                }




                                
                            }
                        }
                        
                        
                    }
                    if (isset($_GET['qdelete']) or $force_display_qdelete){
                        $display_survey_data = false;
                        //delete question page
                        echo <<<_END
<h3>Are You Sure?</h3>
Click 'delete' to confirm the deletion of Question $q_num.
<form action='edit.php?s=$survey_id&q=$q_num' method='post'>
<input type='submit' class='harsh'name='qdelete' value='Delete'>
</form>
<a class='normal'href='edit.php?s=$survey_id&q=$q_num'>Cancel</a>
_END;
                    }
                    if($display_survey_data){
                        $endq_num = $num_questions+1;
                        //construct q nums - can view all questions
                        $qnums = "";
                        for ($q=1;$q<=$num_questions;$q++){
                            if ($q_num == $q) $qclass = 'qedit_curr';
                            else $qclass = 'qedit';
                            $qnums .= "<a class='qnum $qclass' href='edit.php?s=$survey_id&q=$q'>$q</a>";
                        }
                        //qpoint classes
                        $qstart_class = "qedit";
                        if ($q_num == 0) $qstart_class = "qedit_curr";
                        $qend_class = "qedit";
                        if ($q_num == $endq_num) $qend_class = "qedit_curr";
                        //qpoint hrefs
                        $qstarthref = "href='?s=$survey_id'";
                        $qendhref = "href='?s=$survey_id&q=$endq_num'";
                        echo <<<_END
<h2>$survey_name</h2><h3>$surveyprefix Survey - $num_questions question$qplural</h3>
<div id="q_select">
<a class='$qstart_class qpoint' $qstarthref><span>start</span></a>$qnums<a class='$qend_class qpoint' $qendhref><span>end</span></a>
</div>
_END;
                        //display current part info.
                        if ($q_num == 0){
                            //display start info
                            if (strlen($survey_instr) == 0) echo "This survey has no instructions.";
                            else echo <<<_END
                            <h3>Instruction</h3>
                            $survey_instr
_END;
                        }
                        else if ($q_num > 0 and $q_num <= $num_questions){
                            //display question info (edit mode)
                            $sql = "SELECT question_text, question_type, isrequired FROM questions WHERE survey_id = $survey_id AND question_num = $q_num;";
                            $result = mysqli_query($conn, $sql);
                            if ($result === FALSE)
                                $message = "An error was encountered while reading question data.";
                            else if (mysqli_num_rows($result) == 0)
                                $message = "That question doesn't exist";
                            else {
                                $question = mysqli_fetch_assoc($result);
                                //grab q data
                                $q_text = $question['question_text'];
                                $q_type = $question['question_type'];
                                if ($question['isrequired'] == 1) $q_required = true;
                                else $q_required = false;
                                if ($q_required) $required_string = "<span style='color:red;font-style:italic;'>Required</span>";
                                else $required_string = "";
                            
                                //echo data
                                echo "<h3>Question $q_num</h3><h4>$required_string</h4>$q_text<br>";
                                $input_field = ""; //stores the complete input/textarea tag
                                switch ($q_type){
                                    case "textinput":
                                        $input_field = "<textarea class='textinput' name='response'></textarea>";
                                        break;
                                    case "numberinput":
                                        $input_field = "<input class='numberinput' type='number' name='response' value=''>";
                                        break;
                                    case "percentageinput":
                                        $input_field = "<span class='percentage_span'><input class='percentageinput' max='100' min='0' type='number' name='response' value=''>%</span>";
                                        break;
                                    case "yesno":
                                        $input_field = setDefaultRadio(<<<_END
            <label class='radio'>Yes<input class='radioinput' type='radio' name='response' value='yes'><span class='checkbox'></span></label>
            <label class='radio'>No<input class='radioinput' type='radio' name='response' value='no'><span class="checkbox"></span></label>
_END
            , ''); //set radio default to none
                                        break;
                                    case "yesnounsure":
                                        $input_field = setDefaultRadio(<<<_END
            <label class='radio'>Yes<input class='radioinput' type='radio' name='response' value='yes'><span class='checkbox'></span></label>
            <label class='radio'>No<input class='radioinput' type='radio' name='response' value='no'><span class="checkbox"></span></label>
            <label class='radio'>Unsure<input class='radioinput' type='radio' name='response' value='unsure'><span class="checkbox"></span></label>
_END
            , ''); //set radio default to none

                                        break;
                                    case "rate10":
                                        $input_field = "<label class='rate10'><input class='rate10input' type='range' name='response' max='10' min='0' step='1' value='5'><br><span class='readout'>5</span>/ 10</label>";
                                        break;
                                    case "likert":
                                        $input_field = setDefaultRadio(<<<_END
            <label class='radio'>Strongly Agree<input class='radioinput' type='radio' name='response' value='Strongly Disagree'><span class="checkbox"></span></label></label>
            <label class='radio'>Agree<input class='radioinput' type='radio' name='response' value='Disagree'><span class="checkbox"></span></label></label>
            <label class='radio'>Neither<input class='radioinput' type='radio' name='response' value='Neutral'><span class="checkbox"></span></label></label>
            <label class='radio'>Disagree<input class='radioinput' type='radio' name='response' value='Agree'><span class="checkbox"></span></label></label>
            <label class='radio'>Strongly Disagree<input class='radioinput' type='radio' name='response' value='Strongly Agree'><span class="checkbox"></span></label></label>
_END
            , "", true); //set radio default to user input (UCWORDS set to true for Likert)
                                        break;
                                    default:
                                        $message = "question type invalid. contact database administrator.";
                                        break;
                                }
                                echo "<form id='current_q' action='?s=$survey_id&q=$q_num' method='post'>$input_field</form><span class='qerror'></span>";
                            }
                        }
                        else{
                            //display end info
                            echo "<h3>End of Survey</h3>This is the end of the survey.";
                        }
                        $nextq_num = $q_num+1;
                        $prevq_num = $q_num-1;
                        //previous q anchor
                        if ($q_num == 0) $prevq_btn = "<a id='prevq' class='qchange'></a>";
                        else if ($q_num == 1) $prevq_btn = "<a id='prevq' class='qchange' href='?s=$survey_id'></a>";
                        else $prevq_btn = "<a id='prevq' class='qchange' href='?s=$survey_id&q=$prevq_num'></a>";
                        //next q anchor
                        if ($q_num == $endq_num) $nextq_btn = "<a id='nextq' class='qchange'></a>";
                        else $nextq_btn = "<a id='nextq' class='qchange' href='?s=$survey_id&q=$nextq_num'></a>";
                        if ($q_num == 0) $additional_btns = <<<_END
<a class='next' style='font-size:20px;min-width:200px;' href='edit.php?s=$survey_id&instr=true'>Edit Instructions</a>
_END;
                        else if ($q_num == $endq_num) $additional_btns = <<<_END
<a class='next' style='font-size:20px;min-width:200px;' href='../surveys_manage.php'>Exit</a>
_END;
                        else $additional_btns = <<<_END
<a class='next' style='font-size:20px;min-width:200px;' href='question.php?s=$survey_id&q=$q_num'>Edit Question</a>
<a class='next' style='font-size:20px;min-width:200px;' href='edit.php?s=$survey_id&q=$q_num&qdelete=true'>Delete Question</a>
_END;
                        echo <<<_END
<div id='surveyfoot'>
$prevq_btn
<a class='next' style='font-size:20px;min-width:200px;' href='question.php?s=$survey_id&addq=true'>Add Question</a>
$additional_btns


$nextq_btn
</div>
_END;
                    }
                } 
                else {
                    echo "You do not have permission to access this page.";
                }
            } else {
                echo "error, possible invalid GET.";
            }
            
        }
    }
    else {
        //no get request. rip.
        echo "Whoops! That isn't right. <br>To edit surveys use the ?s=[survey id] suffix.";
    }
}

// finish off the HTML for this page:
require_once "footer.php";

?>