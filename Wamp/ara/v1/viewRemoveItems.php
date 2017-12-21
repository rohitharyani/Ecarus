<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(isset($_POST['phone']) and isset($_POST['products'])){

			viewRemoveItems($_POST['phone'], $_POST['products']);
			
			
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid request";
			echo json_encode($response);
		}
	}
	
		function viewRemoveItems($phone, $products){
			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
			$listMode = $phone."_listmode";
				
			$sql = "DELETE FROM `$listMode` WHERE `$listMode`.`products` = '$products'";
	    	$result = mysqli_query($connection, $sql);
	    	
			    $response['error'] = false;
				$response['message'] = "removed from cart!";
				echo json_encode($response);
	    		
		}

?>