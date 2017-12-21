<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();

	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(
			isset($_POST['phone'])  
		){
			//operate the data further

			$db = new DbOperation();

			//parse values to createUser function
			$result = $db->userPhoneExists(
				$_POST['phone']
				);

			//Error checking

			if($result == 1){
				$response['error'] = false;
				$response['message'] = "User Exists";
			}elseif($result == 2){
				$response['error'] = true;
				$response['message'] = "User does not exist! Please enter a valid phone number.";
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