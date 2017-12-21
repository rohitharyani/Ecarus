<?php
	//Call service 
	require_once '../includes/DbOperations.php';
	//Check method 
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){
		//Check data set
		if(isset($_POST['orderId'])){

			//Call function
			$db = new DbOperation();
			$result = $db->employeeCheckout($_POST['orderId']);			
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid request";
			echo json_encode($response);
		}
	}



?>