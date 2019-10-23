<?php

echo <<<_END

<!DOCTYPE html>
<html>
<head>
<title>Survey Website Setup</title>
</head>
<body>
<style>
body{
    text-align:center;
}
div{
    padding:20% 10%;
    font-size:24px;
    font-family:"lucida sans";
}
div a{
    display:inline-block;
    width:200px;
    height:30px;
    line-height:30px;
    background-color:#6699ff;
    text-decoration:none;
    margin:10px;
    padding:5px;
    color:white;
}
</style>
<div>
Welcome to the Survey Station. Choose an option:<br>
<a href="sign_out.php">Log In</a><a href="create_data.php">Create Data</a>
</div>

_END;

?>