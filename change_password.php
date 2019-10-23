<?php
//change the password of a user
// execute the header script:
require_once "header.php";
echo "<h2>Change Password</h2>";
// default values we show in the form:
$password = "";
$newpassword = "";
$newpasswordconfirm = "";
    
// strings to hold any validation error messages:
$password_val = "";
$newpassword_val = "";
 
// should we show the set profile form?:
$show_pwd_form = false;
// message to output to user:
$message = "";

if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
elseif (isset($_POST['password']))
{
	// user just tried to change password
	// connect directly to our database (notice 4th argument) we need the connection for sanitisation:
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	//get username
	$username = $_SESSION["username"];
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}
	
	$password = sanitise($_POST['password'], $connection);
	$newpassword = sanitise($_POST['newpassword'], $connection);
	$newpasswordconfirm = sanitise($_POST['newpasswordconfirm'], $connection);
	//check passwords match
	if ($newpassword === $newpasswordconfirm){
		// check current password is correct:
		$hash = md5(salt($password));
		$sql = "SELECT username FROM users WHERE username='$username' AND hash='$hash';";
		$result = mysqli_query($connection, $sql);
		if ($result === FALSE or mysqli_num_rows($result)==0){
			//password incorrect.
			$password_val = "Incorrect password.";
			$show_pwd_form = true;
		} else {
			//password correct. validation done, update password.
			$newhash = md5(salt($newpassword));
			$sql = "UPDATE users SET hash='$newhash' WHERE username='$username';";
			$result = mysqli_query($connection, $sql);
			if ($result === FALSE){
				$message = "Connection error. Please try again.";
				$show_pwd_form = true;
			} else {
				$message = alert_box("Password updated successfully.");
				$show_pwd_form = true;
			}
		}
	} else{
		$newpassword_val = "Passwords do not match.";
		$show_pwd_form = true;
	}
	
	// we're finished with the database, close the connection:
	mysqli_close($connection);

}
else
{
	// show the form:
	$show_pwd_form = true;

}

if ($show_pwd_form)
{
echo <<<_END

<form action="change_password.php" method="post" class="haserrors">
  <div class="fields">
  <p>
  <label>Current Password </label>
  <input type="password" name="password" maxlength="16" value="$password" required> 
  <span class="error">$password_val</span>
  </p> 
  <p>
  <label>New Password</label>
  <input type="password" name="newpassword" maxlength="16" value="$newpassword" required> 
  <span class="error">$newpassword_val</span>
  </p>
  <p>
  <label>Confirm New Password</label>
  <input type="password" name="newpasswordconfirm" maxlength="16" value="$newpasswordconfirm" required> 
  <span class="error"></span>
  </p>
  </div>
  <input class="submit" type="submit" value="Update Password">
</form>
_END;
}

// display our message to the user:
echo $message;

// finish of the HTML for this page:
require_once "footer.php";
?>