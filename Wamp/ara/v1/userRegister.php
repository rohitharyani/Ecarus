<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();

	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(
			isset($_POST['name']) and isset($_POST['email']) and isset($_POST['phone']) and isset($_POST['password'])  
		){
			//operate the data further

			$db = new DbOperation();

			//parse values to createUser function
			$result = $db->userRegister(
				$_POST['name'],
				$_POST['email'],
				$_POST['phone'],
				$_POST['password']
				);

			//Error checking

			if($result == 1){
				$response['error'] = false;
				$response['message'] = "User registered successfully";
			}elseif($result == 2){
				$response['error'] = true;
				$response['message'] = "ERROR Please try again";
			}elseif($result == 3){
				$response['error'] = true;
				$response['message'] = "User already exists, please choose a different email id and phone number.";
			}

		}else{
			$response['error'] = true;
			$response['message'] = "Required fields are missing";
		}

	}else{
		$response['error'] = true;
		$response['message'] = "Invalid Request";
	}

	echo json_encode($response);	//provide output in JSON format

?>