<?php
/* This code is used for connecting facebook app to the databse
 * and storing info into our database */
 
	session_start();

	//connection
	$host = 'localhost'; // hostname OR IP
	$username = 'facebook' ;//username
	$pass = 'facebook' ; //password
	$dbname = 'facebook'; // database Name
	$conn = mysql_connect($host, $username, $pass) or die(mysql_error());
    if ($conn)
    {
        mysql_select_db($dbname) or die(mysql_error());
    }
    else
    {
       echo 'Connection failed.';
    }
	
	//all data fields list here
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$gender = $_POST['gender'];
	$birthday = $_POST['birthday'];
	$email = $_POST ['email'];

	//insert all data into database
	$query = mysql_query("INSERT INTO app_data (first_name, last_name, gender, birthday, email) VALUES ('$first_name','$last_name', '$gender', '$birthday', '$email')") or die(mysql_error());
	
	//check the process
	if($query){
		echo 'All info has been sent successfully! Thank you!';
	} else {
		echo 'error adding to database';
	}
?>