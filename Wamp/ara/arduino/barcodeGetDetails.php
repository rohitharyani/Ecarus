<?php

	require_once '../arduinoIncludes/arduinoDbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='GET'){

		if(isset($_GET['barcode'])){
		
			$db = new DbOperation();

			if($_GET['barcode'][0] < 6)
				$result = $db->barcodeGetProduct($_GET['barcode']);	
			else
				$result = $db->userEntersMall($_GET['barcode']);	
		
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid request";
			echo json_encode($response);
		}
	}

?>	
