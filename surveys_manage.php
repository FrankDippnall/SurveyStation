<?php

// Things to notice:
// This is the page where each user can MANAGE their surveys
// As a suggestion, you may wish to consider using this page to LIST the surveys they have created
// Listing the available surveys for each user will probably involve accessing the contents of another TABLE in your database
// Give users options such as to CREATE a new survey, EDIT a survey, ANALYSE a survey, or DELETE a survey, might be a nice idea
// You will probably want to make some additional PHP scripts that let your users CREATE and EDIT surveys and the questions they contain
// REMEMBER: Your admin will want a slightly different view of this page so they can MANAGE all of the users' surveys

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
if (isset($_POST['delete_s'])){
	//delete survey confirmed request recieved, delete survey
	$survey_id = $_POST['delete_s'];//survey to delete.
	$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	$sql = "DELETE FROM surveys WHERE survey_id = $survey_id;";
	$result = mysqli_query($conn, $sql);
	if ($result === FALSE){
		echo "Error encountered while deleting survey.";
	}
	mysqli_close($conn);
}
else if (isset($_GET['delete_s'])){
	$display_surveys = false;
	//delete survey tentative request recieved.
	$survey_id = $_GET['delete_s'];
	//check that survey exists
	$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	if ($_SESSION['username']==='admin'){
		$sql = "SELECT survey_name FROM surveys WHERE survey_id = $survey_id;";
	} else {
		$sql = "SELECT survey_name FROM surveys WHERE survey_id = $survey_id and author = '" .$_SESSION['username']."';";
	}
	$result = mysqli_query($conn, $sql);
	if ($result === FALSE or mysqli_num_rows($result) == 0){
		if ($_SESSION['username']==='admin') echo "Survey ID ($survey_id) doesn't exist.";
		else echo "Survey ID ($survey_id) doesn't exist or you are not the author.";
	}
	else {
		$survey = mysqli_fetch_assoc($result);
		$surveyname = $survey['survey_name'];
		echo <<<_END
<h3>Are you sure you want to delete survey ID $survey_id ($surveyname)?</h3>
<h4 style="font-style:italic;">This will also delete any responses to the survey.</h4>
<form action="surveys_manage.php?deleted=true" method="post">
<input hidden name="delete_s" value="$survey_id"> 
<input class="harsh" type="submit" value="Delete">
</form>
<a class="normal" href="surveys_manage.php">Cancel</a>
_END;
	}
	mysqli_close($conn);
}
else if (isset($_GET['public_s']) or isset($_GET['private_s'])){
	//visibility change request recieved.
	$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	if (isset($_GET['public_s'])){
		$survey_id = sanitise($_GET['public_s'],$conn);
		$new_ispublic = "true";
	}
	else{
		 $survey_id = sanitise($_GET['private_s'],$conn);
		 $new_ispublic = "false";
	}
	if (!is_numeric($survey_id)){
		$message="invalid survey ID";
	}
	else {
		//check that survey exists
		$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
		if ($_SESSION['username']==='admin'){
			$sql = "SELECT survey_name FROM surveys WHERE survey_id = $survey_id;";
		} else {
			$sql = "SELECT survey_name FROM surveys WHERE survey_id = $survey_id and author = '" .$_SESSION['username']."';";
		}
		$result = mysqli_query($conn, $sql);
		if ($result === FALSE or mysqli_num_rows($result) == 0){
			if ($_SESSION['username']==='admin') echo "Survey ID ($survey_id) doesn't exist.";
			else echo "Survey ID ($survey_id) doesn't exist or you are not the author.";
			$display_surveys = false;
		} else {
			//perform UPDATE
			$sql = "UPDATE surveys SET ispublic=$new_ispublic WHERE survey_id=$survey_id;";
			$result = mysqli_query($conn, $sql);
			if ($result === FALSE){
				$message = "Error encountered while changing survey visibility.";
			}
		}
	}
	//close connection
	mysqli_close($conn);
} 
if ($display_surveys) {
	//display all user's surveys or all surveys if admin.
	$admin_mode = false;
	if ($_SESSION['username'] == "admin") {
		//admin header
		echo "<h2>All Surveys</h2>";
		$admin_mode = true;
	} else {
		echo "<h2>My Surveys</h2>";
	}
	//connect to db
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}	
	
	if ($admin_mode){
		//if admin show all with author info
		$sql = 
'SELECT survey_id as "Survey ID", 
		survey_name as "Survey Name", 
		author as "Author",
		(SELECT COUNT(*) FROM questions WHERE survey_id = surv.survey_id) as "No. Questions", 
		(SELECT COUNT(*) FROM results WHERE survey_id = surv.survey_id AND iscomplete = true) as "Results", 
		(CONCAT(\'http://localhost/survey_website/surveys/?s=\',survey_id)) as "URL",
		(CONCAT(\'<a title="view results" class="chart_survey surveycell" href="surveys/charts.php?s=\',survey_id,\'"></a>\')) as "<a class=\'chart_survey_head surveyhead\'/>",
		(CONCAT(\'<a title="edit survey" class="edit_survey surveycell" href="surveys/edit.php?s=\',survey_id,\'"></a>\')) as "<a class=\'edit_survey_head surveyhead\'/>",
		if (ispublic=true, 
			(CONCAT(\'<a title="set survey visibility to private" class="private_survey surveycell" href="surveys_manage.php?private_s=\',survey_id,\'"></a>\')),
			(CONCAT(\'<a title="set survey visibility to public" class="public_survey surveycell" href="surveys_manage.php?public_s=\',survey_id,\'"></a>\')))
			 
		as "<a class=\'public_survey_head surveyhead\'/>",
		(CONCAT(\'<a title="delete survey" class="delete_survey surveycell" href="surveys_manage.php?delete_s=\',survey_id,\'"></a>\')) as "<a class=\'delete_survey_head surveyhead\'/>"
FROM surveys surv;';
	} else {
		//if not admin only show theirs without author info
		$sql = 
'SELECT survey_id as "Survey ID", 
		survey_name as "Survey Name", 
		(SELECT COUNT(*) FROM questions WHERE survey_id = surv.survey_id) as "No. Questions", 
		(SELECT COUNT(*) FROM results WHERE survey_id = surv.survey_id AND iscomplete = true) as "Results", 
		(CONCAT(\'http://localhost/survey_website/surveys/?s=\',survey_id)) as "URL",
		(CONCAT(\'<a title="view results" class="chart_survey surveycell" href="surveys/charts.php?s=\',survey_id,\'"></a>\')) as "<a class=\'chart_survey_head surveyhead\'/>",
		(CONCAT(\'<a title="edit survey" class="edit_survey surveycell" href="surveys/edit.php?s=\',survey_id,\'"></a>\')) as "<a class=\'edit_survey_head surveyhead\'/>",
		if (ispublic=true, 
			(CONCAT(\'<a title="set survey visibility to private" class="private_survey surveycell" href="surveys_manage.php?private_s=\',survey_id,\'"></a>\')),
			(CONCAT(\'<a title="set survey visibility to public" class="public_survey surveycell" href="surveys_manage.php?public_s=\',survey_id,\'"></a>\')))
			 
		as "<a class=\'public_survey_head surveyhead\'/>",
		(CONCAT(\'<a title="delete survey" class="delete_survey surveycell" href="surveys_manage.php?delete_s=\',survey_id,\'"></a>\')) as "<a class=\'delete_survey_head surveyhead\'/>"
		
FROM surveys surv
WHERE author = "'.$_SESSION['username'].'";';
	}
	$result = mysqli_query($connection, $sql);
	if ($result === FALSE){
		echo "error retrieving surveys: ".mysqli_error($connection);
	}
	else if (mysqli_num_rows($result)>0){
		echo create_table($result);
	}
	else{
		if ($_SESSION['username']==='admin') echo "No surveys in the database!<br>";
		else echo "You have no surveys!<br>";
	}
	
	echo "<br><a class='normal' href='surveys/create.php'>Create a Survey</a>";
	echo $message;
}

}

// finish off the HTML for this page:
require_once "footer.php";

?>