<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(isset($_POST['barcode'])){

			$db = new DbOperation();
			$result = $db->employeeOrderList($_POST['barcode']);			
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid request";
			echo json_encode($response);
		}
	}

?>