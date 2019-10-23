<?php

// Things to notice:
// This file is the first one we will run when we mark your submission
// Its job is to: 
// Create your database (currently called "skeleton", see credentials.php)... 
// Create all the tables you will need inside your database (currently it makes a simple "users" table, you will probably need more and will want to expand fields in the users table to meet the assignment specification)... 
// Create suitable test data for each of those tables 
// NOTE: this last one is VERY IMPORTANT - you need to include test data that enables the markers to test all of your site's functionality







// read in the details of our MySQL server:
require_once "credentials.php";
require_once "helper.php";

// We'll use the procedural (rather than object oriented) mysqli calls

// connect to the host:
$connection = mysqli_connect($dbhost, $dbuser, $dbpass);

// exit the script with a useful message if there was an error:
if (!$connection)
{
	die("Connection failed: " . $mysqli_connect_error);
}
  
// build a statement to create a new database:
$sql = "CREATE DATABASE IF NOT EXISTS " . $dbname;

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) 
{
	echo "Database created successfully, or already exists<br>";
} 
else
{
	die("Error creating database: " . mysqli_error($connection));
}

// connect to our database:
mysqli_select_db($connection, $dbname);

//////////////////////////////////////////
//DROP ALL TABLES IN ACCEPTABLE ORDER/////
//////////////////////////////////////////

// if there's an old version of our table, then drop it:
$sql = "DROP TABLE IF EXISTS responses";
// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
	echo "Dropped existing table: responses<br>";
} 
else {	
	die("Error checking for existing table: " . mysqli_error($connection));
}
$sql = "DROP TABLE IF EXISTS results";
// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
	echo "Dropped existing table: results<br>";
} 
else {	
	die("Error checking for existing table: " . mysqli_error($connection));
}
$sql = "DROP TABLE IF EXISTS questions";
// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
	echo "Dropped existing table: questions<br>";
} 
else {	
	die("Error checking for existing table: " . mysqli_error($connection));
}
$sql = "DROP TABLE IF EXISTS surveys";
// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
	echo "Dropped existing table: surveys<br>";
} 
else {	
	die("Error checking for existing table: " . mysqli_error($connection));
}
$sql = "DROP TABLE IF EXISTS users";
// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) {
	echo "Dropped existing table: users<br>";
} 
else {	
	die("Error checking for existing table: " . mysqli_error($connection));
}
	






///////////////////////////////////////////
////////////// USERS TABLE ////////////////
///////////////////////////////////////////

// make our table:
$sql = "CREATE TABLE users (username VARCHAR(16), hash CHAR(32), email VARCHAR(64), first_name VARCHAR(32), last_name VARCHAR(32), dob DATE, phone_num CHAR(14), country VARCHAR(32), gender CHAR(1), PRIMARY KEY(username))";

// no data returned, we just test for true(success)/false(failure):
if (mysqli_query($connection, $sql)) 
{
	echo "Table created successfully: users<br>";
}
else 
{
	die("Error creating table: " . mysqli_error($connection));
}

// put some data in our table:
$users_usernames[] = 'barrym'; $users_passwords[] = md5(salt('letmein')); $emails[] = 'barry@m-domain.com'; $firstnames[] = 'Barry'; $lastnames[] = 'Mitchell';  $dobs[] = '1994-03-25'; $phonenums[] = '01282677179'; $countries[] = "UK"; $genders[] = "M";
$users_usernames[] = 'mandyb'; $users_passwords[] = md5(salt('abc123')); $emails[] = 'webmaster@mandy-g.co.uk'; $firstnames[] = 'Mandy'; $lastnames[] = 'Gjurdsen';  $dobs[] = '1987-11-30'; $phonenums[] = '02846104629';$countries[] = "Sweden"; $genders[] = "M";
$users_usernames[] = 'timmy'; $users_passwords[] = md5(salt('secret95')); $emails[] = 'timmy@lassie.com'; $firstnames[] = 'Timmy'; $lastnames[] = 'Mitchell';  $dobs[] = '2002-12-15'; $phonenums[] = '01046307546';$countries[] = "USA"; $genders[] = "";
$users_usernames[] = 'briang'; $users_passwords[] = md5(salt('password')); $emails[] = 'brian@quahog.gov'; $firstnames[] = 'Brian'; $lastnames[] = 'The Brain';  $dobs[] = '1999-06-08'; $phonenums[] = '04679138045';$countries[] = "France"; $genders[] = "M";
$users_usernames[] = 'admin'; $users_passwords[] = md5(salt('secret')); $emails[] = 'admin@mail.com'; $firstnames[] = 'Boss'; $lastnames[] = 'Kimmel';  $dobs[] = '1968-03-18'; $phonenums[] = '';$countries[] = "Germany"; $genders[] = "F";
$users_usernames[] = 'dumbo'; $users_passwords[] = md5(salt('troll')); $emails[] = 'emeil@mail.com'; $firstnames[] = 'Dum'; $lastnames[] = 'Bo';  $dobs[] = '2001-03-30'; $phonenums[] = '';$countries[] = "UK"; $genders[] = "F";
$users_usernames[] = 'juniper2'; $users_passwords[] = md5(salt('p')); $emails[] = 'mailme@mail.uk'; $firstnames[] = 'June'; $lastnames[] = 'May';  $dobs[] = '2000-01-02'; $phonenums[] = '';$countries[] = "UK"; $genders[] = "M";
$users_usernames[] = 'frank'; $users_passwords[] = md5(salt('pass')); $emails[] = '17003003@stu.mmu.ac.uk'; $firstnames[] = 'Frank'; $lastnames[] = 'Dippnall';  $dobs[] = '1999-06-07'; $phonenums[] = '';$countries[] = "UK"; $genders[] = "M";
// loop through the arrays above and add rows to the table:
for ($i=0; $i<count($users_usernames); $i++)
{
	$sql = "INSERT INTO users (username, hash, email, first_name, last_name, dob, phone_num, country, gender) VALUES ('$users_usernames[$i]', '$users_passwords[$i]', '$emails[$i]', '$firstnames[$i]', '$lastnames[$i]','$dobs[$i]','$phonenums[$i]','$countries[$i]', '$genders[$i]');";
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) 
	{
		echo "&emsp;row inserted: $users_usernames[$i], $users_passwords[$i]<br>";
	}
	else 
	{
		die("Error inserting row: " . mysqli_error($connection));
	}
}




///////////////////////////////////////////
////////////// SURVEYS TABLE //////////////
///////////////////////////////////////////
	
	// make our table:
	$sql = "CREATE TABLE surveys(survey_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT, author VARCHAR(16), survey_name VARCHAR(128), instruction VARCHAR(256), ispublic BOOLEAN, CONSTRAINT FOREIGN KEY (author) REFERENCES users(username));";
	
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) 
	{
		echo "Table created successfully: surveys<br>";
	}
	else 
	{
		die("Error creating table: " . mysqli_error($connection));
	}
	// put some data in our table:
	$authors[] = 'barrym'; $surveynames[] = 'Global Warming Questionnaire'; $instructions[] = 'Complete the survey by clicking the buttons.'; //main survey atm
	$authors[] = 'barrym'; $surveynames[] = 'Glaciation Questionnaire'; $instructions[] = 'Complete the survey by clicking the buttons.';
	$authors[] = 'briang'; $surveynames[] = 'Fitness Study'; $instructions[] = 'Complete the study by answering 10 questions on fitness.';

	// loop through the arrays above and add rows to the table:
	for ($i=0; $i<count($authors); $i++)
	{
		$sql = "INSERT INTO surveys (author, survey_name, instruction, ispublic) VALUES ('$authors[$i]', '$surveynames[$i]','$instructions[$i]', true);";
		// no data returned, we just test for true(success)/false(failure):
		if (mysqli_query($connection, $sql)) 
		{
			echo "&emsp;row inserted: $authors[$i], $surveynames[$i]<br>";
		}
		else 
		{
			die("Error inserting row: " . mysqli_error($connection));
		}
	}

///////////////////////////////////////////
////////////// QUESTIONS TABLE ////////////
///////////////////////////////////////////
	
	// make our table:
	$sql = "CREATE TABLE questions(survey_id INT, question_num INT, question_text VARCHAR(128), question_type VARCHAR(16), isrequired BOOLEAN, CONSTRAINT question_pk PRIMARY KEY(survey_id,question_num), CONSTRAINT FOREIGN KEY (survey_id) REFERENCES surveys(survey_id) ON DELETE CASCADE);";
	
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) 
	{
		echo "Table created successfully: questions<br>";
	}
	else 
	{
		die("Error creating table: " . mysqli_error($connection));
	}
	
	// put some data in our table:
	$q_survey_id[] = 1; $q_req[] = "true";  $q_questionnums[] = 1; $questiontexts[] = "Do you think global warming is a real phenomenon?"; $questiontypes[] = "yesnounsure"; 
	$q_survey_id[] = 1; $q_req[] = "false";$q_questionnums[] = 2; $questiontexts[] = "Do you think global warming affects your local environment?"; $questiontypes[] = "yesnounsure";
	$q_survey_id[] = 1; $q_req[] = "false";$q_questionnums[] = 3; $questiontexts[] = "How much of the national budget would you like allocated to combating global warming?"; $questiontypes[] = "percentageinput";
	$q_survey_id[] = 1; $q_req[] = "true";$q_questionnums[] = 4; $questiontexts[] = "In what way do you think global warming affects your local environment?"; $questiontypes[] = "textinput";
	$q_survey_id[] = 1; $q_req[] = "false";$q_questionnums[] = 5; $questiontexts[] = "How good was this survey?"; $questiontypes[] = "rate10"; 
	$q_survey_id[] = 2; $q_req[] = "false";$q_questionnums[] = 1; $questiontexts[] = "Do you like Polar Bears?"; $questiontypes[] = "yesnounsure";
	$q_survey_id[] = 2; $q_req[] = "false";$q_questionnums[] = 2; $questiontexts[] = "Are you sure?"; $questiontypes[] = "yesno";
	$q_survey_id[] = 3; $q_req[] = "true";$q_questionnums[] = 1; $questiontexts[] = "How healthy are you (rate out of ten)?"; $questiontypes[] = "rate10"; 
	$q_survey_id[] = 3; $q_req[] = "false";$q_questionnums[] = 2; $questiontexts[] = "How many TV programmes do you watch every week?"; $questiontypes[] = "numberinput"; 
	// loop through the arrays above and add rows to the table:
	for ($i=0; $i<count($q_survey_id); $i++)
	{
		$sql = "INSERT INTO questions (survey_id, question_num, question_text, question_type, isrequired) VALUES ('$q_survey_id[$i]','$q_questionnums[$i]','$questiontexts[$i]','$questiontypes[$i]', $q_req[$i]);";
		// no data returned, we just test for true(success)/false(failure):
		if (mysqli_query($connection, $sql)) 
		{
			echo "&emsp;row inserted: survey $q_survey_id[$i], Q$q_questionnums[$i]: '$questiontexts[$i]'<br>";
		}
		else 
		{
			die("Error inserting row: " . mysqli_error($connection));
		}
	}
///////////////////////////////////////////
////////////// RESULTS TABLE //////////////
///////////////////////////////////////////
	// make our table:
	$sql = "CREATE TABLE results(result_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT, survey_id INT, username VARCHAR(16), iscomplete BOOLEAN, CONSTRAINT FOREIGN KEY (survey_id) REFERENCES surveys(survey_id) ON DELETE CASCADE, CONSTRAINT FOREIGN KEY (username) REFERENCES users(username));";
	
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) 
	{
		echo "Table created successfully: results<br>";
	}
	else 
	{
		die("Error creating table: " . mysqli_error($connection));
	}
	// put some data in our table:
	$results_survey_id[] = 1; $results_usernames[] = 'mandyb';
	$results_survey_id[] = 1; $results_usernames[] = 'briang';
	$results_survey_id[] = 1; $results_usernames[] = 'frank';
	$results_survey_id[] = 1; $results_usernames[] = 'admin';
	$results_survey_id[] = 1; $results_usernames[] = 'dumbo';
	$results_survey_id[] = 1; $results_usernames[] = 'juniper2';
	$results_survey_id[] = 1; $results_usernames[] = 'timmy';
	$results_survey_id[] = 2; $results_usernames[] = 'frank';
	$results_survey_id[] = 3; $results_usernames[] = 'frank';
	// loop through the arrays above and add rows to the table:
	for ($i=0; $i<count($results_survey_id); $i++)
	{
		$sql = "INSERT INTO results(survey_id, username, iscomplete) VALUES ($results_survey_id[$i], '$results_usernames[$i]', true);";
		// no data returned, we just test for true(success)/false(failure):
		if (mysqli_query($connection, $sql)) 
		{
			echo "&emsp;row inserted: result for survey #$results_survey_id[$i], by '$results_usernames[$i]'<br>";
		}
		else 
		{
			die("Error inserting row: " . mysqli_error($connection));
		}
	}
///////////////////////////////////////////
////////////// RESPONSES TABLE ////////////
///////////////////////////////////////////
	// make our table:
	$sql = "CREATE TABLE responses(result_id INT, question_num INT, response_int INT, response_short VARCHAR(32), response_long LONGTEXT, CONSTRAINT FOREIGN KEY (result_id) REFERENCES results(result_id)  ON DELETE CASCADE, CONSTRAINT PRIMARY KEY (result_id, question_num));";
	
	// no data returned, we just test for true(success)/false(failure):
	if (mysqli_query($connection, $sql)) 
	{
		echo "Table created successfully: responses<br>";
	}
	else 
	{
		die("Error creating table: " . mysqli_error($connection));
	}
	// response data. ONLY IN-DEPTH FOR SURVEY 1, others have little data.
	$resp_result_id[] = 1; $resp_questionnums[] = 1; $response_int[] = -1; $response_short[] = "UNSURE"; $response_long[] = "";
	$resp_result_id[] = 1; $resp_questionnums[] = 2; $response_int[] = -1;$response_short[] = "NO"; $response_long[] = "";
	$resp_result_id[] = 1; $resp_questionnums[] = 3; $response_int[] = 50;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 1; $resp_questionnums[] = 4; $response_int[] = -1;$response_short[] = ""; $response_long[] = sanitise("I don't really care to be honest. This is just sample data.", $connection);
	$resp_result_id[] = 1; $resp_questionnums[] = 5; $response_int[] = 4;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 2; $resp_questionnums[] = 1; $response_int[] = -1; $response_short[] = "YES"; $response_long[] = "";
	$resp_result_id[] = 2; $resp_questionnums[] = 2; $response_int[] = -1;$response_short[] = "NO"; $response_long[] = "";
	$resp_result_id[] = 2; $resp_questionnums[] = 3; $response_int[] = 63;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 2; $resp_questionnums[] = 4; $response_int[] = -1;$response_short[] = ""; $response_long[] = sanitise("It affects the environment in mysterious ways...", $connection);
	$resp_result_id[] = 2; $resp_questionnums[] = 5; $response_int[] = 8;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 3; $resp_questionnums[] = 1; $response_int[] = -1; $response_short[] = "YES"; $response_long[] = "";
	$resp_result_id[] = 3; $resp_questionnums[] = 2; $response_int[] = -1;$response_short[] = "YES"; $response_long[] = "";
	$resp_result_id[] = 3; $resp_questionnums[] = 3; $response_int[] = 10;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 3; $resp_questionnums[] = 4; $response_int[] = -1;$response_short[] = ""; $response_long[] = sanitise("ICEBERG!!!!", $connection);
	$resp_result_id[] = 3; $resp_questionnums[] = 5; $response_int[] = 8;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 4; $resp_questionnums[] = 1; $response_int[] = -1; $response_short[] = "YES"; $response_long[] = "";
	$resp_result_id[] = 4; $resp_questionnums[] = 2; $response_int[] = -1;$response_short[] = "UNSURE"; $response_long[] = "";
	$resp_result_id[] = 4; $resp_questionnums[] = 3; $response_int[] = 30;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 4; $resp_questionnums[] = 4; $response_int[] = -1;$response_short[] = ""; $response_long[] = sanitise("Wikipedia told me to.", $connection);
	$resp_result_id[] = 4; $resp_questionnums[] = 5; $response_int[] = -1;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 5; $resp_questionnums[] = 1; $response_int[] = -1; $response_short[] = "NO"; $response_long[] = "";
	$resp_result_id[] = 5; $resp_questionnums[] = 2; $response_int[] = -1;$response_short[] = "NO"; $response_long[] = "";
	$resp_result_id[] = 5; $resp_questionnums[] = 3; $response_int[] = -1;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 5; $resp_questionnums[] = 4; $response_int[] = -1;$response_short[] = ""; $response_long[] = sanitise("I don't believe in fairies", $connection);
	$resp_result_id[] = 5; $resp_questionnums[] = 5; $response_int[] = 6;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 6; $resp_questionnums[] = 1; $response_int[] = -1; $response_short[] = "YES"; $response_long[] = "";
	$resp_result_id[] = 6; $resp_questionnums[] = 2; $response_int[] = -1;$response_short[] = "YES"; $response_long[] = "";
	$resp_result_id[] = 6; $resp_questionnums[] = 3; $response_int[] = 40;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 6; $resp_questionnums[] = 4; $response_int[] = -1;$response_short[] = ""; $response_long[] = sanitise("Scientists are smarter than me, so why question what they say? Also, the evidence against climate change and global warming is flimsy at best.", $connection);
	$resp_result_id[] = 6; $resp_questionnums[] = 5; $response_int[] = 8;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 7; $resp_questionnums[] = 1; $response_int[] = -1; $response_short[] = "YES"; $response_long[] = "";
	$resp_result_id[] = 7; $resp_questionnums[] = 2; $response_int[] = -1;$response_short[] = "UNSURE"; $response_long[] = "";
	$resp_result_id[] = 7; $resp_questionnums[] = 3; $response_int[] = 70;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 7; $resp_questionnums[] = 4; $response_int[] = -1;$response_short[] = ""; $response_long[] = sanitise("", $connection);
	$resp_result_id[] = 7; $resp_questionnums[] = 5; $response_int[] = 8;$response_short[] = ""; $response_long[] = "";
	//other survey results
	$resp_result_id[] = 8; $resp_questionnums[] = 1; $response_int[] = -1;$response_short[] = "UNSURE"; $response_long[] = "";
	$resp_result_id[] = 8; $resp_questionnums[] = 2; $response_int[] = -1;$response_short[] = "NO"; $response_long[] = "";
	$resp_result_id[] = 9; $resp_questionnums[] = 1; $response_int[] = 9;$response_short[] = ""; $response_long[] = "";
	$resp_result_id[] = 9; $resp_questionnums[] = 2; $response_int[] = 120;$response_short[] = ""; $response_long[] = "";


	// loop through the arrays above and add rows to the table:
	for ($i=0; $i<count($resp_result_id); $i++)
	{
		$sql = "INSERT INTO responses(result_id, question_num, response_int, response_short, response_long) VALUES ($resp_result_id[$i], $resp_questionnums[$i], $response_int[$i],'$response_short[$i]','$response_long[$i]');";
		// no data returned, we just test for true(success)/false(failure):
		if (mysqli_query($connection, $sql)) 
		{
			echo "&emsp;row inserted: response for result #$resp_result_id[$i], Q$resp_questionnums[$i]: $response_long[$i]$response_short[$i]<br>";
		}
		else 
		{
			die("Error inserting row: " . mysqli_error($connection));
		}
	}
// we're finished, close the connection:
mysqli_close($connection);

echo "<br>Operation Complete. <a href='sign_out.php?forgetuser=true&noalert=true'>Go to the site</a>";
?>