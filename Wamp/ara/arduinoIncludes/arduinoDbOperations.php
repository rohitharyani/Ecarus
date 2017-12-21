<?php
	class DbOperation{
		private $con;
		function __construct(){
			require_once dirname(__FILE__).'/arduinoDbConnect.php';
			$db = new DbConnect();
			$this->con = $db->connect();	
		}
		
		/*Create */
		
		public function userEntersMall($phone){
			$listMode = $phone."_listmode";
			$shoppingMode = $phone."_shoppingmode";	
			$cartMode = $phone."_cartmode";

			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

		    $stmt = $this->con->prepare("SELECT * FROM `users` WHERE `phone` = '$phone'");
			$stmt->execute();
			$stmt->store_result();
			if($stmt->num_rows > 0){
				
				$sqlMall = "SELECT `mall` FROM `users` WHERE phone = '$phone'";
				$resultMall = mysqli_query($connection, $sqlMall) or die("Error in Selecting " . mysqli_error($connection));
						
				$arrayMall = mysqli_fetch_assoc($resultMall);
				foreach ($arrayMall as $key => $value) {
					$entered = $value;
				}
				if($entered == 1){
					$response['error'] = true;
					$response['message'] = "Cart taken already";
					echo json_encode($response);
				}
				else{
					$stmt1 = $this->con->prepare("SELECT * FROM `$shoppingMode`");
					$stmt1->execute();
					$stmt1->store_result();
					if($stmt1->num_rows > 0){
				    	$response['message'] = "User Modes > 1";
						echo json_encode($response);	
				    }
				    else{
				    	$stmt2 = $this->con->prepare("SELECT * FROM `$listMode`");
						$stmt2->execute();
						$stmt2->store_result();
						if($stmt2->num_rows > 0){

							$sql1 = "SELECT SUM(total) FROM `$listMode` WHERE 1";
							$result = mysqli_query($connection, $sql1) or die("Error in Selecting " . mysqli_error($connection));
							
							$array = mysqli_fetch_assoc($result);
							foreach ($array as $key => $value) {
							  $total = $value;
							}

							$sql2 = "SELECT SUM(quantity) FROM `$listMode` WHERE 1";
							$result2 = mysqli_query($connection, $sql2) or die("Error in Selecting " . mysqli_error($connection));
							
							$array1 = mysqli_fetch_assoc($result2);
							foreach ($array1 as $key => $value) {
							  $quantity = $value;
							}

							$sql3 = "SELECT `balance` FROM `wallet` WHERE `phone` = '$phone'";
					        $result3 = mysqli_query($connection, $sql3) or die("Error loading balance " . mysqli_error($connection));

					        $arrayBalance = mysqli_fetch_assoc($result3);
					                foreach ($arrayBalance as $key => $value) {
					                  $balance = $value;
					                }

							$vat = $total * 0.054;
							$vat = round($vat,2);
							$payable = $total + $vat;
							$payable = round($payable,2);
							if($balance >= $payable){
								$sql4 = "CREATE TABLE `ara`.`$cartMode` ( `category` VARCHAR(100) NOT NULL, `productId` INT(4) NOT NULL , `date` VARCHAR(15) NOT NULL , `products` VARCHAR(100) NOT NULL , `cost` FLOAT(10) NOT NULL , `weight` FLOAT(10) NOT NULL , `image` LONGBLOB NOT NULL ,`mfgDate` VARCHAR(15) NOT NULL,`expDate` VARCHAR(15) NOT NULL,`total` FLOAT(10) NOT NULL , `quantity` INT(3) NOT NULL, UNIQUE (`products`) , INDEX(`productId`) , FOREIGN KEY (`productId`) REFERENCES `products`(`productId`) ON DELETE NO ACTION ON UPDATE CASCADE) ENGINE = InnoDB";
								$result4 = mysqli_query($connection, $sql4) or die("Error creating cart table " . mysqli_error($connection));
							
								$sqlSetMall = "UPDATE `users` SET `mall` = '1' WHERE `users`.`phone` = '$phone'";
				    			$resultSetMall = mysqli_query($connection, $sqlSetMall) or die("Error in Selecting " . mysqli_error($connection));
				    		
								$response['phone'] = $phone;
								echo json_encode($response);
							}
							else{
								$response['error'] = true;
								$response['message'] = "Balance is low!";
								echo json_encode($response);	
							}
						}
				    	else{

							$sql4 = "CREATE TABLE `ara`.`$cartMode` ( `productId` INT(4) NOT NULL , `date` VARCHAR(15) NOT NULL , `products` VARCHAR(100) NOT NULL , `cost` FLOAT(10) NOT NULL , `weight` FLOAT(10) NOT NULL ,`mfgDate` VARCHAR(15) NOT NULL,`expDate` VARCHAR(15) NOT NULL,`total` FLOAT(10) NOT NULL , `quantity` INT(3) NOT NULL, UNIQUE (`products`) , INDEX(`productId`) , FOREIGN KEY (`productId`) REFERENCES `products`(`productId`) ON DELETE NO ACTION ON UPDATE CASCADE) ENGINE = InnoDB";
								$result4 = mysqli_query($connection, $sql4) or die("Error creating cart table " . mysqli_error($connection));
							
							$sqlSetMall = "UPDATE `users` SET `mall` = '1' WHERE `users`.`phone` = '$phone'";
				    			$resultSetMall = mysqli_query($connection, $sqlSetMall) or die("Error in Selecting " . mysqli_error($connection));
				    			
							$response['phone'] = $phone;
							echo json_encode($response);	
				    	}
				    }
				}
			}
		    else{
		    	$response['error']=true;
				$response['message'] = "ERROR";
				echo json_encode($response);
			}
		    
		}


		public function listGetFirstProduct($phone){
			$listMode = $phone."_listmode";

			$stmt = $this->con->prepare("SELECT `products` FROM `$listMode` ORDER BY `productId` ASC LIMIT 1");
			$stmt->execute();
			$stmt->store_result();
			if($stmt->num_rows > 0){
				$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
			    //fetch table rows from mysql db
			    $sql = "SELECT `products` AS productName FROM `$listMode` ORDER BY `productId` ASC LIMIT 1";
			    $result = mysqli_query($connection, $sql) or die("Error loading listMode " . mysqli_error($connection));

			    $products = array();
			    while($row =mysqli_fetch_assoc($result))
			    {
			        $products[] = $row;
			    }

				foreach($products as $key=>$value){
				    $productName =  $value;
				}
			    echo json_encode($productName);
				
			}else{
				$message = "List Empty!";
				echo json_encode($message);
			}		
		}



		public function barcodeGetProduct($barcode){

			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

			$sql = "SELECT `productId`, `productName`, `mfgDate`, `expDate`, `cost`, `weight`, `barcode` FROM `products` WHERE `barcode` = '$barcode'";
		    $result = mysqli_query($connection, $sql) or die("Error fetching product!" . mysqli_error($connection));

		    	$products = array();
			    while($row =mysqli_fetch_assoc($result))
			    {
			        $products[] = $row;
			    }

			    foreach($products as $key=>$value){
			    $barcodeProduct =  $value;
			    }
			    header('Content-type: application/json');
			    echo json_encode($barcodeProduct);
		    
		}


		public function cartAddProduct($phone, $productId){
			date_default_timezone_set("Asia/Kolkata");
			$date = date('Y-m-d');
			$cartMode = $phone."_cartmode";
			$listMode = $phone."_listmode";
			$stmt = $this->con->prepare("INSERT INTO `$cartMode`(`category`, `productId`, `date`, `products`, `cost`, `weight`, `image`, `mfgDate`, `expDate`, `total`, `quantity`) SELECT `category`, `productId`, '$date', `productName`, `cost`, `weight`, `image`, `mfgDate`, `expDate`, `cost`, '1' FROM products WHERE `productId` = '$productId'");
			
			if ($stmt->execute()) {
				$response['message'] = "Added succesfully!";
				echo json_encode($response);
			}else{
				$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
				$sql1 = "SELECT `quantity` FROM `$cartMode` WHERE `productId` = '$productId'";
	    		$result1 = mysqli_fetch_array(mysqli_query($connection, $sql1)) or die("Error loading values " . mysqli_error($connection));
	    		$sql2 = "SELECT `cost` FROM `$cartMode` WHERE `productId` = '$productId'";
	    		$result2 = mysqli_fetch_array(mysqli_query($connection, $sql2)) or die("loading values " . mysqli_error($connection));
	    			
	    		$cost = $result2['cost'];
	    		
	    		$realQuantity = $result1['quantity'] + 1;
	    		$realTotal = $realQuantity * $cost;
	    		if($realQuantity >5){
	    			$response['message'] = "QTY > 5";
						echo json_encode($response);
	    		}else{
	    			$sql = ("UPDATE `$cartMode` SET `quantity` = '$realQuantity' WHERE `$cartMode`.`productId` = '$productId'");
	    			$sql1 = ("UPDATE `$cartMode` SET `total` = '$realTotal' WHERE `$cartMode`.`productId` = '$productId'");
		    		if(mysqli_query($connection, $sql) && mysqli_query($connection, $sql1)){
			    		$response['message'] = "quantity : $realQuantity";
						echo json_encode($response);		
			    	} 
			    	else{
			    		$response['message'] = "ERROR";
						echo json_encode($response);
			    	}
	    		}			
			}

			$stmt2 = $this->con->prepare("SELECT * FROM `$listMode` WHERE `$listMode`.`productId` = '$productId'");
				$stmt2->execute();
				$stmt2->store_result();
				if($stmt2->num_rows > 0){

					$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
					
					$sql1 = "SELECT `quantity` FROM `$listMode` WHERE `productId` = '$productId'";
		    		$result1 = mysqli_fetch_array(mysqli_query($connection, $sql1)) or die("Error  values " . mysqli_error($connection));
					$sql2 = "SELECT `quantity` FROM `$cartMode` WHERE `productId` = '$productId'";
	    			$result2 = mysqli_fetch_array(mysqli_query($connection, $sql2)) or die("loading values " . mysqli_error($connection));
	    			$sql3 = "SELECT `cost` FROM `$listMode` WHERE `productId` = '$productId'";
	    			$result3 = mysqli_fetch_array(mysqli_query($connection, $sql3)) or die("loading values " . mysqli_error($connection));
	    			
	    			$cost = $result3['cost'];
		    		$updateQuantity =  $result1['quantity'] - 1;
		    		$updateTotal = $updateQuantity * $cost;
		    		if($updateQuantity > 0){
		    			$sqlUpdate = ("UPDATE `$listMode` SET `quantity` = '$updateQuantity' WHERE `$listMode`.`productId` = '$productId'");
		    			$sqlUpdate1 = ("UPDATE `$listMode` SET `total` = '$updateTotal' WHERE `$listMode`.`productId` = '$productId'");
			    		if(mysqli_query($connection, $sqlUpdate) && mysqli_query($connection, $sqlUpdate1)){
				    		$response['message2'] = "quantity : $updateQuantity";
							echo json_encode($response);		
				    	} 
				    	else{
				    		$response['message'] = "item not added!";
							echo json_encode($response);
				    	}
		    		}

		    		else if ($updateQuantity == 0){
		    			$stmt3 = $this->con->prepare("DELETE FROM `$listMode` WHERE `$listMode`.`productId` = '$productId'");
						if($stmt3->execute()){
							$respond['message'] = "Product deleted!";
							echo json_encode($respond);	
						}else{
							$respond['message'] = "Product not deleted!";
							echo json_encode($respond);
						}
		    		}
		    		else{
		    				$respond['message'] = "Product not deleted!";
							echo json_encode($respond);
							
		    		}					
				}
				else{
					$respond['message'] = "Product added!";
					echo json_encode($response);
				}
		}


		public function cartGetBill($phone){
			$cartMode = $phone.'_cartmode';
			$stmt1 = $this->con->prepare("SELECT * FROM `$cartMode`");
			$stmt1->execute();
			$stmt1->store_result();
			if($stmt1->num_rows > 0){
				$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));				

				$sql = "SELECT SUM(total) FROM `$cartMode` WHERE 1";
				$result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));
				
				$array = mysqli_fetch_assoc($result);
				foreach ($array as $key => $value) {
				  $bill = $value;
				}

				$sql1 = "SELECT SUM(quantity) FROM `$cartMode` WHERE 1";
				$result1 = mysqli_query($connection, $sql1) or die("Error in Selecting " . mysqli_error($connection));
				
				$array1 = mysqli_fetch_assoc($result1);
				foreach ($array1 as $key => $value) {
				  $quantity = $value;
				}

				$discount = $bill * 0.1;
				$discount = round($discount,2);
				$subTotal = $bill - $discount;
				$subTotal = round($subTotal,2);
				$vat = $subTotal * 0.054;
				$vat = round($vat,2);
				$payable = $subTotal + $vat;
				$payable = round($payable , 2);

				$response['bill'] = $bill;
				$response['total'] = $quantity;
				$response['discount'] = $discount;
				$response['subTotal'] = $subTotal;
				$response['vat'] = $vat;
				$response['payable'] = $payable;
				echo json_encode($response);
			}
			else{
				$response['error'] = true;
				$response['message'] = "Cart Cannot be empty!";
				echo json_encode($response);
			}
		}

/*
		public function listRemainingProducts($phone){
			$listMode = $phone.'_listmode';
			$stmt1 = $this->con->prepare("SELECT * FROM `$listMode`");
			$stmt1->execute();
			$stmt1->store_result();
			if($stmt1->num_rows > 0){
				$response['error'] = true;
				$response['message'] = "Products still remaining!";
				echo json_encode($response);	
			}
			
		}
*/

		public function walletCheckAndCheckout($phone){
			date_default_timezone_set("Asia/Kolkata");
	        $time = date('H:i:s');
			$date = date('Y-m-d');

	        $connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

	        $cartMode = $phone.'_cartmode';
	        $shoppingMode = $phone.'_shoppingmode';
	        $listMode = $phone.'_listmode';
	        
	        //fetch table rows from mysql db
	        $sql = "SELECT `balance` FROM `wallet` WHERE `phone` = '$phone'";
	        $result = mysqli_query($connection, $sql) or die("Error loading balance " . mysqli_error($connection));

	        $arrayBalance = mysqli_fetch_assoc($result);
	        foreach ($arrayBalance as $key => $value) {
	            $balance = $value;
	        }

	        $sql1 = "SELECT SUM(total) FROM `$cartMode` WHERE 1";
	        $result = mysqli_query($connection, $sql1) or die("Error in Selecting " . mysqli_error($connection));
	                
	        $arrayBill = mysqli_fetch_assoc($result);
	        foreach ($arrayBill as $key => $value) {
	            $bill = $value;
	        }

	        $sql1 = "SELECT SUM(quantity) FROM `$cartMode` WHERE 1";
	        $result1 = mysqli_query($connection, $sql1) or die("Error in Selecting " . mysqli_error($connection));
	                
	        $arrayQuantity = mysqli_fetch_assoc($result1);
	        foreach ($arrayQuantity as $key => $value) {
	        	$quantity = $value;
	        }

	        $discount = $bill * 0.1;
	        $discount = round($discount,2);
	        $subTotal = $bill - $discount;
	        $subTotal = round($subTotal,2);
	        $vat = $subTotal * 0.054;
	        $vat = round($vat,2);
	        $payable = $subTotal + $vat;
	        $payable = round($payable , 2);

	        if($balance >= $payable){
	        	//$paid = $payable;
	            $balance = $balance - $payable;
	            $balance = round($balance,2);
	            $sql = "UPDATE `wallet` SET `balance` = '$balance' WHERE `wallet`.`phone` = '$phone'";
	            $result = mysqli_query($connection, $sql) or die("Error deducting balance " . mysqli_error($connection));
	            $sql1 = "INSERT INTO `orders` (`orderId`, `phone`, `orderDate`, `orderTime`) VALUES (NULL, '$phone', '$date' , '$time')";	
	            $result1 = mysqli_query($connection, $sql1) or die("Error entering in orders " . mysqli_error($connection));
	            $sql2 = "SELECT `orderId`FROM `orders` WHERE `phone` = '$phone' AND `orderDate` = '$date' order BY `orderTime` DESC limit 1";
	            $result2 = mysqli_query($connection, $sql2) or die("Error selecting order Id " . mysqli_error($connection));

	            $arrayorderId = mysqli_fetch_assoc($result2);
	        	foreach ($arrayorderId as $key => $value) {
	        		$orderId = $value;
	        	}

	        	$sql3 = "INSERT INTO `orderdetails` (`phone`,`orderId`, `category`, `productId`, `products`, `cost`, `weight`, `image`, `mfgDate`, `expDate`, `total`, `quantity`, `paid`, `rating`) SELECT $phone, '$orderId', `category`,`productId`, `products`, `cost`, `weight`, `image`, `mfgDate`, `expDate`, `total`, `quantity`, '$payable', '3' from $cartMode";
	        	$result3 = mysqli_query($connection, $sql3) or die("Error inserting in orderDetails " . mysqli_error($connection));
				$sql4 = "DROP TABLE $cartMode";
	            $result4 = mysqli_query($connection, $sql4) or die("Error emptying shoppingmode " . mysqli_error($connection));
	            $sql5 = "UPDATE `users` SET `mall` = '0' WHERE `users`.`phone` = '$phone'";
	    		$result5 = mysqli_query($connection, $sql5) or die("Error loading values1 " . mysqli_error($connection));
	    		$sql6 = "TRUNCATE $listMode";
	    		$result6 = mysqli_query($connection, $sql6) or die("Error loading values1 " . mysqli_error($connection));
	    		        
	            $response['error'] = false;
	            $response['message'] = "PAYMENT SUCCESSFULL!";
	     	}
	        else{
	        	$response['error'] = true;
	        	$response['message'] = "ERROR";
	        }
	        echo json_encode($response);
	    }


	



	}

	
?>