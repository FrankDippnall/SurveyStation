<?php

// Things to notice:
// This script will let a logged-in user VIEW their account details and allow them to UPDATE those details
// The main job of this script is to execute an UPDATE statement to update a user's account information...
// ... but only once the data the user supplied has been validated on the client-side, and then sanitised ("cleaned") and validated again on the server-side
// It's your job to add these steps into the code
// Both sign_up.php and sign_in.php do client-side validation, followed by sanitisation and validation again on the server-side -- you may find it helpful to look at how they work 
// HTML5 can validate all the account data for you on the client-side
// The PHP functions in helper.php will allow you to sanitise the data on the server-side and validate *some* of the fields... 
// There are fields you will want to add to allow the user to update them...
// ... you'll also need to add some new PHP functions of your own to validate email addresses, telephone numbers and dates

// execute the header script:
require_once "header.php";

// default values we show in the form:
	$firstname = "";
	$lastname = "";
	$email = "";
	$dob = "";
	$phonenum = "";
	$country = "";
	$gender = "";


    
// strings to hold any validation error messages:
$firstname_val = "";
$lastname_val = "";
$dob_val = "";
$phonenum_val = "";	
$email_val = "";
$country_val = "";
$gender_val = "";
 
// should we show the set profile form?:
$show_account_form = false;
// message to output to user:
$message = "";

if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
elseif (isset($_POST['email']))
{
	// user just tried to update their profile
	// read their username from the session:
	$username = $_SESSION["username"];
	// connect directly to our database (notice 4th argument) we need the connection for sanitisation:
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}
	
	//get the stuff from POST and sanitise
	$firstname = sanitise($_POST['firstname'], $connection);
	$firstname_val = validateString($firstname, 1,32);
	$lastname = sanitise($_POST['lastname'], $connection);
	$lastname_val = validateString($lastname, 1,32);
	$email = sanitise($_POST['email'], $connection);
	$email_val = validateEmail($email);
	$dob = sanitise($_POST['dob'], $connection);
	if ($dob !== "") $dob_val = validateBirthDate($dob);
	$phonenum = sanitise($_POST['phonenum'], $connection);
	if ($phonenum !== "")$phonenum_val = validatePhoneNum($phonenum);
	$country = sanitise($_POST['country'], $connection);
	if ($country !== "")$country_val = validateCountry($phonenum);
	$gender = sanitise($_POST['gender'], $connection);
	if ($gender !== "")$gender_val = validateGender($gender);
	//concatenate val errors
	$errors = $firstname_val.$lastname_val.$email_val.$dob_val.$phonenum_val.$country_val.$gender_val;
	
	// check that all the validation tests passed before going to the database:
	if ($errors === "") {		
		
		//values validated -> update!
		$sql = "UPDATE users SET first_name = '$firstname', last_name = '$lastname', email = '$email', dob = '$dob', phone_num = '$phonenum', country = '$country', gender = '$gender' WHERE username = '$username';";
		$result = mysqli_query($connection, $sql);
		if ($result === FALSE){
			$message = "Update failed, please try again.";
			$show_account_form = true;
		} else {
			$message = alert_box("Update successful.");
			$show_account_form = true;
		}
	} else {
		// validation failed, show the form again with guidance:
		$show_account_form = true;
		// show an unsuccessful update message:
		$message = "Update failed, please check the errors above and try again<br>";
	}
	
	// we're finished with the database, close the connection:
	mysqli_close($connection);

}
else
{
	// arrived at the page for the first time, show any data already in the table:
	
	// read the username from the session:
	$username = $_SESSION["username"];
	
	// now read their profile data from the table...
	
	// connect directly to our database (notice 4th argument):
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}
	
	// check for a row in our profiles table with a matching username:
	$query = "SELECT first_name, last_name, email, dob, phone_num, country, gender FROM users WHERE username='$username'";
	
	// this query can return data ($result is an identifier):
	$result = mysqli_query($connection, $query);
	if ($result === FALSE or mysqli_num_rows($result) == 0){
	} else {
		//match found
		// use the identifier to fetch one row as an associative array (elements named after columns):
		$row = mysqli_fetch_assoc($result);
		// extract their profile data for use in the HTML:
			$firstname = $row['first_name'];
			$lastname = $row['last_name'];
			$email = $row['email'];
			$dob = $row['dob'];
			$phonenum = $row['phone_num'];
			$country = $row['country'];
			$gender = $row['gender'];
		// show the set profile form:
		$show_account_form = true;
		
		// we're finished with the database, close the connection:
		mysqli_close($connection);
	}
}

if ($show_account_form)
{
	$user_string = user_string($username);
	$option_list_country = create_options($COUNTRIES, $COUNTRY_LABELS);
	$select_list_country = setDefaultSelect(<<<_END
	<select name="country">
		$option_list_country
	  </select>
_END
	, $country, true);
	$option_list_gender = create_options(array("M", "F", "O"), array("Male", "Female", "Other"));
	$select_list_gender = setDefaultSelect(<<<_END
	<select name="gender">
		$option_list_gender
	</select>
_END
	, $gender, true);
echo <<<_END

<form action="account.php" method="post" class="haserrors">
  Update profile info for your account ($user_string).<br> Note that fields marked with a <span class="required">*</span>&nbsp&nbsp&nbspare required.
  <div class="fields">
  <p>
  <label>Email<span class="required">*</span></label>  <!--disabled client email validation b/c server error message is friendlier-->
  <input type="text" name="email" maxlength="64" value="$email" required>
  <span class="error">$email_val</span>
  </p>
  <p>
  <label>First Name<span class="required">*</span> </label>
  <input type="text" name="firstname" maxlength="32" value="$firstname" required> 
  <span class="error">$firstname_val</span>
  </p>
  <p>
  <label>Last Name<span class="required">*</span> </label>
  <input type="text" name="lastname" maxlength="32" value="$lastname" required> 
  <span class="error">$lastname_val</span>
  </p>
  <p>
  <label>Date of Birth&nbsp</label>
  <input class="dateinput"type="date" name="dob" value="$dob"> 
  <span class="error">$dob_val</span>
  </p>
  <p>
  <label>Phone Number&nbsp</label>
  <input type="text" name="phonenum" value="$phonenum"> 
  <span class="error">$phonenum_val</span>
  </p>
  <p>
  <label>Country&nbsp</label>
  $select_list_country
  <span class="error">$country_val</span>
  </p>
  <p>
  <label>Gender&nbsp</label>
  $select_list_gender
  <span class="error">$gender_val</span>
  </p>
  </div>
  <input class="submit" type="submit" value="Submit">
</form>
_END;
}

// display our message to the user:
echo $message;

// finish of the HTML for this page:
require_once "footer.php";
?>