<?php

// Things to notice:
// This script is called by every other script (via require_once)
// It begins the HTML output, with the customary tags, that will produce each of the pages on the web site
// It starts the session and displays a different set of menu links depending on whether the user is logged in or not...
// ... And, if they are logged in, whether or not they are the admin
// It also reads in the credentials for our database connection from credentials.php

// database connection details:
require_once "credentials.php";

// our helper functions:
require_once "helper.php";

// start/restart the session:
session_start();
echo <<<_END
<!DOCTYPE html>
<html>
<head>
<title>Survey Website</title>
<link rel="stylesheet" type="text/css" href="css/survey.css">
<meta name="viewport" content="width=device-width,height=device-height, initial-scale=1">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
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
<a class='normal' href='about.php'>About</a>
<a class='normal' href='account.php'>Edit Account</a>
<a class='normal' href='public_surveys.php'>Public Surveys</a>
<a class='normal' href='surveys_manage.php'>$surveyprefix Surveys</a>
<a class='normal' href='competitors.php'>Design and Analysis</a>
<a class='normal' href='manage_account.php'>Manage Account</a>

_END;
	// add an extra menu option if this was the admin:
	if ($_SESSION['username'] == "admin")
	{
		echo "<a class='normal' href='admin.php'>Admin Tools</a>
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
<a class='normal' href='about.php'>About</a>
<a class='normal' href='sign_up.php'>Sign Up</a>
<a class='normal' href='sign_in.php'>Sign In</a>
</nav>

_END;
}
if (isset($_GET['logout']) and !isset($_SESSION['loggedIn '])){
	//user just logged out, display alert.
	echo alert_box("signed out.");
} 
elseif (isset($_GET['login']) and isset($_SESSION['loggedIn']) and isset($_SESSION['username'])){
	//user just logged in, display alert.
	$userstring = user_string($_SESSION['username']);
	echo alert_box("signed in as $userstring.");
}
elseif (isset($_GET['signup'])){
	echo alert_box("signup successful.");
}
elseif (isset($_GET['deleted'])){
	//user just deleted a survey.
	echo alert_box("survey deleted.");
}
elseif (isset($_GET['created'])){
	//user just created a survey.
	echo alert_box("survey created.");
}
elseif (isset($_GET['uforget']) and isset($_SESSION['loggedIn']) and isset($_SESSION['username'])){
	//user details forgotten.
	echo alert_box(user_string($_SESSION['username'])." details removed from your computer.");
}
elseif (isset($_GET['uremember']) and isset($_SESSION['loggedIn']) and isset($_SESSION['username'])){
	//user details remembered.
	echo alert_box(user_string($_SESSION['username'])." details added to your computer.");
}
else if (isset($_GET['public_s'])){
	//user just made survey public
	echo alert_box("survey is now <span class='public'>public</span>");
}
if (isset($_GET['private_s'])){
	//user made survey private
	echo alert_box("survey is now <span class='private'>private<span>");
}
if (isset($_SESSION['loggedIn']) and isset($_SESSION['username'])){
	//display logged in message
	$userstring = user_string($_SESSION['username']);
	echo "<div id='login_notify'>signed in as $userstring</div>";
}

?>