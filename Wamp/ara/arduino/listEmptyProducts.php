<?php

	require_once '../arduinoIncludes/arduinoDbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='GET'){

		if(isset($_GET['phone'])){

			listEmptyProducts($_GET['phone']);

		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid credentials";
			echo json_encode($response);
		}
	}


	function listEmptyProducts($phone){

		$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

	    //fetch table rows from mysql db
	    $listMode = $phone."_listmode";
	    $sql = "TRUNCATE $listMode";
	    if(mysqli_query($connection, $sql)){
	    	$response['error'] = false;
	    	echo json_encode($response);
	    }
	    else{
	    	$response['error'] = true;
	    	echo json_encode($response);
	    }
	}