<?php

//DATABASE INFO
$dbhost = "localhost";
$dbname = "database_main";
$dblogin = "database_user";
$dbpass = "password";


//CONNECT TO DATABASE
$z = new mysqli($dbhost, $dblogin, $dbpass, $dbname);
$tableprefix = "prefix_";

?>