<?php

	require_once '../arduinoIncludes/arduinoDbOperations.php';

	$response = array();
	if($_SERVER['REQUEST_METHOD']=='GET'){

		
			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
				$sql1 = "UPDATE `users` SET `mall` = '0' WHERE `users`.`phone` = '7875987888'";
	    		$result1 = mysqli_query($connection, $sql1) or die("Error loading values1 " . mysqli_error($connection));
	    		$sql = "DROP TABLE 7875987888_cartmode";
	    		$result = mysqli_query($connection, $sql) or die("Error loading values1 " . mysqli_error($connection));
	    		
		
	}


?>	