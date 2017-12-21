
<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(isset($_POST['phone']) and isset($_POST['otp'])){

			walletSetOTP($_POST['phone'], $_POST['otp']);
		}
		else{
			$response['error'] = true;
			$response['message'] = "ERROR: Please try again.";
			echo json_encode($response);
		}
	}

	
	function walletSetOTP($phone, $otp){
		$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

	    $sql = "UPDATE `wallet` SET `otp` = '$otp' WHERE `wallet`.`phone` = '$phone';";
	    $result = mysqli_query($connection, $sql) or die("Error updating " . mysqli_error($connection));
	    
	    $sql1 = "UPDATE `wallet` SET `verified` = '0' WHERE `wallet`.`phone` = '$phone';";
	    $result1 = mysqli_query($connection, $sql1) or die("Error updating " . mysqli_error($connection));

	    $response['error'] = false;
			$response['message'] = "OTP updated successfully";
			echo json_encode($response);
	}


?>
