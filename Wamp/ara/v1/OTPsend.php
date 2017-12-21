<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();

	if($_SERVER['REQUEST_METHOD']=='POST'){

		if(
			isset($_POST['phone']) and isset($_POST['otp'])  
		){

			OTPSend($_POST['phone'], $_POST['otp']);
		}
		else{
			$response['error'] = true;
			$response['message'] = "ERROR: Please try again.";
			echo json_encode($response);
		}
	}


	function OTPSend($phone, $otp){
		$username = 'rohit.sae196@gmail.com';
		$hash = '68cc9291c58f591efe05f4b33b5eac84a7997bf8';
	
		

		$phone1 = '91'.$phone;
		// Message details
		$numbers = $phone1;
		$sender = urlencode('ARAOTP');
		$value = 'Welcome to ARA - An Ingenious Pushcart Experience! Your One Time Password is: '.$otp.' .Please use this OTP within 2 minutes.';
		$message = rawurlencode($value);
	 
		
	 
		// Prepare data for POST request
		$data = array('username' => $username, 'hash' => $hash, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
	 
		// Send the POST request with cURL
		$ch = curl_init('http://api.textlocal.in/send/');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		
		// Process your response here
		echo $response;	
	}
	
	
?>