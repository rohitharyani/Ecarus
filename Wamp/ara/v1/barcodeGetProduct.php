<?php
	//Call service
	require_once '../includes/DbOperations.php';
	//Check method 
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){
		//Check Dataset
		if(isset($_POST['barcode'])){

			$db = new DbOperation();


			$result = $db->barcodeGetProduct($_POST['barcode']);
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid request";
			echo json_encode($response);
		}
	}
?>	
