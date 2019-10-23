<?php

// Things to notice:
// The main job of this script is to execute an INSERT statement to add the submitted username, password and email address
// However, the assignment specification tells you that you need more fields than this for each user.
// So you will need to amend this script to include them. Don't forget to update your database (create_data.php) in tandem so they match
// This script does client-side validation using "password","text" inputs and "required","maxlength" attributes (but we can't rely on it happening!)
// we sanitise the user's credentials - see helper.php (included via header.php) for the sanitisation function
// we validate the user's credentials - see helper.php (included via header.php) for the validation functions
// the validation functions all follow the same rule: return an empty string if the data is valid...
// ... otherwise return a help message saying what is wrong with the data.
// if validation of any field fails then we display the help messages (see previous) when re-displaying the form

// execute the header script:
require_once "header.php";

// default values we show in the form:
$firstname = "";
$lastname = "";
$dob = "";
$phonenum = "";
$username = "";
$password = "";
$passwordConfirm = "";
$email = "";
$country = "";
$gender = "";

// strings to hold any validation error messages:
$firstname_val = "";
$lastname_val = "";
$dob_val = "";
$phonenum_val = "";
$username_val = "";
$password_val = "";
$email_val = "";
$country_val = "";
$gender_val = "";

// should we show the signup form?:
$show_signup_form = false;
// message to output to user:
$message = "";

if (isset($_SESSION['loggedIn']))
{
	// user is already logged in, just display a message:
	echo "You are already logged in, please log out if you wish to create a new account<br>";
	
}
elseif (isset($_POST['username']))
{
	// user just tried to sign up:
	
	// connect directly to our database (notice 4th argument) we need the connection for sanitisation:
	$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	
	// if the connection fails, we need to know, so allow this exit:
	if (!$connection)
	{
		die("Connection failed: " . $mysqli_connect_error);
	}	
	
	// SANITISATION (see helper.php for the function definition)
	
	// take copies of the credentials the user submitted, and sanitise (clean) them:
	$email = sanitise($_POST['email'], $connection);
	$firstname = sanitise($_POST['firstname'], $connection);
	$lastname = sanitise($_POST['lastname'], $connection);
	$dob = sanitise($_POST['dob'], $connection);
	$phonenum = sanitise($_POST['phonenum'], $connection);
	$username = sanitise($_POST['username'], $connection);
	$password = sanitise($_POST['password'], $connection);
	$passwordConfirm = sanitise($_POST['passwordConfirm'], $connection);
	$country = sanitise($_POST['country'], $connection);
	$gender =  sanitise($_POST['gender'], $connection);

	
	
	// VALIDATION (see helper.php for the function definitions)
	$firstname_val = validateString($firstname, 1, 32);
	$lastname_val = validateString($lastname, 1, 32);
	if ($dob !== "") $dob_val = validateBirthDate($dob);
	if ($phonenum !== "") $phonenum_val = validatePhoneNum($phonenum);
	if ($country !== "") $country_val = validateCountry($country);
	if ($gender !== "") $gender_val = validateGender($gender);
	$username_val = validateString($username, 1, 16);
	$password_val = validateString($password, 1, 16);
	$email_val = validateEmail($email, 1, 64);
	
	
	// concatenate all the validation results together ($errors will only be empty if ALL the data is valid):
	$errors = $country_val . $email_val . $firstname_val . $lastname_val . $dob_val . $phonenum_val . $username_val . $password_val . $gender_val;
	
	// check that all the validation tests passed before going to the database:
	if ($errors == "")
	{
		if ($password !== $passwordConfirm){
			//passwords don't match.
			$password_val = "passwords don't match.";
			$show_signup_form = true;
		}
		else{
			$hash = md5(salt($password));
			// try to insert the new details:
			$query = "INSERT INTO users (username, hash, email, first_name, last_name, dob, phone_num, country, gender) VALUES ('$username', '$hash', '$email','$firstname','$lastname','$dob','$phonenum','$country', '$gender');";
			$result = mysqli_query($connection, $query);
			
			// no data returned, we just test for true(success)/false(failure):
		if ($result) {
			//attempt js redirect
			echo "<script> window.location.assign('sign_in.php?signup=true'); </script>";
			// show a successful signup message:
			$message = "Signup was successful, please <a href='sign_in.php'>sign in</a><br>";
			$show_signup_form = false;
		}
		else {
			// show the form:
			$show_signup_form = true;
			//check if username already exists
			$sql = "SELECT username FROM users WHERE username='$username'";
			$result = mysqli_query($connection, $sql);
			if ($result !== FALSE && mysqli_num_rows($result)>0){
				//username already exists, display error:
				$username_val = "Username taken :(";
			}
			
			// show an unsuccessful signup message:
			$message = "Sign up failed, please try again<br>";
		}
		}
	}
	else
	{
		// validation failed, show the form again with guidance:
		$show_signup_form = true;
		// show an unsuccessful signin message:
		$message = "Sign up failed, please check the errors shown above and try again<br>";
	}
	
	// we're finished with the database, close the connection:
	mysqli_close($connection);

}
else
{
	// just a normal visit to the page, show the signup form:
	$show_signup_form = true;
	
}

if ($show_signup_form)
{
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
// show the form that allows users to sign up
// Note we use an HTTP POST request to avoid their password appearing in the URL:	
echo <<<_END
<form action="sign_up.php" method="post" class="haserrors">
  Use this form to sign up. Note that fields marked with a <span class="required">*</span>&nbsp&nbsp&nbspare required.
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
  <label>Username<span class="required">*</span> </label>
  <input type="text" name="username" maxlength="32" value="$username" required> 
  <span class="error">$username_val</span>
  </p>
  <p>
  <label>Password<span class="required">*</span> </label>
  <input type="password" name="password" maxlength="16" value="$password" required> 
  <span class="error">$password_val</span>
  </p>
  <p>
  <label>Confirm Password<span class="required">*</span> </label>
  <input type="password" name="passwordConfirm" maxlength="16" value="$passwordConfirm" required> 
  <span class="error"></span>
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

// finish off the HTML for this page:
require_once "footer.php";

?>