<?php

// this script lets an ADMIN edit the profile of any user.


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

if (!isset($_SESSION['loggedIn']) or !isset($_SESSION['username'])) {
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
elseif ($_SESSION['username'] !== "admin"){
	echo "You don't have permission to view this page...<br>";
}
elseif (isset($_POST['email'])){
	// admin just tried to update their profile
	//read username from GET
	$editusername = $_GET["username"];
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
	if ($country !== "")$country_val = validateCountry($country);
	$gender = sanitise($_POST['gender'], $connection);
	if ($gender !== "")$gender_val = validateGender($gender);
	//concatenate val errors
	$errors = $firstname_val.$lastname_val.$email_val.$dob_val.$phonenum_val.$country_val.$gender_val;
	
	// check that all the validation tests passed before going to the database:
	if ($errors == "") {		
		
		//values validated -> update!
		$sql = "UPDATE users SET first_name = '$firstname', last_name = '$lastname', email = '$email', dob = '$dob', phone_num = '$phonenum', country = '$country', gender = '$gender' WHERE username = '$editusername';";
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
elseif (isset($_GET['username'])) {
	//admin loading page, load the data fromm db:
	$editusername = $_GET['username'];
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	$sql = "SELECT * FROM users WHERE username='$editusername'";
	$result = mysqli_query($connection, $sql);
	if ($result === FALSE or mysqli_num_rows($result)==0){
		echo "User $editusername not found.";
	} else {
		$row = mysqli_fetch_assoc($result);
		$firstname = $row['first_name'];
		$lastname = $row['last_name'];
		$email = $row['email'];
		$dob = $row['dob'];
		$phonenum = $row['phone_num'];
		$country = $row['country'];
		$gender = $row['gender'];
		$show_account_form = true;
	}
} else{
	echo "Error. (username not supplied)";
}

if ($show_account_form)
{
	$user_string = user_string($editusername);

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

<form action="account_set.php?username=$editusername" method="post" class="haserrors">
  Update profile info for $user_string.<br> Note that fields marked with a <span class="required">*</span>&nbsp&nbsp&nbspare required.
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
  <a class='normal' title='return to admin page' href='admin.php'>Go Back</a>
</form>

_END;
}

// display our message to the user:
echo $message;

// finish of the HTML for this page:
require_once "footer.php";
?>