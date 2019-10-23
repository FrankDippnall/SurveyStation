<?php

//this page creates a new survey which can then be edited.

// execute the header script:
require_once "header.php";


if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
    echo "You must be logged in to create surveys.<br>";
}
else {
    //default values
    $surveyname = "";
    $surveyname_val = "";
    $message = "";
    //display form?
    $show_form = true;

    if (isset($_POST['survey_name'])){
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        //sanitise
        $surveyname = sanitise($_POST['survey_name'], $conn);
        
        //validate
        $surveyname_val = validateString($surveyname, 1, 128);
        if ($surveyname_val === ""){
            //check if current user already has survey by that name
            $sql = "SELECT survey_name FROM surveys WHERE survey_name = '$surveyname' AND author = '{$_SESSION['username']}'";
            $result = mysqli_query($conn, $sql);
            if ($result === FALSE){
                $message = "Error while checking for author duplicate.";
            }
            else if (mysqli_num_rows($result)>0){
                $surveyname_val = "You already have a survey with that name.";
            }
            else {
                //user clicked submit, perform create
                $sql = "INSERT INTO surveys(survey_name, author) VALUES('$surveyname', '{$_SESSION['username']}');";
                $result = mysqli_query($conn, $sql);
                if ($result === TRUE){
                    $survey_id = mysqli_insert_id($conn);
                    $message = "Survey (ID $survey_id) creation successful. Click <a href='edit.php?s=$survey_id'>here</a> to edit";
                    //attempt js redirect
			        echo "<script> window.location.assign('../surveys_manage.php?created=true'); </script>";

                    $show_form = false;
                } else {
                    $message = "Error encountered. Please try again.";
                }
            }

        }
        else {
            $message = "Failed, check the errors above and try again.";
        }
        mysqli_close($conn);
    }

    if ($show_form)
    echo <<<_END
<h2>Create Survey</h2>
<form action="create.php" method="post" class="haserrors">
<div class="fields">
<p>
<label>Survey Name </label><input type="text" name="survey_name" maxlength="128" value="$surveyname" required> <span class="error">$surveyname_val</span>
<br>
</p>
</div>
<br>
<input class="submit" type="submit" value="Submit">

</form>	
_END;
echo $message;
}

// finish off the HTML for this page:
require_once "footer.php";

?>