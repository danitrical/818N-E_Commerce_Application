<?php 
$dbHost = $_ENV['DB_HOST'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];
$dbName = $_ENV['DB_NAME'];
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if(!$con){
    die(mysqli_error($con));
}

?>