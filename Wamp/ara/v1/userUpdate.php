<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();

	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(
			isset($_POST['name']) and isset($_POST['email']) and isset($_POST['password']) and isset($_POST['phone'])  
		){
			//operate the data further

			$db = new DbOperation();

			//parse values to forgotPassword function
			$result = $db->userUpdate(
				$_POST['name'],
				$_POST['email'],
				$_POST['password'],
				$_POST['phone']
				);

			//Error checking

			if($result == 1){
				$response['error'] = false;
				$response['message'] = "Details updated successfully, please login again!";
			}elseif($result == 2){
				$response['error'] = true;
				$response['message'] = "ERROR Please try again";
			}elseif($result == 0){
				$response['error'] = true;
				$response['message'] = "Email id already in use, please choose a different Email id.";
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