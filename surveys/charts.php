<?php

//this page lets the author of a survey or admin view chart analytics for each question of the survey.

// execute the header script:
require_once "header.php";


if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view surveys.<br>";
}
else
{
    $message = "";
    //connect to db
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    //view specific survey, or view recommended surveys?
    if (isset($_GET['s'])){
        //GET current survey data
        $survey_id = sanitise($_GET['s'], $conn);
        //GET question data
        if (isset($_GET['q'])) $q_num = sanitise($_GET['q'],$conn);
        else $q_num = 0;
        if (is_numeric($survey_id) and is_numeric($q_num)){
            $sql = 'SELECT author, survey_name, instruction, COUNT(question_num) AS "num_questions", (SELECT COUNT(*) FROM results WHERE survey_id = '.$survey_id.' AND iscomplete = true) as "num_results" FROM surveys INNER JOIN questions USING (survey_id) GROUP BY survey_id HAVING survey_id = '.$survey_id.';';
            $result = mysqli_query($conn, $sql);
            if ($result === FALSE){
                echo "Error retrieving survey data. Please try again.";
                echo mysqli_error($conn);
            }
            else {
                $continue = true;
                if (mysqli_num_rows($result)==0){
                    //check for edge case (no questions)
                    $sql = 'SELECT author, survey_name, instruction, 0 AS "num_questions", (SELECT COUNT(*) FROM results WHERE survey_id = '.$survey_id.' AND iscomplete = true) as "num_results" FROM surveys WHERE survey_id = '.$survey_id.';';
                    $result = mysqli_query($conn, $sql);
                    if ($result === FALSE or mysqli_num_rows($result) == 0){
                        echo "Survey ID ($survey_id) does not exist.";
                        $continue = false;
                    }
                }
                if ($continue){
                    $survey = mysqli_fetch_assoc($result);
                    $author = $survey["author"];
                    $survey_name = $survey["survey_name"];
                    $survey_instr = $survey["instruction"];
                    $num_questions = $survey["num_questions"];
                    $num_results = $survey["num_results"];
                    $endq_num = $num_questions;
                    $nextq_num = $q_num + 1;
                    if ($num_questions==1) $qplural = "";
                    else $qplural = "s";
                    $surveyprefix = "Your";
                    if ($author !== 'admin' && $_SESSION['username'] === 'admin') $surveyprefix = user_string($author)."'s";
                    //check if current user has access:
                    if ($_SESSION['username'] === $author or $_SESSION['username'] === 'admin'){
                        //user access granted
                        if ($num_results > 0) {
                            //results found
                            
                            //echo chart header
                            echo <<<_END
<h1>Results</h1>
<h2 style='margin-top:-20px;'>$survey_name</h2>

_END;
                            
                            $qnums = "";
                            for ($q=1;$q<=$num_questions;$q++){
                                $qclass = 'qedit'; 
                                if ($q_num == $q) $qclass = 'qedit_curr';
                                $qnums .= "<a class='qnum $qclass' href='?s=$survey_id&q=$q'>$q</a>";
                            }
                            //qpoint classes
                            $qstart_class = "qedit";
                            if ($q_num == 0) $qstart_class = "qedit_curr";
                            $qend_class = "qedit";
                            if ($q_num == $endq_num) $qend_class = "qedit_curr";
                            //qpoint hrefs
                            $qstarthref = "href='?s=$survey_id'";
                            $qendhref = "href='?s=$survey_id&q=$endq_num'";
                            echo <<<_END
<div id="q_select">
<a class='$qstart_class qpointwide' $qstarthref><span>summary</span></a>$qnums
</div>
_END;
                            
                            if ($q_num == 0){
                                //summary of result info & participant info
                                $participants = array();
                                //get participant info
                                $sql = 'SELECT username, email, first_name, last_name, dob, phone_num, country, gender, FLOOR(DATEDIFF(NOW(), dob)/365.25) as "age" FROM users INNER JOIN results USING (username) WHERE survey_id = '.$survey_id.' and iscomplete = true;';
                                $result = mysqli_query($conn, $sql);
                                if ($result === FALSE){
                                    $message = "error encountered getting participant info.<br>".mysqli_error($conn);
                                } else {
                                    for($i=0;$i<mysqli_num_rows($result);$i++){
                                        //add participant to array
                                        $participants[] = mysqli_fetch_assoc($result);
                                    }
                                }
                                //formulate participant username strings
                                $user_str = "";
                                //age data
                                $age_index = array('Under 16', '16-18', '19-24','25-30','31-40','41-50','51-70','Over 70');
                                $age_data = array(0, 0, 0, 0, 0, 0, 0, 0);
                                $age_unspecified = 0; //no. unspecified dobs.
                                //country data
                                $country_index = $COUNTRIES;
                                $country_index[] = ""; // add unspecified
                                $country_label = $country_index;
                                $country_label[count($COUNTRIES)] = "Unspecified";
                                $country_data = array();
                                for ($i=0; $i<count($country_index); $i ++){
                                    $country_data[] = 0; // instatiate empty country data for each country
                                }
                                //gender data
                                $gender_label = array("Male", "Female", "Other", "Unspecified");
                                $gender_index = array("M", "F", "O","");
                                $gender_data = array(0,0,0,0);
                                $max_display_user = 3; //max users displayed in table.
                                for ($i=0;$i<count($participants);$i++){
                                    $user = $participants[$i];
                                    $username = $user["username"];
                                    if ($i < $max_display_user or isset($_GET['all_users'])){
                                        $user_str .= "<a href='../user.php?u=$username' class='user_str'>$username</a>";
                                    } 
                                    else if ($i == $max_display_user){
                                        $user_str .= "<a title='show all users' href='charts.php?s=$survey_id&all_users=true' class='user_str'>•••</a>";
                                    }
                                    //get demographic data
                                    //get age data
                                    $age = $user["age"];
                                    if (is_numeric($age)){
                                        if ($age < 16){
                                            $age_data[0]++;
                                        }
                                        elseif ($age < 18){
                                            $age_data[1]++;
                                        }
                                        elseif ($age < 24){
                                            $age_data[2]++;
                                        }
                                        elseif ($age < 30){
                                            $age_data[3]++;
                                        }
                                        elseif ($age < 40){
                                            $age_data[4]++;
                                        }
                                        elseif ($age < 50){
                                            $age_data[5]++;
                                        }
                                        elseif ($age < 70){
                                            $age_data[6]++;
                                        }
                                        else {
                                            $age_data[7]++;
                                        }
                                    } else $age_unspecified++;
                                    //get country data
                                    $country = $user["country"];
                                    for ($c = 0; $c < count($country_index); $c ++){
                                        if ($country === $country_index[$c]){
                                            $country_data[$c]++;
                                            break;
                                        }
                                    }
                                    //get gender data
                                    for ($g = 0; $g < count($gender_index); $g++){
                                        if ($user["gender"] === $gender_index[$g]) {
                                            $gender_data[$g]++;
                                        }
                                    }
                                }
                                //formulate google DATA_STRING
                                $age_datastring = google_datastring(array_reverse($age_index),array_reverse($age_data)); //reverse so that the smallest is on the bottom.
                                $country_datastring = google_datastring($country_label, $country_data);
                                $gender_datastring = google_datastring($gender_label, $gender_data);
                                //get max age range value
                                $max_age = max($age_data);
                                //
                                if ($age_unspecified > 0) $age_extra = "$age_unspecified unspecified";
                                else $age_extra = "";
                                echo <<<_END
<h3>Summary</h3>
<table id='result_info'>
    <tr><th>Results</th><td>$num_results</td></tr>
    <tr><th>Participants</th><td>
        $user_str
    </td></tr>
</table>
<h3>Demographics</h3>

<div class='chartspace'>
    <span class='charthead'>Age</span>
    <div id="chart_area_age"></div>
    <span class='chartfoot'>$age_extra</span>
</div>
<div class='chartspace'>
    <span class='charthead'>Country</span>
    <div id="chart_area_country"></div>
</div>
<div class='chartspace'>
    <span class='charthead'>Gender</span>
    <div id="chart_area_gender"></div>
</div>
<script type="text/javascript">
    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart']});
    // Set a callback to run when the Google Visualization API is loaded.
    google.charts.setOnLoadCallback(function(){
        //CHART OPTIONS
        var options_age = { 'width':500,
            'height':400,
            'backgroundColor': '#d4dcff',
            maxvalue : '$max_age',
            legend: 'none'
        };
        var options_country = { 'width':500,
            'height':400,
            'backgroundColor': '#d4dcff',
            };
        var options_gender = { 'width':500,
            'height':400,
            'backgroundColor': '#d4dcff',
            colors : ['#5495ff','#fc5858','#da43f2', '#a5a5a5']
        };
        // AGE 
        var age_data = new google.visualization.DataTable();
        age_data.addColumn('string', 'Age');
        age_data.addColumn('number', 'Participants');
        age_data.addRows($age_datastring);
        var ageChart = new google.visualization.BarChart(document.getElementById('chart_area_age'));
        ageChart.draw(age_data, options_age);
        // COUNTRY
        var country_data = new google.visualization.DataTable();
        country_data.addColumn('string', 'Country');
        country_data.addColumn('number', 'Participants');
        country_data.addRows($country_datastring);
        var countryChart = new google.visualization.PieChart(document.getElementById('chart_area_country'));
        countryChart.draw(country_data, options_country);
        // GENDER
        var gender_data = new google.visualization.DataTable();
        gender_data.addColumn('string', 'Country');
        gender_data.addColumn('number', 'Participants');
        gender_data.addRows($gender_datastring);
        var genderChart = new google.visualization.PieChart(document.getElementById('chart_area_gender'));
        genderChart.draw(gender_data, options_gender);
    });
</script>
_END;

                            }
                            else {
                                //QUESTION specific graphing
                                $sql = "SELECT question_text, question_type FROM questions WHERE survey_id = $survey_id and question_num = $q_num;";
                                $result = mysqli_query($conn, $sql);
                                if ($result === FALSE){
                                    echo "error encountered while searching for question.";
                                } else {
                                    $question = mysqli_fetch_assoc($result);
                                    $q_text = $question['question_text'];
                                    $q_type = $question['question_type'];
                                    $resp_col = q_type_col($q_type); //column response is stored in
                                    //get responses
                                    $sql = "SELECT result_id, username,  (SELECT $resp_col FROM responses WHERE result_id = res.result_id AND question_num = $q_num) AS \"response\" FROM results res INNER JOIN users USING (username) WHERE survey_id = $survey_id;";
                                    $result = mysqli_query($conn, $sql);
                                    $responses = array();
                                    if ($result === FALSE){
                                        echo "ERROR in sql.<br>".mysqli_error($conn);
                                    } else {
                                        for ($r = 0; $r < mysqli_num_rows($result); $r ++){
                                            $responses[] = mysqli_fetch_assoc($result);
                                        }
                                    }
                                    $draw_chart = true;
                                    $reverse_values = false;
                                    $unknown_max = false; //is max value unknown?
                                    $chart_type = "BarChart";
                                    $index_type = "range"; //type of indexing. alternative is "assoc"
                                    $ranges = array(); //list of segemntation points [e.g. 0, 25, 50 ,75, 100 to create 4 segments]. 
                                    if (isset($_GET['ctype'])){
                                        //specific chart type requested
                                        $c_type = sanitise($_GET['ctype'],$conn);
                                        if (validateCType($c_type) === ""){
                                            //TODO
                                        } else echo "invalid ctype in GET.";
                                        
                                    }
                                    else {
                                        //display OVERALL chart
                                        //response data indices
                                        $resp_index = array();
                                        $resp_unspecified = 0;
                                        switch ($q_type){
                                            case "percentageinput":
                                            $resp_index = array("0-25%", "25-50%", "50-75%", "75-100%");
                                            $ranges = array(0, 25, 50, 75, 100);
                                            $chart_type = "ColumnChart";
                                            break;
                                            case "yesno":
                                            $resp_index = array("Yes", "No");
                                            $chart_type = "PieChart";
                                            $index_type = "assoc";
                                            break;
                                            case "yesnounsure":
                                            $resp_index = array("Yes", "No", "Unsure");
                                            $chart_type = "PieChart";
                                            $index_type = "assoc";
                                            break;
                                            case "rate10":
                                            $resp_index = array("0-1", "2-3", "4-5","6-7", "8-9", "10");
                                            $ranges = array(0, 2, 4, 6, 8, 10, 11); // 11 will only return 10
                                            $reverse_values = true; 
                                            break;
                                            case "likert":
                                            $resp_index = array("S. Disagree", "Disgree", "Neither", "Agree", "S. Agree");
                                            $chart_type = "PieChart";
                                            $index_type = "assoc";
                                            break;
                                            default: case "textinput": case "numberinput":
                                                $draw_chart = false;
                                            break;
                                        }
                                        if ($draw_chart){
                                            //get initial resp_data
                                            
                                            $resp_data = array();
                                            for ($i = 0; $i < count($resp_index); $i ++){
                                                $resp_data[] = 0;
                                            }
                                            for ($r=0;$r<count($responses);$r++){
                                                $resp = $responses[$r];
                                                $response = ucwords(strtolower($resp["response"]));
                                                if ($response === "" or is_null($response) or $response == -1) $resp_unspecified++;
                                                else {
                                                    if ($response === "" and $index_type === "range") $response = null; //nullify answers that contain nothing.
                                                    for ($i = 0; $i < count($resp_index); $i ++){
                                                        switch ($index_type){
                                                            case "assoc":
                                                            //associative indexing. check for string equality, such as "Yes" or "Disagree"
                                                            if ($response === $resp_index[$i]){
                                                                $resp_data[$i]++;
                                                                break 2; //exit for
                                                            } break;
                                                            case "range":
                                                            //range checking. check for range inclusion, such as 24%->0-25%
                                                            if (count($ranges) == (count($resp_index) + 1) ){
                                                                if (is_numeric($response)){
                                                                    if ($response >= $ranges[0] && $response < $ranges[$i+1]){
                                                                        $resp_data[$i]++; //increment relevant value
                                                                        break 2; //exit for
                                                                        
                                                                    }
                                                                    else {
                                                                    }
                                                                }
                                                            } else echo "ERROR: invalid resp_index : range comparison."; 
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                            if ($reverse_values) $response_datastring = google_datastring(array_reverse($resp_index), array_reverse($resp_data));
                                            else $response_datastring = google_datastring($resp_index, $resp_data);
                                            $max_resp = max($resp_data);
                                        }
                                    }

                                    //TODO
                                    //USE GET REQUEST TO CHANGE CHART

                                    if ($chart_type === "BarChart" or $chart_type === "ColumnChart") $legend = "legend : 'none',";
                                    else $legend = "";

                                    switch ($q_type){
                                        case "yesno":
                                            $colors = "colors : ['#2dbc44','#ff3030']"; break;
                                        case "yesnounsure":
                                            $colors = "colors : ['#2dbc44','#ff3030', '#ffb93a']"; break;
                                        default: $colors = ""; break;
                                    }
                                    $extra_option = $legend.$colors;

                                    if ($resp_unspecified > 0) $resp_extra = "$resp_unspecified unspecified";
                                    else $resp_extra = "";
                                    echo "<h3>Question Results</h3><h4>$q_text</h4><br>";
                                    if ($draw_chart) echo <<<_END
<div class='chartspace'>
    <span class='charthead'>Responses</span>
    <div id="chart_area_response"></div>
    <span class='chartfoot'>$resp_extra</span>
</div>
<script type="text/javascript">
    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart']});
    // Set a callback to run when the Google Visualization API is loaded.
    google.charts.setOnLoadCallback(function(){
        //CHART OPTIONS
        var options = { 'width':500,
            'height':400,
            'backgroundColor': '#d4dcff',
            maxvalue : '$max_resp',
            $extra_option
        };
        var response_data = new google.visualization.DataTable();
        response_data.addColumn('string', 'Response');
        response_data.addColumn('number', 'Participants');
        response_data.addRows($response_datastring);
        var respChart = new google.visualization.$chart_type(document.getElementById('chart_area_response'));
        respChart.draw(response_data, options);
    });
</script>
_END;
                                else echo "This question cannot be charted.";
                                }
                                
                            }
                        
                            echo "<br><div id='surveyfoot'>";
                            //previous anchor
                            $prevq_num = $q_num - 1;
                            if ($q_num == 0) $prevq_btn = "<a id='prevq' class='qchange'></a>";
                            else if ($q_num == 1) $prevq_btn = "<a id='prevq' class='qchange' href='?s=$survey_id'></a>";
                            else $prevq_btn = "<a id='prevq' class='qchange' href='?s=$survey_id&q=$prevq_num'></a>";
                            //download csv button
                            if ($q_num == 0){
                                //get RESULT data
                                $csv_button = "<a class='next'target='_blank' style='width:200px;' href='results.php?s=$survey_id' title='download a .csv file containing the results of this survey (excludes question responses)'>Download CSV</a>";
                            }
                            else {
                                //get RESPONSE data
                                $csv_button = "<a class='next'target='_blank' style='width:200px;' href='results.php?s=$survey_id&q=$q_num' title='download a .csv file containing the responses to this question'>Download CSV</a>";
                            }
                            //next anchor
                            if ($q_num == $endq_num) $nextq_btn = "<a id='nextq' class='qchange'></a>";
                            else $nextq_btn = "<a id='nextq' class='qchange' href='?s=$survey_id&q=$nextq_num'></a>";
                            //output menu
                            echo $prevq_btn.$csv_button.$nextq_btn;
                            echo "</div>";
                            echo "$message";
                        } else echo "This survey has no results.";
                        
                    } 
                    else {
                        echo "You do not have permission to access this page.";
                    }
                } else {
                }
            }
        }
        else echo "invalid GET.";
    }
    else {
        //no get request. rip.
        echo "Whoops! That isn't right. <br>To edit surveys use the ?s=[survey id] suffix.";
    }
}

// finish off the HTML for this page:
require_once "footer.php";

?>