<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();

	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(isset($_POST['phone']) and isset($_POST['password'])){

			$db = new DbOperation();

			if($db->userLogin($_POST['phone'], $_POST['password'])){
				$user = $db->getUserByPhone($_POST['phone']);
				$response['error'] = false;
				$response['name'] = $user['name'];
				$response['email'] = $user['email'];
				$response['phone'] = $user['phone'];
				$response['password'] = base64_decode($user['password']);
			}else{
				$response['error'] = true;
				$response['message'] = "Invalid phone number or password";	
			}

		}else{
			$response['error'] = true;
			$response['message'] = "Required fields are missing";
		}


	}

	echo json_encode($response);
?>