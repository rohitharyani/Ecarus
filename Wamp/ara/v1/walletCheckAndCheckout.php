<?php

    require_once '../includes/DbOperations.php';
    
    $response = array();
    if($_SERVER['REQUEST_METHOD']=='POST'){

        if(isset($_POST['phone']) and isset($_POST['rating'])){
        	
        	$db = new DbOperation();

			//parse values to createUser function
			$result = $db->walletCheckAndCheckout($_POST['phone'], $_POST['rating']);
        }
        else{
            $response['error'] = true;
            $response['message'] = "Invalid credentials";
            echo json_encode($response);
        }
    }
    
?>


