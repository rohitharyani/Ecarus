<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(isset($_POST['phone']) and isset($_POST['products'])){

			$db = new DbOperation();
			$result = $db-> shoppingRemoveItems($_POST['phone'], $_POST['products']);
			$result1 = $db->shoppingRemoveItems1($_POST['phone'], $_POST['products']);			
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid request";
			echo json_encode($response);
		}
	}

?>