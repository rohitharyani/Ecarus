<?php

	require_once '../arduinoIncludes/arduinoDbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='GET'){

		if(isset($_GET['phone'])){

			$db = new DbOperation();

			$db->listRemainingProducts($_GET['phone']);
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid credentials";
			echo json_encode($response);
		}
	}


	


				
