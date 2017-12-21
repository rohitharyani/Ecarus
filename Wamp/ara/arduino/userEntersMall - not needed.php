<?php

	require_once '../arduinoIncludes/arduinoDbOperations.php';

	$response = array();
	if($_SERVER['REQUEST_METHOD']=='GET'){

		if(isset($_GET['phone'])){
		
			$db = new DbOperation();

			$result = $db->userEntersMall($_GET['phone']);
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid request";
			echo json_encode($response);
		}
	}

	
?>	