<?php

// execute the header script:
require_once "header.php";


if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
else
{
	$showform = true;
	if (isset($_POST['forgetme'])){
		//forget user request
		setcookie('r_username', "",time()-60);
		$showform = false;
		//attempt js redirect
		echo "<script> window.location.assign('manage_account.php?uforget=true'); </script>";
		echo "Operation complete. click <a href='manage_account.php'>here</a> to return to Manage Account.";
	

	}
	else if (isset($_POST['rememberme'])){
		//remember user request
		setcookie('r_username', $_SESSION['username'],time()+60*60*24*30);
		$showform = false;
		//attempt js redirect
		echo "<script> window.location.assign('manage_account.php?uremember=true'); </script>";
		echo "Operation complete. click <a href='manage_account.php'>here</a> to return to Manage Account.";
	

	}
	if ($showform){
		$userstring = user_string($_SESSION['username']);
		if (isset($_COOKIE['r_username'])) 
			$rememberme_html = "<form class='inline2' action='manage_account.php' method='post'><input class='submit'title='forget this account on your computer.' type='submit' name='forgetme' value='Forget Me'></form>";
		else $rememberme_html = "<form class='inline2' action='manage_account.php' method='post'><input class='submit'title='remember this account on your computer.' type='submit' name='rememberme' value='Remember Me'></form>";
		echo <<<_END
	<h2>Manage Account</h2>
	You are currently signed in as $userstring.
	<br>
	<a class='harsh'href='sign_out.php'>Sign Out</a>
	<a class='normal' href='change_password.php'>Change Password</a>
	$rememberme_html
_END;
	}
}

// finish off the HTML for this page:
require_once "footer.php";

?>