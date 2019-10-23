<?php

// Things to notice:
// The main job of this script is to end the user's session
// Meaning we want to destroy the current session data


	// user just clicked logout
	//destroy session data
	// first clear the session superglobal array:
	$_SESSION = array();
	// then the cookie that holds the session ID:
	setcookie(session_name(), "", time() - 2592000, '/');
	// then the session data on the server:
	session_destroy();
	//if set, forget user
	if (isset($_GET["forgetuser"])) setcookie('r_username', "",time()-60);
	//reload sign_in.php, displaying logout message unless 'noalert' is enabled.
	if (isset($_GET["noalert"])) header("Location: sign_in.php");
	else header("Location: sign_in.php?logout=true");
	die();

?>