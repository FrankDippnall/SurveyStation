<?php

// Things to notice:
// You need to add code to this script to implement the admin functions and features
// Notice that the code not only checks whether the user is logged in, but also whether they are the admin, before it displays the page content
// You can implement all the admin tools functionality from this script, or...

// execute the header script:
require_once "header.php";

if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
else
{
	// only display the page content if this is the admin account (all other users get a "you don't have permission..." message):
	if ($_SESSION['username'] == "admin")
	{
		echo <<<_END
<h2>All Users</h2>
Click on a user to edit their profile.
_END;
		//display list of users
		$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
		$sql = 'SELECT username as "Username", email as "E-Mail", first_name as "First Name", last_name as "Last Name", 
		dob as "DOB", phone_num as "Phone Number", country as "Country", IF (gender = \'M\', \'Male\', IF(gender = \'F\', \'Female\', IF (gender = \'O\', \'Other\', \'Unspecified\'))) as "Gender" FROM users ORDER BY username;';
		$result = mysqli_query($connection, $sql);
		if($result === FALSE){
			echo mysqli_error($connection);
		} else{
			//create clickable table that loads account.php with a post of username
			echo create_table($result, "account_set.php", "Username");
		}
	}
	else
	{
		echo "You don't have permission to view this page...<br>";
	}
}

// finish off the HTML for this page:
require_once "footer.php";
?>