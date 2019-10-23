Frank Dippnall's
Survey Station

GitHub release v1.0
Note: 	this is a university project for the year of 2018. 
	This site uses no personal data and is not connected to any server.
	To run this you will need an Apache/MySQL server, or, like the readme below assumes, use XAMPP (google it) to serve the site on localhost.

SETUP:
 *using XAMPP* 
1) Place root folder (survey_website) in htdocs of XAMPP
2) Start the XAMPP server, ensuring both Apache and MySQL services are online.
3) Navigate to the landing page at " localhost/survey_website/ "
4) Select 'Create Data' to initialise the test data
5) Once the data is loaded click the link at the bottom of the log to go to the sign in page.

NOTE:   To sign in you will need a valid username/password combination. you can grab these in plaintext by referring to the create_data.php file.
        You can sign up a new user instead if you want.
        To use the ADMIN features, you will need to enter the admin details which are:
            username : "admin"
            password : "secret"

        To use this site, you should have JavaScript ENABLED in your browser. If you disable is the site will run less efficiently, 
        and you may have to click more times to perform an operation due to the disabling of js redirects. Visual effects will also be limited.

OPTIMISED FOR CHROME
    ( but feel free to use any browser you want :) )

----+----------------------------------------------------------------+----------------------------------------------------------------------------------------
    |   DOCUMENTATION                                                |
    +----------------------------------------------------------------+                                        
    |   EVERY file in the survey_website directory is listed here.   |
    |   This includes .php files, and also site assets such as:      |
    |       - JavaScript files                                       |
    |       - CSS                                                    |
    |       - Images                                                 |
    |   To find a specific file, use CTRL+F to find its file name.   |
----+----------------------------------------------------------------+----------------------------------------------------------------------------------------


/readme.txt 
    - this readme!

/                                           [ /index.php         ]
    - initialises the website.
        - "Log In":
            - links to the [sign_out.php] page. 
              Use this to:
                - navigate to the log-in page
        - "Create Data":
            - links to the [create_data.php] page. 
              Use this to:
                - setup the database
                - reinitialise the database with fresh values
                - navigate to the log-in page

/about.php
    - displays basic information about the site
    - includes reference to icons8.com for the icons used in the site.

/account_set.php
    - allows the Admin to update the account details of any user.
    - username of user to edit specified with 'username' in GET

/account.php 
    - allows the user to update the account details of their account.

/admin.php
    - allows the Admin to view the account details of all users in a table format.
    - clicking any row (user) takes the Admin to the [account_set.php] page for that user (using GET 'username')

/change_password.php
    - allows the current user to change their password
        - this requires the user to input the correct current password, along with the new password TWICE.
            - the password change is only performed if the current password is correct and both new passwords match.

/competitors.php
    - displays the Competitor Analysis for the Design & Analysis section of the assignment.

/create_data.php
    - reinitialises the database with the sample data provided.
        - sample data includes:
            * 8 users
            * 3 surveys
            * 9 questions
            * 9 results
            * 38 responses
            - NOTE: most responses are for the survey 1 'Global Warming Questionnaire'.
                    to test [charts.php] for the results analysis and charting, use this 
                    survey as it has the most realistic statistical distribution of all the surveys.

/credentials.php
    - contains the database details for the site.
        - $dbhost: URL of host of DB.
        - $dbuser: user to access DB.
        - $dbpass: password to access DB; for $dbuser = 'root', this is EMPTY.
        - $dbname: name of the DB.
    - database run using MySQL

/footer.php
    - outputs the main site footer.
        * includes Unit Code & Copyright Notice
        * runs the [main.js] client-side code.
    - closes the <body> and <html> tags to end the page.

/header.php
    - runs the [credentials.php] and [helper.php] files
        - this means any page can access the variables / functions in these files (provided it indludes [header.php])
    - starts the session
        - used to store login statuses.
    - outputs the main site header.
        * includes the <!DOCTYPE html> to specify for HTTP requests
        * opens the <html> and <body> tags
        * sets the website default Title ("Survey Website")
        * links to the main Stylesheet [survey.css]
        * sets the <meta name="viewport"> tag
            - this is important for visual consistency over different screen sizes
        * links to a JQuery API
            - hosted by Google
        * displays the main site Heading "Survey Station"
        * displays the <nav> menu
            - the buttons displayed depend on the logged in state
            - some buttons display differently / are hidden when the user is the Admin
        * outputs the Alert Box for an Alert GET.
            - see file for specifics of each Alert GET.

/helper.php
    - includes critical functions for across-site use.
    - includes validation functions.
        - for details, view file.
    - Description of some important non-validation functions:

        sanitise($str, $connection)
            - sanitises the given string to make it safe for use in PHP and MySQL.
            - Parameters
                $str : the string to sanitise
                $connection : the connection on which to escape bad characters
            - Returns
                the sanitised version of the input string
        
        salt($string)
            - arbitrarily "salts" the given string.
            - Parameters
                $string : the string to salt.
            - Returns   
                the "salted" string
            * this is useful to mask passwords so, when hashed, 
              they are almost impossible to crack without explicit knowledge 
              of the salt itself, which is never transferred across HTTP.
        
        create_table($result, [$destination, $field])
            - constructs an HTML <table> for the given $result.
            - Parameters
                $result : the result to extract data from. Must be (mysqli_result)
                $destination : the destination for a .clickable table GET after row click. (Optional)
                $field : the GET key for a .clickable table after row click (Optional)
            - Returns
                - if $destination and $field are supplied, outputs a <table class=".clickable">
                    - this contains the extra attributes:
                        - data-destination
                        - data-field
                    which contain the relevant data. This is then used in [main.js] for table click handling.
                - if only $result supplied, outputs a normal <table>

            user_string($username)
                - returns a <span> tag containing the given username.
                    - normal users have a class="user" attribute and the Admin has a class="admin".
                - Parameters
                    $username : the username for the <span>
                - Returns
                    the generated <span> with appropriate class.
    
            alert_box($message)
                - returns an Alert Box containing the message specified.
                    - the Alert Box will appear in the bottom right corner of the screen
                    - uses JQuery to fade out the button when the "X" is clicked
                - Parameters
                    $message : the message to be displayed, in HTML (can include tags)
                - Returns 
                    the Alert Box.
                
            setDefaultSelect($select, $matchvalue, [$ucwords])
                - adds the selected="selected" attribute to the <option> in the given <select> which matches the match value specified.
                    - if match value is not found, returns the given <select>
                - Parameters
                    $select : the <select> tag to update
                    $matchvalue : the value to find. this corresponds to the value='' attribute in the <option> tags
                    $ucwords : (boolean) are the values written in ucwords (first letter uppercase)?
                        - if omitted defaults to false.
                - Returns
                    - the new <select> with the updated selected="selected" <option> tag
            
            setDefaultRadio($radio, $matchvalue, [$ucwords])
                - equivalent to setDefaultSelect() except it works with <input type="radio"> instead of <option>
            
            q_type_col($q_type)
                - returns the column that the responses to the given question type should be stored in.
                - Parameters
                    $q_type : the Question Type to look for.
                - Returns
                    a column name if $q_type is valid, else an EMPTY string.
            
            format_tel($tel)
                - adds a standardised spacing to the given phone number.
                - Parameters
                    $tel : the telephone number to format
                - Returns
                    the formatted number.
                * this function adds a space after the 4th and 7th digits, so 
                    "12345678910" -> "1234 567 8910"
                
            google_datastring($indices, $data)
                - returns a JS array that Google will recognise as a set of key/value pairs for Google Charts
                - Parameters
                    $indices : an array of indices (indexes) that store the KEYS (e.g. UK, USA, Germany)
                    $data : an array of integers that store the VALUES (e.g. 2, 4, 1)
                - Returns
                    the complete JS array for the key/values specified.
            
            create_options($options, $labels, [$rather_not_say])
                - returns an HTML <select> list populated with <option> tags
                - Parameters
                    $options : an array of the options. this corresponds to the value="" attribute of each <option> tag
                    $labels : an array of the labels. this corresponds to what is written in the <option> tag
                    $rather_not_say : (boolean) include in the option list an empty option (so the user can choose to not answer the question)
                - Returns
                    the complete <select> tag constructed, or if the parameters were invalid, an error message.
                
        - also includes some Global Arrays:
            - $COUNTRIES : array of all the valid countries supported.
            - $COUNTRY_LABELS : array of all the valid countries' display names. e.g. for UK, "United Kingdom (UK)"
        
/manage_account.php
    - allows the user to perform actions relevant to their current login
        - allows the user to "Sign Out"
            - this links to [sign_out.php]
        - allows the user to "Change Password"
            - this links to [change_password.php]
        - allows the user to "Forget Me" / "Remember Me"
            - if "Remember Me" is clicked:
                - adds the "r_username" cookie to the client
                    - this contains the username of the logged in user.
                    - when the user signs out, this is displayed in the Username text box
            - if "Forget Me" is clicked:
                - removes the cookie "r_username" from the client.
            
/public_surveys.php
    - displays a compiled list of all surveys that are set to Public   
        - this includes the survey data itself
        - and an indicator displaying if the user has completed a response to that survey.
    - the user can use "Hide Completed" to exclude all surveys that they have completed a submission for. 

/sign_in.php
    - allows the user to sign in to the site.
        - user must enter Username and Password fields
            - if the Username and Password given are valid, logs the user in as that username.
                - this then redirects the user to [manage_accout.php] using JS.
            - if they are not valid, rejects the login request.
        - user can click "Remember Me" to add the "r_username" cookie to the client to rememeber the username.
    - if the "r_username" cookie is set, inserts the remembered username into the Username field.

/sign_out.php
    - linking to this page immediately signs the user out of the site
        - it then auto redirects the user to [sign_in.php] using JS.

/sign_up.php
    - allows the user to sign up to the site.
        - user must enter all required fields
        - non-required fields are optional.
        - on submit, all the inputs are validated
            - this includes checking if the username given is already taken.
        - if the data is valid, the request is approved.
            - the user is then redirected to the[sign_in.php] page via JS
        - if the data is invalid, the request is rejected.
            - field-specific error messages are displayed next to the relevant fields
            - main error message is displayed below the submit button.

/surveys_manage.php
    - displays all of the current users surveys
        - if the user is Admin, instead displays all surveys, along with the author's name.
        - the number of questions for that survey is shown
        - the number of submitted results for that survey is shown
    - for each survey, user can click buttons to perform tasks:
        - "URL" 
            simple hyperlink to the survey page in [/surveys/] (uses 's' in GET)
        - "view results" 
            display results and analysis info in [/surveys/charts.php] (uses 's' in GET)
        - "edit survey"
            edit survey info & questions in [/surveys/edit.php] (uses 's' in GET)
        - Padlock:
            - displays the ispublic state of each survey. 
                - an open padlock indicates a Public survey
                - a closed padlock indicates a Private survey
            - clicking this toggles this state.
        - "delete survey"
            deletes the current survey. (requires confirmation)
    - allows the user to "Create a Survey" in their name
        - this links to [/surveys/create.php]
    
/user.php
    - displays the profile data for a user.
        - information displayed in stylised format (via [survey.css])
    - user specified with 'u' in GET.
    - this page can be viewed by any user that is logged in. 

/surveys/                                   [ /surveys/index.php ]
    - allows a user to view a survey
        - survey specified with 's' in GET.
        - if the user has completed a submission for the survey:
            * the user cannot submit the survey.
            * displays a message informing the user that they cannot submit the survey.
        - if 'q' NOT in GET:
            * displays the author of the survey.
            * displays the instructions for the survey.
        - if 'q' IS in GET and is a question ('q' <= number of questions):
            * displays the question text
            - if the question is marked as "required":
                * displays a message "Required".
            * displays the input form for the question
                - form inputs depend on the question type
                - some question types are heavily stylised, especially any type that uses checkboxes.
        - if 'q' IS in GET and is not a question:
            * displays the "end of survey" message
            - if the user can submit the survey: 
                * allows the user to click "submit"
                    - this marks their result for this survey as "completed"
            - if the user can't submit the survey:
                * displays an "exit" button
                    - clicking this returns the user to the [public_surveys.php] page.

/surveys/charts.php
    - displays graphical analysis of results for a survey
        - survey specified with 's' in GET.
        - users can only access surveys that they have created. (admin can see all)
        - charts are supplied via the Google Charts API
        - if 'q' NOT given in GET:
            *outputs a "Summary" of submitted results including the number of results and participant usernames.
                - Clicking the names takes you to the [user.php] file for that user.
            *outputs three "Demographics" charts:
                - Age - a breakdown of the age range of participants
                - Country - a breakdown of the country of participants
                - Gender - a breakdown of the gender of participants
        - if 'q' IS given in GET:
            *outputs the specified question's text
            *outputs a "Responses" chart: a breakdown of the responses to that question.
    - allows user to "Download CSV"
        - if 'q' NOT given in GET (i.e. the page is the "summary/demographics" page):
            * links to [results.php] and requests RESULT data (NO 'q' in GET)
        - if 'q' IS given in GET (i.e. the page is a question analysis page):
            * links to [results.php] and requests RESPONSE data ('q' in GET = current question number)

/surveys/create.php
    - creates a new survey
        - user inputs the Survey Name
    - takes the user back to the [surveys_manage.php] page containing the new survey.

/surveys/edit.php
    - allows a user to edit a survey
        - survey specified with 's' in GET.
        - users can only access surveys that they have created. (admin can see all)
        - allows user to "Add Question"
            - this takes them to [question.php] and sets the page up to create a new question (using "&addq=true")
        - if 'q' NOT given in GET:
            * allows user to edit the Instructions of the survey.
                -"Auto Generate" button provides an automatic vague instruction.
        - if 'q' IS given in GET:
            * displays the question text for the relevant question
            * allows user to edit the Question
                - this takes them to [question.php] and sets the page up to edit an existing Question
            * allows user to delete the question
                - this takes them to [question.php] and sets the page up to delete an existing Question
                - requires Confirmation, to reduce chance of accidental deletion.
                    - Question Deletion requires a 4-step process, explained in the php comments

/surveys/footer.php
    - this file contains the "/surveys" subdomain footer.
        - it differs from [/footer.php] in that the link to [main.js] is corrected for the subdomain.
          other than that it is identical.

/surveys/header.php
    - this file contains the "/surveys" subdomain header.
    - it differs from [/header.php]:
        - the <nav> links are adjusted for the subdomain
        - this header includes the Google Charts referenced library for [charts.php]
        - Alert cases ONLY catch Alerts for the "/surveys" subdomain
      other than that it is identical.

/surveys/question.php
    - survey must be specified with 's' in GET.
    - allows a user to manipulate the questions of the current survey
        - if 'addq' IS in GET:
            * allows user to create a new question.
                - user must input new question data:
                    - question text
                    - question type
                    - is required? (boolean)
                - on success returns the user to the [edit.php] page for the NEW question.
        - if 'addq' NOT in GET:
            * allows user to edit an existing question.
                - question must be specified with 'q' in GET.
                - user can change question data:
                    - question text
                    - question type
                    - is required? (boolean)
                - on success returns the user to the [edit.php] page for the EDITED question.

/surveys/results.php
    - survey must be specified with 's' in GET.
    - delivers a .CSV file containing result/response data about the survey
        - if 'q' NOT in GET:
            * constructs a .CSV containing data from the "users" table, specifically the:
                - username
                - email
                - first_name
                - last_name
                - dob
                - phone_num
                - country 
                - gender
                - age (calculated)
            of each participant of the survey.
        - if 'q' IS in GET:
            * constructs a .CSV containing the 
                - username
                - response 
            of each participant to the survey (that responded to this question), for this question.
        - .CSV file is then dispatched via "Content-Disposition: attachment"
            - caching is disabled because survey response data can change rapidly, 
              so the browser will always get the latest version.
            - the file's first row has headings matching the column keys from SQL

/css/survey.css 
    - contains all of the styling for the entire website (excluding jQuery dynamic styling)
        Lots of stuff here, but things of note:
        - hides default checkboxes for .radio and .check_box styles.
        - restyles the .rate10 inputs to have a '0' on the left and a '10' on the right.

/js/main.js
    - adds minor appearance changes
        - resets validation error messages when a form is submitted
        - fades in the Alert Box
        - controls styling of the .data_table table (SQL output table)
            * hovering over rows highlights the entire row
            * hovering over column headers highlights the entire column.
    - operates some important functionality
        - Clickable Tables (.clickable)
        - Expandable Images (.expandable & .expanded)
        - Stylized Checkboxes (.radio & .check_box)

/img/chart.png
    - icon of a black bar chart.
        - used for the View Results button

/img/chart2.png
    - icon of a white bar chart.
        - used for the View Results column heading
    
/img/checked.png
    - icon of a white check mark.
        - used for the Stylized Checkboxes (checked)

/img/delete.png
    - icon of a black waste bin.
        - used for the Delete Survey button

/img/delete.png
    - icon of a white waste bin.
        - used for the Delete Survey column heading

/img/edit.png
    - icon of a black pencil.
        - used for the Edit Survey button

/img/edit2.png
    - icon of a white pencil.
        - used for the Edit Survey column heading

/img/googleforms1.png
    - image of a page in Google Forms
        - used in the Competitor Analysis page   

/img/greencheck.png
    - icon of a green check mark.
        - used for the User Completed Survey? indicator (yes)

/img/prev.png
    - icon of a white right arrow.
        - used for the Next Question buttons

/img/notcheck.png
    - icon of a red cross mark.
        - used for the User Completed Survey? indicator (no)

/img/prev.png
    - icon of a white left arrow.
        - used for the Previous Question buttons

/img/private.png
    - icon of a black closed padlock.
        - used for the Set Survey to Public button

/img/private2.png
    - icon of a white closed padlock.
        - used for the Set Survey to Public/Private column header

/img/public.png
    - icon of a black open padlock.
        - used for the Set Survey to Private button

/img/public2.png
    - icon of a white open padlock.
        - not currently used.

/img/star.png
    - icon of a yellow 5-pointed star.
        - used for the star rating in the Competitor Analysis page

/img/surveymonkey1.png
    - image of page in Survey Monkey.
        - used in the Competitor Analysis

/img/surveyplanet.png
    - image of page in Survey Planet.
        - used in the Competitor Analysis