<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();

	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(
			isset($_POST['phone']) and isset($_POST['password']) and isset($_POST['otp'])  
		){
			//operate the data further

			$db = new DbOperation();

			//parse values to forgotPassword function
			$result = $db->forgotPassword(
				$_POST['phone'],
				$_POST['password'],
				$_POST['otp']
				);

			//Error checking

			if($result == 1){
				$response['error'] = false;
				$response['message'] = "Password changed successfully";
			}elseif($result == 2){
				$response['error'] = true;
				$response['message'] = "ERROR Please try again";
			}elseif($result == 0){
				$response['error'] = true;
				$response['message'] = "Authentication error! Please enter a valid OTP.";
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