<?php

//this page show the user all of the surveys set to PUBLIC in the database.
// execute the header script:
require_once "header.php";


if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
} else {
$message = "";
//display surveys?
$display_surveys = true;
//show/hide completed surveys?
if (isset($_GET['hide'])) $hide_completed = true;
else $hide_completed = false;
if ($display_surveys) {
	//display all surveys set to PUBLIC
	//connect to db
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}	
	//if admin show all with author info
	$user_completed_survey_head = "<a class='completed_survey_head surveyhead'/>";
	//hide completed logic
	if ($hide_completed) $hide_completed_logic = " AND (((SELECT iscomplete FROM results WHERE survey_id = surv.survey_id AND username = '{$_SESSION['username']}') = false) OR ((SELECT iscomplete FROM results WHERE survey_id = surv.survey_id AND username = '{$_SESSION['username']}') is null))";
	else $hide_completed_logic = "";
	$sql = <<<_END
SELECT survey_id as "Survey ID", 
	survey_name as "Survey Name", 
	author as "Author", 
	(SELECT COUNT(*) FROM questions WHERE survey_id = surv.survey_id) as "No. Questions", 
	(SELECT COUNT(*) FROM results WHERE survey_id = surv.survey_id AND iscomplete = true) as "Results", 
	(CONCAT('http://localhost/survey_website/surveys/?s=',survey_id)) as "URL",  
	if(((SELECT result_id FROM results res WHERE survey_id = surv.survey_id AND iscomplete=true AND username = '{$_SESSION['username']}') IS NOT NULL),
		'<a title="survey completed" class="completed_survey surveycellbig" ></a>',
		'<a title="survey not completed" class="notcompleted_survey surveycellbig" ></a>'
		
	) as "$user_completed_survey_head"

FROM surveys surv WHERE ispublic = true $hide_completed_logic;
_END;
	$result = mysqli_query($connection, $sql);
	if ($result === FALSE){
		echo "error retrieving surveys: ".mysqli_error($connection);
	}
	else if (mysqli_num_rows($result)>0){
		$user_completed_any = false;
		//find if user has completed any surveys
		for ($i=0;$i<mysqli_num_rows($result);$i++){
			$user_completed_survey = mysqli_fetch_assoc($result)[$user_completed_survey_head];
			if (strpos($user_completed_survey,"completed_survey")){
				//user completed a survey.
				$user_completed_any = true;
				break;
			}
		}
		echo "<h2>Public Surveys</h2>";
		echo create_table($result);
		if ($user_completed_any){
			//display hide/show completed survey button
			if ($hide_completed) echo "<a class='normal' style='margin-top:20px;' href='public_surveys.php'>Show Completed</a>";
			else echo "<a class='normal' style='margin-top:20px;'href='public_surveys.php?hide=true'>Hide Completed</a>";
		}
		
	}
	else{
		if ($hide_completed) {
			echo "No uncompleted surveys found!<br>";
			echo "<a class='normal' style='margin-top:20px;' href='public_surveys.php'>Show Completed</a>";
		}
		else echo "No public surveys found in the database!<br>";
	}
	echo $message;
}

}

// finish off the HTML for this page:
require_once "footer.php";

?>