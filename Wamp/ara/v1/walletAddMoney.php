<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(isset($_POST['phone']) && isset($_POST['otp']) && isset($_POST['balance']) ){

			walletVerifyOTP($_POST['phone'], $_POST['otp'], $_POST['balance']);
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid credentials";
			echo json_encode($response);
		}
	}

	
	function walletVerifyOTP($phone, $otp, $balance){
		$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

	    $sql = "SELECT `otp` FROM `wallet` WHERE `phone` = '$phone'";
	    $result = mysqli_fetch_array(mysqli_query($connection, $sql)) or die("Error loading values " . mysqli_error($connection));

	    $sql1 = "SELECT `balance` FROM `wallet` WHERE `phone` = '$phone'";
	    $result1 = mysqli_fetch_array(mysqli_query($connection, $sql1)) or die("Error loading values " . mysqli_error($connection));

	    $realOTP = $result['otp'];
	    $realBalance = $balance + $result1['balance'];

	    if($otp == $realOTP){
	    	$sql = "UPDATE `wallet` SET `verified` = '1' WHERE `wallet`.`phone` = '$phone'";
	    	$sql1 = "UPDATE `wallet` SET `balance` = '$realBalance' WHERE `wallet`.`phone` = '$phone'";
	    
	    	if(mysqli_query($connection, $sql) && mysqli_query($connection, $sql1)){
	    		$response['error'] = false;
				$response['message'] = "Balance updated successfully!";
				echo json_encode($response);		
	    	} 
	    	else{
	    		$response['error'] = true;
				$response['message'] = "INTERNAL Error: otp cannot be verified!";
				echo json_encode($response);
	    	}
	    }
	    else{
	    	$response['error'] = true;
			$response['message'] = "OTP does not match!";
			echo json_encode($response);
	    }

	}
?>