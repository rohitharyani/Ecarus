<?php

	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='GET'){

		if(isset($_GET['phone']) and isset($_GET['products'])){

			listRemoveProduct($_GET['phone'], $_GET['products']);

		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid credentials";
			echo json_encode($response);
		}
	}


	function listRemoveProduct($phone, $products){

		$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

	    //fetch table rows from mysql db
	    $listMode = $phone."_listmode";
	    $sql = "DELETE FROM `$listMode` WHERE `$listMode`.`products` = '$products'";
	    if(mysqli_query($connection, $sql)){
	    	$response['error'] = false;
	    	echo json_encode($response);
	    }
	    else{
	    	$response['error'] = true;
	    	echo json_encode($response);
	    }
	}