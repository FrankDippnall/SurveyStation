<?php

// execute the header script:
require_once "header.php";

echo <<<_END
<h2>About Survey Station</h2>
<p>
This website is my submission for the PHP focused assessment section of the Web Design & Development Unit.
</p>
<p>
The Survey Station site allows you to:
<ul class='thin'>
    <li>sign up using a form, including details such as username and password,</li>
    <li>sign in using a valid username and password pair,</li>
    <li>create new surveys under your name, and view your own surveys,</li>
    <li>view surveys from other users and submit responses to them.</li>
</ul>
</p>
<p style='margin-top:40px;'>
Featuring awesome icons by <a href='http://icons8.com'>icons8.com</a>.
</p>
_END;

// finish of the HTML for this page:
require_once "footer.php";

?>