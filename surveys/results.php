<?php

//this file lets a user download a CSV (comma seperated values) file 
//containing the results/responses to a survey as requested

$success = false; //is the csv request a success?

session_start();
include_once "../credentials.php";
include_once "../helper.php";

if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
    echo "You must be logged in to view surveys.<br>";
}
else
{
    //connect to db
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if (isset($_GET['s'])){
        //GET current survey data
        $survey_id = sanitise($_GET['s'], $conn);
        //GET question data
        if (isset($_GET['q'])) $q_num = sanitise($_GET['q'],$conn);
        else $q_num = 0;
        if (is_numeric($survey_id) and is_numeric($q_num)){
            $sql = 'SELECT author, survey_name, instruction, COUNT(question_num) AS "num_questions", (SELECT COUNT(*) FROM results WHERE survey_id = '.$survey_id.' AND iscomplete = true) as "num_results" FROM surveys INNER JOIN questions USING (survey_id) GROUP BY survey_id HAVING survey_id = '.$survey_id.';';
            $result = mysqli_query($conn, $sql);
            if ($result === FALSE){
                echo "Error retrieving survey data. Please try again.";
                echo mysqli_error($conn);
            }
            else {
                $continue = true;
                if (mysqli_num_rows($result)==0){
                    //check for edge case (no questions)
                    $sql = 'SELECT author, survey_name (SELECT COUNT(*) FROM results WHERE survey_id = '.$survey_id.' AND iscomplete = true) as "num_results" FROM surveys WHERE survey_id = '.$survey_id.';';
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
                    $num_results = $survey["num_results"];
                    //check if current user has access:
                    if ($_SESSION['username'] === $author or $_SESSION['username'] === 'admin'){
                        //user access granted
                        if ($num_results > 0) {
                            if ($q_num >= 0 and $q_num <= $num_questions) {
                                $continue = true;
                                //results found
                                if ($q_num == 0){
                                    //send RESULT data
                                    $sql = 'SELECT username, email, first_name, last_name, dob, phone_num, country, gender, FLOOR(DATEDIFF(NOW(), dob)/365.25) as "age" FROM users INNER JOIN results USING (username) WHERE survey_id = '.$survey_id.' and iscomplete = true;';
                                    $filename = "survey_".$survey_id."[$author].csv";
                                } 
                                else {
                                    
                                    //get question type
                                    $sql = "SELECT question_type FROM questions WHERE survey_id = $survey_id AND question_num = $q_num;";
                                    $result = mysqli_query($conn, $sql);
                                    if ($result === FALSE or mysqli_num_rows($result) == 0){
                                        echo "question not found in database.";
                                        $continue = false;
                                    } else {
                                        $q_type = mysqli_fetch_assoc($result)['question_type'];
                                        $q_type_col = q_type_col($q_type);
                                        //send RESPONSE data
                                        $sql = "SELECT username, $q_type_col as \"response\" FROM responses INNER JOIN results USING (result_id) WHERE survey_id = $survey_id AND question_num = $q_num";
                                        $filename = "survey_".$survey_id."[$author](Q$q_num).csv";
                                    }
                                }
                                if ($continue){
                                    //perform query
                                    $result = mysqli_query($conn, $sql);
                                    if ($result === FALSE){
                                        echo "ERROR: ".mysqli_error($conn);
                                    }
                                    else if (mysqli_num_rows($result) == 0){
                                        echo "No responses found.";
                                    }
                                    else {
                                        //construct csv data.
                                        $csv_data = array();
                                        for($i = 0; $i < mysqli_num_rows($result); $i ++){
                                            $csv_data[] = mysqli_fetch_assoc($result);
                                        }
                                        $success = true;
                                    }
                                }
                            } else {
                                echo "bad GET: question does not exist.";
                            }
                        } else {
                            echo "No results found.";
                        }
                    }
                }
            }
        }
        else {
            echo "invalid GET.";
        }
        if($success){
            //use file download header
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="'.$filename.'"');   
            // do not cache the file
            header('Pragma: no-cache');
            header('Expires: 0'); 
            //open output for writing
            $csv_file = fopen('php://output', 'w');
            //get the heading values
            $first_row = $csv_data[0];
            $headings = array_keys($first_row);
            //write headings
            fputcsv($csv_file, $headings);
            //write rows
            for ($i = 0; $i < count($csv_data); $i ++){
                fputcsv($csv_file, $csv_data[$i]);
            }

            exit(); //get out of here


        }
    }
    else {
        //no get request. rip.
        echo "bad GET";
    }
}


?>