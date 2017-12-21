<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(isset($_POST['phone'])){

			listEmptyItems($_POST['phone']);

		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid credentials";
			echo json_encode($response);
		}
	}


	function listEmptyItems($phone){

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