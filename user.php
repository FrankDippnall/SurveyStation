<?php

// this script lets anyone view the profile of a user.


// execute the header script:
require_once "header.php";
$show_details = false;
if (!isset($_SESSION['loggedIn']) or !isset($_SESSION['username'])) {
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
elseif (isset($_GET['u'])) {
	//user loading page, load the data from db:
	
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    $username = sanitise($_GET['u'], $connection);
	$sql = "SELECT * FROM users WHERE username='$username'";
	$result = mysqli_query($connection, $sql);
	if ($result === FALSE or mysqli_num_rows($result)==0){
		echo "User $username not found.";
	} else {
		$row = mysqli_fetch_assoc($result);
		$firstname = $row['first_name'];
		$lastname = $row['last_name'];
		$email = $row['email'];
		$dob = $row['dob'];
		$phonenum = $row['phone_num'];
		$country = $row['country'];
		$show_details = true;
	}
} else{
	echo "Error. (username not supplied)";
}

if ($show_details)
{
    $user_name = ucwords($firstname . " ". $lastname);
    $format_phone = format_tel($phonenum);
    $user_country = ucwords($country);
echo <<<_END

<div class='table_container'>
<h2 class='table_head'>$username</h2>
<table id='result_info'>
<tr><th>Name</th><td>$user_name</td></tr>
<tr><th>Email</th><td>$email</td></tr>
<tr><th>DOB</th><td>$dob</td></tr>
<tr><th>Phone Number</th><td>$format_phone</td></tr>
<tr><th>Country</th><td>$user_country</td></tr>
</table>
</div>

_END;
}

// finish of the HTML for this page:
require_once "footer.php";
?>