<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='GET'){

		if(isset($_GET['phone']) and isset($_GET['productId'])){

			cartRemoveProduct($_GET['phone'], $_GET['productId']);
		//	$db = new DbOperation();
		//	$result = $db->cartRemoveProducts1($_GET['phone'], $_GET['products']);			
		}
		else{
			$response['error'] = true;
			$response['message'] = "Invalid request";
			echo json_encode($response);
		}
	}


		function cartRemoveProduct($phone, $productId){
			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
				
				$cartMode = $phone."_cartmode";
				$sql1 = "SELECT `quantity` FROM `$cartMode` WHERE `productId` = '$productId'";
	    		$result1 = mysqli_fetch_array(mysqli_query($connection, $sql1)) or die("Error loading values1 " . mysqli_error($connection));
	    		
	    		$sql2 = "SELECT `cost` FROM `$cartMode` WHERE `productID` = '$productId'";
	    		$result2 = mysqli_fetch_array(mysqli_query($connection, $sql2)) or die("Error loading values2 " . mysqli_error($connection));

	    		$realQuantity = $result1['quantity'] - 1;
	    		$realTotal = $realQuantity * $result2['cost'];
	    		if($realQuantity == 0){
			    	$sql = "DELETE FROM `$cartMode` WHERE `$cartMode`.`productId` = '$productId'";
	    			$result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

			    	$response['error'] = false;
					$response['message'] = "removed from cart!";
					echo json_encode($response);
	    		}else{
	    			$sql = ("UPDATE `$cartMode` SET `quantity` = '$realQuantity' WHERE `$cartMode`.`productId` = '$productId'");
	    			$sql1 = ("UPDATE `$cartMode` SET `total` = '$realTotal' WHERE `$cartMode`.`productId` = '$productId'");
		    		if(mysqli_query($connection, $sql) && mysqli_query($connection, $sql1)){
			    		$response['error'] = false;
						$response['message'] = "quantity : $realQuantity";
						echo json_encode($response);		
			    	} 
			    	else{
			    		$response['error'] = true;
						$response['message'] = ": INTERNAL Error(item cannot be added!)";
						echo json_encode($response);
			    	}
	    		}
	    		
		}


?>	