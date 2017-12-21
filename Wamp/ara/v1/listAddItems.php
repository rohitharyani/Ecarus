<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();

	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(
			isset($_POST['phone']) and isset($_POST['category']) and isset($_POST['productId']) and isset($_POST['date']) and isset($_POST['products']) and isset($_POST['cost']) and isset($_POST['weight']) and isset($_POST['image']) and isset($_POST['mfgDate']) and isset($_POST['expDate'])
		){
			//operate the data further

			$db = new DbOperation();

			//parse values to createUser function
			$result = $db->listAddItems(
				$_POST['phone'],
				$_POST['category'],
				$_POST['productId'],
				$_POST['date'],
				$_POST['products'],
				$_POST['cost'],
				$_POST['weight'],
				$_POST['image'],
				$_POST['mfgDate'],
				$_POST['expDate']
				);

			//Error checking

			if($result == 1){
				$response['error'] = false;
				$response['message'] = "Added successfully!";
			}elseif($result == 2){
				$response['error'] = true;
				$response['message'] = "Cannot be added twice!";
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