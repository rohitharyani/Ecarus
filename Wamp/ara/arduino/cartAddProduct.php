<?php

	require_once '../arduinoIncludes/arduinoDbOperations.php';

	$response = array();
	if($_SERVER['REQUEST_METHOD']=='GET'){

		if(isset($_GET['phone']) and isset($_GET['productId'])){
		
			$db = new DbOperation();

			$result = $db->cartAddProduct($_GET['phone'], $_GET['productId']);
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid request";
			echo json_encode($response);
		}
	}


?>	