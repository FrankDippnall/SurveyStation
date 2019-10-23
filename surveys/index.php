<?php

// execute the header script:
require_once "header.php";


if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view surveys.<br>";
}
else
{
    $cansubmit = true;
    $user_completed_survey = false;
    $message = "";
    //connect to db
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    //view specific survey, or view recommended surveys?
    if (isset($_GET['s'])){
        //GET current survey data
        $survey_id = sanitise($_GET['s'], $conn);
        if (!is_numeric($survey_id)){
            echo"Invalid format for survey ID";
            die();
        }
        $result_id = 0; //dummy default value
        $input_val = ""; //error message for user
        //check if user already started survey.
        $sql = "SELECT result_id, if (iscomplete, 'true', 'false')as iscomplete FROM results WHERE survey_id = $survey_id AND username = '{$_SESSION['username']}';";
        $result = mysqli_query($conn, $sql);
        if ($result === FALSE or mysqli_num_rows($result)!=1) $user_started_survey = false;
        else {
            $user_started_survey = true;
            $user_result = mysqli_fetch_assoc($result);
            if ($user_result['iscomplete'] === 'true'){
                //user completed survey. disable submission
                $cansubmit = false;
                $user_completed_survey = true;
            }
            $result_id = $user_result['result_id']; //grab result id for later updates.
        }
        
        //get question data
        if (isset($_GET['q'])) $q_num = $_GET['q'];
        else $q_num = 0;
        $nextq_num = $q_num+1;
        $prevq_num = $q_num-1;

        $user_answered_this = false;
        //check if response already exists
        if ($user_started_survey){
            $sql = "SELECT result_id, response_int, response_short, response_long FROM responses WHERE result_id = $result_id AND question_num = $q_num;";
            $result = mysqli_query($conn, $sql);
            if ($result === FALSE) echo mysqli_error($conn);
            elseif (mysqli_num_rows($result)==1){
                $user_answered_this = true;
                $user_response_array = mysqli_fetch_assoc($result);
                $result_id = $user_response_array['result_id'];
            }
        }

        if (isset($_POST['submit_resp']) and !$user_completed_survey){
            $username = $_SESSION['username'];
            //user just finished the survey
            $continue = true;
            if ($result_id == 0){
                //user not submitted any responses... create result
                $sql = "INSERT INTO results(survey_id, username, iscomplete) VALUES ($survey_id, '$username', false)";
                $result = mysqli_query($conn, $sql);
                if ($result === FALSE){
                    echo "error occured while creating new result.";
                    $continue = false;
                }
                else $result_id = mysqli_insert_id($conn);
            }
            if ($continue){
                //check if all required questions have been answered
                $sql = "SELECT question_num FROM questions q WHERE survey_id = $survey_id AND isrequired = true AND (SELECT COUNT(*) FROM responses WHERE result_id = $result_id AND question_num = q.question_num) = 0;";
                $result = mysqli_query($conn, $sql);
                if ($result === FALSE){
                    echo mysqli_error($conn);
                }
                else if (mysqli_num_rows($result) > 0){
                    //USER CANNOT SUBMIT - must complete required questions.
                    //take first needed q
                    $q_num_needed = mysqli_fetch_assoc($result)['question_num'];
                    //attempt js redirect
                    echo "<script> window.location.assign('?s=$survey_id&q=$q_num_needed&q_required=true'); </script>";
                    $message = "Question $q_num_needed is required.";
                }
                else {
                    $sql = "UPDATE results SET iscomplete = true WHERE survey_id = $survey_id and username='{$_SESSION['username']}';";
                    $result = mysqli_query($conn, $sql);
                    if ($result === FALSE){
                        $message = "Error encountered while finishing survey, please try again.";
                    } else {
                        //attempt js redirect
                        echo "<script> window.location.assign('?s=$survey_id&submitted=true'); </script>";
                        $message = "Response submitted.";
                    }
                }
            }
        }
        else if (isset($_POST['resp']) and $cansubmit){
            //user just submitted a response.
            if (isset($_POST['response']) and $_POST['response'] !== ''){
                $response = strtoupper(sanitise($_POST['response'], $conn));
                //get q type
                $sql = "SELECT question_type FROM questions WHERE survey_id = $survey_id AND question_num = $q_num;";
                $result = mysqli_query($conn, $sql);
                $continue = true;
                if ($result === FALSE or mysqli_num_rows($result)!=1){
                    $message = "An error occurred.";
                    $continue = false;
                }
                if ($continue){
                    $q_type = mysqli_fetch_assoc($result)['question_type'];
                    $storage_column = q_type_col($q_type); //column to store user data in. dependant on question type.
                    if ($storage_column === ""){
                        //q type invalid
                        $message = "Question type invalid. Contact database administrator.";
                    } else {
                        
                        $username = $_SESSION['username'];
                        if ($user_answered_this){
                            //user already responded. update response.
                            $sql = "UPDATE responses SET $storage_column = '$response' WHERE result_id = $result_id AND question_num = $q_num;";
                            $result = mysqli_query($conn, $sql);
                            if ($result === FALSE){
                                $message = "error occurred while updating response.";
                            } else {
                                //attempt js redirect
			                    echo "<script> window.location.assign('?s=$survey_id&q=$nextq_num&responded=true'); </script>";
                                $message = "response updated!";
                            }
                        }
                        else{
                            //user not responded. create response.
                            $continue = true;
                            if (! $user_started_survey){
                                //user not started survey. create result.
                                $sql = "INSERT INTO results(survey_id, username, iscomplete) VALUES ($survey_id, '$username', false)";
                                $result = mysqli_query($conn, $sql);
                                if ($result === FALSE){
                                    $message = "error occured while creating new result.";
                                    $continue = false;
                                }
                                else $result_id = mysqli_insert_id($conn);

                            }
                            if ($continue and $result_id != 0){
                                //create response.
                                $sql = "INSERT INTO responses(result_id, question_num, $storage_column) VALUES ($result_id, $q_num, '$response')";
                                $result = mysqli_query($conn, $sql);
                                if ($result === FALSE){
                                    $message = "error occured while creating new response.";
                                } 
                                else {
                                    //attempt js redirect
                                    echo "<script> window.location.assign('?s=$survey_id&q=$nextq_num&responded=true'); </script>";
                                    $message = "response submitted!";
                                }
                            }
                        }
                    }
                }
            } 
            else {
                $input_val = "Please answer the question.";
            }
        }

        //get survey details
        $sql = 'SELECT author, survey_name, instruction, COUNT(question_num) AS "num_questions", ispublic FROM surveys INNER JOIN questions USING (survey_id) GROUP BY survey_id HAVING survey_id = '.$survey_id.';';
        $result = mysqli_query($conn, $sql);
        if ($result === FALSE){
            $message = "Error retrieving survey data. Please try again.";
        }
        else {
            $continue = true;
            if (mysqli_num_rows($result)==0){
                //check for edge case (no questions)
                $sql = 'SELECT author, survey_name, instruction, 0 AS "num_questions", ispublic FROM surveys WHERE survey_id = '.$survey_id.';';
                $result = mysqli_query($conn, $sql);
                if ($result === FALSE or mysqli_num_rows($result) == 0){
                    echo "Survey ID ($survey_id) does not exist.";
                    $continue = false;
                } 
            }
            if ($continue){
                $survey = mysqli_fetch_assoc($result);
                $author = $survey["author"];
                $survey_name = $survey["survey_name"];
                $survey_instr = $survey["instruction"];
                $num_questions = $survey["num_questions"];
                $endq_num = $num_questions+1;
                $ispublic = ($survey['ispublic']==1) ? true : false;
                if ($num_questions==1) $qplural = "";
                else $qplural = "s";
                
                if ($ispublic === TRUE){
                    //construct q nums
                    $qnums = "";
                    //check if current user is author:
                    if ($_SESSION['username'] === 'author') $surveyprefix = "your";
                    else $surveyprefix = user_string($author)."'s";
                    //check if user in view mode or submit mode
                    if ($cansubmit and !$user_completed_survey){
                        //user can submit, show submit mode qnums
                        for ($q=1;$q<=$num_questions;$q++){
                            if ($q_num < $q) $qclass = 'qnext'; //q not completed
                            elseif ($q_num == $q) $qclass = 'qcurr'; // q current q
                            else $qclass = 'qprev';//q complete
                            $qnums .= "<a class='qnum $qclass'>$q</a>";
                        }
                        //qpoint classes
                        $qstart_class = "qprev";
                        if ($q_num == 0) $qstart_class = "qcurr";
                        $qend_class = "qnext";
                        if ($q_num > $num_questions) $qend_class = "qcurr";
                        //user message empty
                        $user_completed_msg = "";
                        //qpoint hrefs
                        $qstarthref = "";
                        $qendhref = "";
                    } else {
                        //user cannot submit, show view mode qnums
                        for ($q=1;$q<=$num_questions;$q++){
                            $qclass = 'qedit'; 
                            if ($q_num == $q) $qclass = 'qedit_curr';
                            $qnums .= "<a class='qnum $qclass' href='?s=$survey_id&q=$q'>$q</a>";
                        }
                        //qpoint classes
                        $qstart_class = "qedit";
                        if ($q_num == 0) $qstart_class = "qedit_curr";
                        $qend_class = "qedit";
                        if ($q_num == $endq_num) $qend_class = "qedit_curr";
                        //user message
                        $user_completed_msg = "<span class='minor'>you have already completed this survey, and cannot edit your results.</span>";
                        //qpoint hrefs
                        $qstarthref = "href='?s=$survey_id'";
                        $qendhref = "href='?s=$survey_id&q=$endq_num'";
                    }
                    
                    //
                    //display author details on first page.
                    $subtitle = "";
                    if ($q_num == 0) $subtitle = "<h3>$surveyprefix survey - $num_questions question$qplural</h3>";
                    echo <<<_END
<h2>$survey_name</h2>$subtitle
<div id="q_select">
    <a class='$qstart_class qpoint' $qstarthref><span>start</span></a>$qnums<a class='$qend_class qpoint' $qendhref><span>end</span></a>
</div>
$user_completed_msg
_END;
                }
            }
            echo "<br>";
            if ($ispublic){
                //display current part info.
                if ($q_num == 0){
                    if (strlen($survey_instr) == 0) $survey_instr =  "This survey has no instructions.";
                    //display start info
                    echo <<<_END
                    <h3>Instruction</h3>
                    $survey_instr
_END;
                }
                else if ($q_num > 0 and $q_num <= $num_questions){
                    
                    //display question info
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
                        $q_required = $question['isrequired'];
                        $user_input = "";
                        if ($user_answered_this){
                            //get user response
                            $storage_column = q_type_col($q_type);
                                //nothing default
                            if ($storage_column === ""){
                                $message = "question type invalid. contact database administrator.";
                            } else {
                                //get user response to this question.
                                $user_input = strtolower($user_response_array[$storage_column]);
                            }

                        }
                        //disable question inputs if cannot submit
                        if ($cansubmit) {
                            $input_enable = "";
                            $rate10_display = "";
                        }
                        else {
                            $input_enable = "disabled";
                            $rate10_display = " style='display:none'";
                        }
                        if ($q_required) $required_string = "<span style='color:red;font-style:italic;'>Required</span>";
                        else $required_string = "";
                            
                        //echo data
                        echo "<h3>Question $q_num</h3><h4>$required_string</h4>$q_text<br>";
                        $input_field = "";
                        if ($user_completed_survey){
                            if ($user_answered_this and $user_input != -1)echo "<br><h4>YOU ANSWERED:</h4>";
                            else echo "<br><h4>you didn't answer this part.</h4>";
                        }
                        if ($user_input != -1){
                            switch ($q_type){
                                case "textinput":
                                    $input_field = "<textarea class='textinput' $input_enable name='response'>$user_input</textarea>";
                                    break;
                                case "numberinput":
                                    $input_field = "<input class='numberinput' $input_enable type='number' name='response' value='$user_input'>";
                                    break;
                                case "percentageinput":
                                    $input_field = "<span class='percentage_span'><input class='percentageinput' $input_enable max='100' min='0' type='number' name='response' value='$user_input'>%</span>";
                                    break;
                                case "yesno":
                                    $input_field = setDefaultRadio(<<<_END
        <label class='radio'>Yes<input class='radioinput' $input_enable type='radio' name='response' value='yes'><span class='checkbox'></span></label>
        <label class='radio'>No<input class='radioinput' $input_enable type='radio' name='response' value='no'><span class="checkbox"></span></label>
_END
        , $user_input); //set radio default to user input
                                    break;
                                case "yesnounsure":
                                    $input_field = setDefaultRadio(<<<_END
        <label class='radio'>Yes<input class='radioinput' $input_enable type='radio' name='response' value='yes'><span class='checkbox'></span></label>
        <label class='radio'>No<input class='radioinput' $input_enable type='radio' name='response' value='no'><span class="checkbox"></span></label>
        <label class='radio'>Unsure<input class='radioinput' $input_enable type='radio' name='response' value='unsure'><span class="checkbox"></span></label>
_END
        , $user_input); //set radio default to user input

                                    break;
                                case "rate10":
                                    if ($user_input === '')$user_input = 5; //default value is 5/10
                                    $input_field = "<label class='rate10'><input class='rate10input' $input_enable $rate10_display type='range' name='response' max='10' min='0' step='1' value='$user_input'><br><span class='readout'>$user_input</span>/ 10</label>";
                                    break;
                                case "likert":
                                    $input_field = setDefaultRadio(<<<_END
        <label class='radio'>Strongly Agree<input class='radioinput' $input_enable type='radio' name='response' value='Strongly Disagree'><span class="checkbox"></span></label></label>
        <label class='radio'>Agree<input class='radioinput' $input_enable type='radio' name='response' value='Disagree'><span class="checkbox"></span></label></label>
        <label class='radio'>Neither<input class='radioinput' $input_enable type='radio' name='response' value='Neutral'><span class="checkbox"></span></label></label>
        <label class='radio'>Disagree<input class='radioinput' $input_enable type='radio' name='response' value='Agree'><span class="checkbox"></span></label></label>
        <label class='radio'>Strongly Disagree<input class='radioinput' $input_enable type='radio' name='response' value='Strongly Agree'><span class="checkbox"></span></label></label>
_END
        , $user_input, true); //set radio default to user input (UCWORDS set to true for Likert)
                                    break;
                                default:
                                    $message = "question type invalid. contact database administrator.";
                                    break;
                            }
                            echo "<form id='current_q' action='?s=$survey_id&q=$q_num' method='post'>$input_field</form><span class='qerror'>$input_val</span>";
                        }
                    }
                }
                else{
                    //display end info
                    if ($cansubmit) echo "<h3>Nice Job!</h3>Submit this survey by clicking 'finish' below.<br><i>Once you do this you will no longer be able to change your responses.</i>";
                    else echo "<h3>End of Survey</h3>This is the end of this survey. Click 'exit' to return to the survey list.";
                }
                echo "<br><div id='surveyfoot'>";
                //previous q anchor
                if ($q_num == 0) $prevq_btn = "<a id='prevq' class='qchange'></a>";
                else if ($q_num == 1) $prevq_btn = "<a id='prevq' class='qchange' href='?s=$survey_id'></a>";
                else $prevq_btn = "<a id='prevq' class='qchange' href='?s=$survey_id&q=$prevq_num'></a>";
                //next button
                if ($cansubmit){
                    if ($q_num == $endq_num) $next_btn = "<form class='inline' action='?s=$survey_id' method='post'><input id='next' class='next' type='submit'name='submit_resp' value='finish'></form>";
                    else if ($q_num == 0) $next_btn = "<a id='next' class='next' href='?s=$survey_id&q=$nextq_num'>next</a>";
                    else $next_btn = "<div class='inline'><input form='current_q' id='next' class='next' type='submit' name='resp' value='save'></div>";
                } else{
                    if ($q_num == $endq_num) $next_btn = "<a id='next' class='next' href='../public_surveys.php'>exit</a>";
                    else $next_btn = "<a id='next' class='next' href='?s=$survey_id&q=$nextq_num'>next</a>";
                } 
                //next q anchor
                if ($q_num == $endq_num) $nextq_btn = "<a id='nextq' class='qchange'></a>";
                else $nextq_btn = "<a id='nextq' class='qchange' href='?s=$survey_id&q=$nextq_num'></a>";
                //output menu
                echo $prevq_btn.$next_btn.$nextq_btn;
                echo "</div>";
                echo "$message";
            } else echo "This survey is currently private.";
        }
    }
    else {
        echo "<br>For individual surveys use the ?s=[survey id] suffix.";
    }
}

// finish off the HTML for this page:
require_once "footer.php";

?>