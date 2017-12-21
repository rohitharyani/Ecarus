<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(isset($_POST['phone'])){

			$db = new DbOperation();

			$db->recommenderSimilarProducts($_POST['phone']);
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid credentials";
			echo json_encode($response);
		}
	}

?>