<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(isset($_POST['phone'])){

			$db = new DbOperation();
			$result = $db->previousOrderAvailable($_POST['phone']);			
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid request";
			echo json_encode($response);
		}
	}

?>