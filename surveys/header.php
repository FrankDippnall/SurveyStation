
<?php



//edited header for survey viewing

// database connection details:
require_once "../credentials.php";

// our helper functions:
require_once "../helper.php";

// start/restart the session:
session_start();
echo <<<_END
<!DOCTYPE html>
<html>
<head>
<title>Survey Website</title>
<link rel="stylesheet" type="text/css" href="../css/survey.css">
<meta name="viewport" content="width=device-width,height=device-height, initial-scale=1">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<body>
<main id='mainContainer'>
<h1>Survey Station</h1>
_END;
if (isset($_SESSION['loggedIn']))
{
	// THIS PERSON IS LOGGED IN
	// show the logged in menu options:
	$surveyprefix = "My";
	if ($_SESSION['username'] == "admin"){$surveyprefix = "All";}

echo <<<_END
<nav>
<a class='normal' href='../about.php'>About</a>
<a class='normal' href='../account.php'>Edit Account</a>
<a class='normal' href='../public_surveys.php'>Public Surveys</a>
<a class='normal' href='../surveys_manage.php'>$surveyprefix Surveys</a>
<a class='normal' href='../competitors.php'>Design and Analysis</a>
<a class='normal' href='../manage_account.php'>Manage Account</a>

_END;
	// add an extra menu option if this was the admin:
	if ($_SESSION['username'] == "admin")
	{
		echo "<a class='normal' href='../admin.php'>Admin Tools</a>
		";
	}
echo "</nav>";
}
else
{
	// THIS PERSON IS NOT LOGGED IN
	// show the logged out menu options:
	
echo <<<_END
<nav>
<a class='normal' href='../about.php'>About</a>
<a class='normal' href='../sign_up.php'>Sign Up</a>
<a class='normal' href='../sign_in.php'>Sign In</a>
</nav>

_END;
}
if (isset($_GET['submitted'])){
	//user just submitted survey
	echo alert_box("survey results submitted.");
} 
else if (isset($_GET['responded'])){
	//user just submitted response
	echo alert_box("response saved.");
}
else if (isset($_GET['instr_update'])){
	//user just updated instruction
	echo alert_box("instructions updated.");
}
else if (isset($_GET['q_create'])){
	//user just created question
	echo alert_box("question added to survey.");
}
else if (isset($_GET['qdeleted']) and is_numeric($_GET['qdeleted'])){
	//user just deleted question
	$qdeleted = $_GET['qdeleted'];
	echo alert_box("question $qdeleted deleted.");
}
else if (isset($_GET['q_required'])){
	//user just created question
	echo alert_box("This question is <span style='color:red;'>required</span>.");
}


if (isset($_SESSION['loggedIn']) and isset($_SESSION['username'])){
	//display logged in message
	$userstring = user_string($_SESSION['username']);
	echo "<div id='login_notify'>signed in as $userstring</div>";
}
?>