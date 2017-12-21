<?php
	class DbOperation{
		private $con;
		function __construct(){
			require_once dirname(__FILE__).'/DbConnect.php';
			$db = new DbConnect();
			$this->con = $db->connect();	
		}
		
		/*Create */
		
		public function userRegister($name , $email, $phone, $pass ){
			if($this->isUserExist($email, $phone)){
				return 3;
			}else{
				$password = base64_encode($pass);
				$stmt = $this->con->prepare("INSERT INTO `users` (`name`, `email`, `phone`, `password`, `otp`, `verified`) VALUES (?, ?, ?, ?, '', '');");
				$stmt->bind_param("ssss",$name,$email,$phone,$password);

				$listMode = $phone . "_listmode";
				$stmt1 = $this->con->prepare("CREATE TABLE `ara`.`$listMode` ( `category` VARCHAR(100) NOT NULL, `productId` INT(4) NOT NULL , `date` VARCHAR(15) NOT NULL , `products` VARCHAR(100) NOT NULL , `cost` FLOAT(10) NOT NULL , `weight` FLOAT(10) NOT NULL , `image` LONGBLOB NOT NULL ,`mfgDate` VARCHAR(15) NOT NULL,`expDate` VARCHAR(15) NOT NULL,`total` FLOAT(10) NOT NULL , `quantity` INT(3), `totalweight` FLOAT(10) NOT NULL, UNIQUE (`products`) , INDEX(`productId`) , FOREIGN KEY (`productId`) REFERENCES `products`(`productId`) ON DELETE NO ACTION ON UPDATE CASCADE) ENGINE = InnoDB");

				$shoppingMode = $phone . "_shoppingmode";
				$stmt2 = $this->con->prepare("CREATE TABLE `ara`.`$shoppingMode` ( `category` VARCHAR(100) NOT NULL, `productId` INT(4) NOT NULL , `date` VARCHAR(15) NOT NULL , `products` VARCHAR(100) NOT NULL , `cost` FLOAT(10) NOT NULL , `weight` FLOAT(10) NOT NULL , `image` LONGBLOB NOT NULL ,`mfgDate` VARCHAR(15) NOT NULL,`expDate` VARCHAR(15) NOT NULL,`total` FLOAT(10) NOT NULL , `quantity` INT(3) NOT NULL, `totalweight` FLOAT(10) NOT NULL, UNIQUE (`products`) , INDEX(`productId`) , FOREIGN KEY (`productId`) REFERENCES `products`(`productId`) ON DELETE NO ACTION ON UPDATE CASCADE) ENGINE = InnoDB");
				
				$stmt3 = $this->con->prepare("INSERT INTO `wallet` (`phone`, `balance`, `otp`, `verified`) VALUES (?, '0', '', '');");
				$stmt3->bind_param("s",$phone);

				if($stmt->execute() && $stmt1->execute() && $stmt2->execute() && $stmt3->execute()){
					return 1;
				}else{
					return 2;
				}
			}
		}

		public function forgotPassword($phone, $pass, $otp ){
			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
			$sql = "SELECT `otp` FROM `users` WHERE `phone` = '$phone'";
	    	$result = mysqli_fetch_array(mysqli_query($connection, $sql)) or die("Error loading values " . mysqli_error($connection));
			
	    	$realOTP = $result['otp'];
	    	if($otp == $realOTP){
		    	$stmt1 = $this->con->prepare("UPDATE `users` SET `otp` = '', `verified` = '1' WHERE `users`.`phone` = '$phone';");
				
				$password = base64_encode($pass);
				$stmt = $this->con->prepare("UPDATE `users` SET `password` = ? WHERE `users`.`phone` = ?;");
				$stmt->bind_param("ss",$password,$phone);
				
				if($stmt->execute() && $stmt1->execute()){
					return 1;
				}else{
					return 2;
				}	
	    	}
	    	else{
	    		return 0;
	    	}

		}

		public function userUpdate($name, $email, $pass, $phone){
			$password = base64_encode($pass);
			$stmt = $this->con->prepare("UPDATE `users` SET `name` = ?, `email` = ?, `password` = ? WHERE `users`.`phone` = ?;");
			$stmt->bind_param("ssss",$name,$email,$password,$phone);
			
			if($stmt->execute()){
				return 1;
			}else{
				return 2;
			}
		}
		
		private function isUserExist($email, $phone){
			$stmt = $this->con->prepare("SELECT phone FROM users WHERE email = ? OR phone = ?");
			$stmt->bind_param("ss", $email, $phone);
			$stmt->execute();
			$stmt->store_result();
			return $stmt->num_rows > 0;

		}

		public function userPhoneExists($phone){
			$stmt = $this->con->prepare("SELECT phone FROM users WHERE phone = ?");
			$stmt->bind_param("s", $phone);
			$stmt->execute();
			$stmt->store_result();
			if($stmt->num_rows > 0)
				return 1;
			else
				return 2;

		}

		public function userLogin($phone, $pass){
			$password = base64_encode($pass);
			$stmt = $this->con->prepare("SELECT phone FROM users WHERE phone = ? AND password =?");
			$stmt->bind_param("ss", $phone, $password);
			$stmt->execute();
			$stmt->store_result();
			return $stmt->num_rows > 0;			
		}

		

		public function getUserByPhone($phone){
			$stmt = $this->con->prepare("SELECT * FROM users WHERE phone = ?");
			$stmt->bind_param("s", $phone);
			$stmt->execute();
			return $stmt->get_result()->fetch_assoc();
		}


		public function listAddItems($phone, $category, $productId, $date, $products, $cost, $weight, $image, $mfgDate, $expDate){
			$imageDecoded = base64_decode($image);
			$listMode = $phone."_listmode";
			$total = $cost;
			$stmt = $this->con->prepare("INSERT INTO `$listMode` (`category`, `productId`, `date`, `products`, `cost`, `weight`, `image`, `mfgDate`, `expDate`, `total`, `quantity`, `totalweight`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1', ?)");
			$stmt->bind_param("sssssssssss",$category,$productId,$date,$products,$cost,$weight,$imageDecoded,$mfgDate,$expDate,$total,$weight);

			if ($stmt->execute()) {
				return 1;	
			}else{
				$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
				$sql1 = "SELECT `quantity` FROM `$listMode` WHERE `productId` = '$productId'";
	    		$result1 = mysqli_fetch_array(mysqli_query($connection, $sql1)) or die("Error loading values " . mysqli_error($connection));

	    		
	    		$realQuantity = $result1['quantity'] + 1;
	    		$realTotal = $realQuantity * $cost;
	    		$realWeight = $realQuantity * $weight;
	    		if($realQuantity >5){
	    			$response['error'] = true;
						$response['message'] = ": Quantity cannot be greater than 5.";
						echo json_encode($response);
	    		}else{
	    			$sql = ("UPDATE `$listMode` SET `quantity` = '$realQuantity' WHERE `$listMode`.`productId` = '$productId'");
	    			$sql1 = ("UPDATE `$listMode` SET `total` = '$realTotal' WHERE `$listMode`.`productId` = '$productId'");
	    			$sql2 = ("UPDATE `$listMode` SET `totalweight` = '$realWeight' WHERE `$listMode`.`productId` = '$productId'");
		    		if(mysqli_query($connection, $sql) && mysqli_query($connection, $sql1) && mysqli_query($connection, $sql2)){
			    		$response['error'] = false;
						$response['message'] = "Quantity : $realQuantity";
						echo json_encode($response);		
			    	} 
			    	else{
			    		$response['error'] = true;
						$response['message'] = ": INTERNAL Error(item cannot be added!)";
						echo json_encode($response);
			    	}
	    		}
	    				
			}
		}

		public function listRemoveItems($phone, $products){
			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
				$listMode = $phone."_listmode";
				
				$sql1 = "SELECT `quantity` FROM `$listMode` WHERE `products` = '$products'";
	    		$result1 = mysqli_fetch_array(mysqli_query($connection, $sql1)) or die("Error loading values " . mysqli_error($connection));
	    		$sql2 = "SELECT `cost` FROM `$listMode` WHERE `products` = '$products'";
	    		$result2 = mysqli_fetch_array(mysqli_query($connection, $sql2)) or die("Error loading values " . mysqli_error($connection));
	    		$sql3 = "SELECT `weight` FROM `$listMode` WHERE `products` = '$products'";
	    		$result3 = mysqli_fetch_array(mysqli_query($connection, $sql3)) or die("Error loading values " . mysqli_error($connection));

	    		$realQuantity = $result1['quantity'] - 1;
	    		$realTotal = $realQuantity * $result2['cost'];
	    		$realWeight = $realQuantity * $result3['weight'];
	    		if($realQuantity == 0){
			    	$sql = "DELETE FROM `$listMode` WHERE `$listMode`.`products` = '$products'";
	    			$result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

			    	$response['error'] = false;
					$response['message'] = "removed from cart!";
					echo json_encode($response);
	    		}else{
	    			$sql = ("UPDATE `$listMode` SET `quantity` = '$realQuantity' WHERE `$listMode`.`products` = '$products'");
	    			$sql1 = ("UPDATE `$listMode` SET `total` = '$realTotal' WHERE `$listMode`.`products` = '$products'");
	    			$sql2 = ("UPDATE `$listMode` SET `totalweight` = '$realWeight' WHERE `$listMode`.`products` = '$products'");
		    		if(mysqli_query($connection, $sql) && mysqli_query($connection, $sql1) && mysqli_query($connection, $sql2)){
			    		$response['error'] = false;
						$response['message'] = "Quantity : $realQuantity";
						echo json_encode($response);		
			    	} 
			    	else{
			    		$response['error'] = true;
						$response['message'] = ": INTERNAL Error(item cannot be added!)";
						echo json_encode($response);
			    	}
	    		}
	    		
		}

		public function listGetCart($phone){
			$listMode = $phone."_listmode";
			$stmt1 = $this->con->prepare("SELECT * FROM `$listMode` ORDER BY `productId` ASC");
			$stmt1->execute();
			$stmt1->store_result();
			if($stmt1->num_rows > 0){
				$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
			    //fetch table rows from mysql db
			    $sql = "SELECT * FROM `$listMode` ORDER BY `productId` ASC";
			    $result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

			    //create an array
			    $products = array();
			    while($row =mysqli_fetch_assoc($result))
			    {
			        $products[] = $row;
			    }

			    foreach($products as $key=>$value){
			    $cartImage[$key] =  $products[$key];
			    $cartImage[$key]['image'] = base64_encode($products[$key]['image']);
			    }
			    header('Content-type: application/json');
			    echo json_encode(array('cartProducts' => $cartImage));
			}
			else{
				$response['error'] = true;
				echo json_encode($response);
			}
		}


		public function listGetBill($phone){
			$listMode = $phone.'_listmode';
			$stmt1 = $this->con->prepare("SELECT * FROM `$listMode` ORDER BY `productId` ASC");
			$stmt1->execute();
			$stmt1->store_result();
			if($stmt1->num_rows > 0){
				$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));				

				$sql = "SELECT SUM(total) FROM `$listMode` WHERE 1";
				$result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));
				
				$array = mysqli_fetch_assoc($result);
				foreach ($array as $key => $value) {
				  $total = $value;
				}

				$sql1 = "SELECT SUM(quantity) FROM `$listMode` WHERE 1";
				$result1 = mysqli_query($connection, $sql1) or die("Error in Selecting " . mysqli_error($connection));
				
				$array1 = mysqli_fetch_assoc($result1);
				foreach ($array1 as $key => $value) {
				  $quantity = $value;
				}

				$vat = $total * 0.054;
				$vat = round($vat,2);
				$payable = $total + $vat;
				$payable = round($payable,2);

				$response['error'] = false;
				$response['message'] = "Approximate bill generated successfully";
				$response['bill'] = $total;
				$response['quantity'] = $quantity;
				$response['vat'] = $vat;
				$response['payable'] = $payable;
				echo json_encode($response);
			}
			else{
				$response['error'] = true;
				$response['message'] = "Shopping list cannot be empty!";
				echo json_encode($response);
			}

		}


		public function barcodeGetProduct($barcode){

			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

			$sql = "SELECT * FROM `products` WHERE `barcode` = '$barcode'";
		    $result = mysqli_query($connection, $sql);

		    if($result){
			    	//create an array

			    $products = array();
			    while($row =mysqli_fetch_assoc($result))
			    {
			        $products[] = $row;
			    }

			    foreach($products as $key=>$value){
			    $cartImage[$key] =  $products[$key];
			    $cartImage[$key]['image'] = base64_encode($products[$key]['image']);
			    }
			    header('Content-type: application/json');
			    echo json_encode(array('cartProducts' => $cartImage));	
		    }
		    else{
				$response['error'] = true;
				$response['message'] = "Product not found!";
				echo json_encode($response);
			}
		    
		}

		public function shoppingAddItems($phone, $category, $productId, $date, $products, $cost, $weight,$image,$mfgDate,$expDate){
			$imageDecoded = base64_decode($image);
			$shoppingMode = $phone."_shoppingmode";
			$listMode = $phone."_listmode";
			$total = $cost;
			$stmt = $this->con->prepare("INSERT INTO `$shoppingMode` (`category`, `productId`, `date`, `products`, `cost`, `weight`, `image`, `mfgDate`, `expDate`, `total`, `quantity`, `totalweight`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1', ?)");
			$stmt->bind_param("sssssssssss",$category,$productId,$date,$products,$cost,$weight,$imageDecoded,$mfgDate,$expDate,$total,$weight);


			if ($stmt->execute()) {
				$response['error'] = false;
				$response['message'] = "Product added successfully!";
				echo json_encode($response);
			}else{
				$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
				$sql1 = "SELECT `quantity` FROM `$shoppingMode` WHERE `productId` = '$productId'";
	    		$result1 = mysqli_fetch_array(mysqli_query($connection, $sql1)) or die("Error loading values " . mysqli_error($connection));

	    		
	    		$realQuantity = $result1['quantity'] + 1;
	    		$realTotal = $realQuantity * $cost;
	    		$realWeight = $weight * $realQuantity;
	    		if($realQuantity >5){
	    			$response['error'] = true;
						$response['message'] = ": Quantity cannot be greater than 5.";
						echo json_encode($response);
	    		}else{
	    			$sql = ("UPDATE `$shoppingMode` SET `quantity` = '$realQuantity' WHERE `$shoppingMode`.`productId` = '$productId'");
	    			$sql1 = ("UPDATE `$shoppingMode` SET `total` = '$realTotal' WHERE `$shoppingMode`.`productId` = '$productId'");
		    		$sql2 = ("UPDATE `$shoppingMode` SET `totalweight` = '$realWeight' WHERE `$shoppingMode`.`productId` = '$productId'");
		    		if(mysqli_query($connection, $sql) && mysqli_query($connection, $sql1) && mysqli_query($connection, $sql2)){
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

			$stmt2 = $this->con->prepare("SELECT * FROM `$listMode` WHERE `$listMode`.`products` = '$products' ORDER BY `productId` ASC");
				$stmt2->execute();
				$stmt2->store_result();
				if($stmt2->num_rows > 0){

					$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
					
					$sql1 = "SELECT `quantity` FROM `$listMode` WHERE `products` = '$products'";
		    		$result1 = mysqli_fetch_array(mysqli_query($connection, $sql1)) or die("Error  values " . mysqli_error($connection));
					$sql2 = "SELECT `quantity` FROM `$shoppingMode` WHERE `products` = '$products'";
	    			$result2 = mysqli_fetch_array(mysqli_query($connection, $sql2)) or die("loading values " . mysqli_error($connection));
	    			
		    		if($result1['quantity'] == $result2['quantity']){
		    			$stmt3 = $this->con->prepare("DELETE FROM `$listMode` WHERE `$listMode`.`products` = '$products'");
						if($stmt3->execute()){
							$respond['error'] = false;
							$respond['message'] = "Product deleted successfully!";
							echo json_encode($respond);	
						}else{
							$respond['error'] = true;
							$respond['message'] = "ERROR: Product cannot be deleted!";
							echo json_encode($respond);
						}
		    		}	
		    		else{
		    				$respond['error'] = true;
							$respond['message'] = "ERROR: Product cannot be deleted!";
							echo json_encode($respond);
							
		    		}					
				}
				else{
					return 3;
				}
		}

		public function shoppingRemoveItems($phone, $products){
			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
				$shoppingMode = $phone."_shoppingmode";
				$sql1 = "SELECT `quantity` FROM `$shoppingMode` WHERE `products` = '$products'";
	    		$result1 = mysqli_fetch_array(mysqli_query($connection, $sql1)) or die("Error loading values1 " . mysqli_error($connection));
	    		
	    		$sql2 = "SELECT `cost` FROM `$shoppingMode` WHERE `products` = '$products'";
	    		$result2 = mysqli_fetch_array(mysqli_query($connection, $sql2)) or die("Error loading values2 " . mysqli_error($connection));

	    		$sql3 = "SELECT `weight` FROM `$shoppingMode` WHERE `products` = '$products'";
	    		$result3 = mysqli_fetch_array(mysqli_query($connection, $sql3)) or die("Error loading values3 " . mysqli_error($connection));

	    		$realQuantity = $result1['quantity'] - 1;
	    		$realTotal = $realQuantity * $result2['cost'];
	    		$realWeight = $realQuantity * $result3['weight'];
	    		if($realQuantity == 0){
			    	$sql = "DELETE FROM `$shoppingMode` WHERE `$shoppingMode`.`products` = '$products'";
	    			$result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

			    	$response['error'] = false;
					$response['message'] = "removed from cart!";
					echo json_encode($response);
	    		}else{
	    			$sql = ("UPDATE `$shoppingMode` SET `quantity` = '$realQuantity' WHERE `$shoppingMode`.`products` = '$products'");
	    			$sql1 = ("UPDATE `$shoppingMode` SET `total` = '$realTotal' WHERE `$shoppingMode`.`products` = '$products'");
	    			$sql2 = ("UPDATE `$shoppingMode` SET `totalweight` = '$realWeight' WHERE `$shoppingMode`.`products` = '$products'");
		    		if(mysqli_query($connection, $sql) && mysqli_query($connection, $sql1) && mysqli_query($connection, $sql2)){
			    		$response['error'] = false;
						$response['message'] = "Quantity : $realQuantity";
						echo json_encode($response);		
			    	} 
			    	else{
			    		$response['error'] = true;
						$response['message'] = ": INTERNAL Error(item cannot be added!)";
						echo json_encode($response);
			    	}
	    		}
	    		
		} 

		public function shoppingRemoveItems1($phone, $products){			
			$listMode = $phone.'_listmode';
			$shoppingMode = $phone.'_shoppingmode';
			$stmt2 = $this->con->prepare("SELECT * FROM `$listMode` WHERE `$listMode`.`products` = '$products' ORDER BY `productId` ASC");
				$stmt2->execute();
				$stmt2->store_result();
				if($stmt2->num_rows > 0){

					$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
					
					$sql1 = "SELECT `quantity` FROM `$listMode` WHERE `products` = '$products'";
		    		$result1 = mysqli_fetch_array(mysqli_query($connection, $sql1)) or die("Error  values " . mysqli_error($connection));
					$sql2 = "SELECT `quantity` FROM `$shoppingMode` WHERE `products` = '$products'";
	    			$result2 = mysqli_fetch_array(mysqli_query($connection, $sql2)) or die("loading values " . mysqli_error($connection));
	    			
		    		if($result1['quantity'] == $result2['quantity']){
		    			$stmt3 = $this->con->prepare("DELETE FROM `$listMode` WHERE `$listMode`.`products` = '$products'");
						if($stmt3->execute()){
							$respond['error'] = false;
							$respond['message'] = "Product deleted successfully!";
							echo json_encode($respond);	
						}else{
							$respond['error'] = true;
							$respond['message'] = "ERROR: Product cannot be deleted!";
							echo json_encode($respond);
						}
		    		}	
		    		else{
		    				$respond['error'] = true;
							$respond['message'] = "ERROR: Product cannot be deleted!";
							echo json_encode($respond);
							
		    		}
					
				}
				else{
					return 3;
				}
		}


		public function shoppingGetCart($phone){
			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

		    //fetch table rows from mysql db
		    $shoppingMode = $phone."_shoppingmode";
		    $sql = "SELECT * FROM `$shoppingMode` ORDER BY `productId` ASC";
		    $result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

		    //create an array
		    $products = array();
		    while($row =mysqli_fetch_assoc($result))
		    {
		        $products[] = $row;
		    }

		    foreach($products as $key=>$value){
		    $cartImage[$key] =  $products[$key];
		    $cartImage[$key]['image'] = base64_encode($products[$key]['image']);
		    }
		    header('Content-type: application/json');
		    echo json_encode(array('cartProducts' => $cartImage));
		}


		public function shoppingGetBill($phone){
			$shoppingMode = $phone.'_shoppingmode';
			$stmt1 = $this->con->prepare("SELECT * FROM `$shoppingMode` ORDER BY `productId` ASC");
			$stmt1->execute();
			$stmt1->store_result();
			if($stmt1->num_rows > 0){
				$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));				

				$sql = "SELECT SUM(total) FROM `$shoppingMode` WHERE 1";
				$result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));
				
				$array = mysqli_fetch_assoc($result);
				foreach ($array as $key => $value) {
				  $bill = $value;
				}

				$sql1 = "SELECT SUM(quantity) FROM `$shoppingMode` WHERE 1";
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

				$response['error'] = false;
				$response['message'] = "Bill generated successfully";
				$response['bill'] = $bill;
				$response['quantity'] = $quantity;
				$response['discount'] = $discount;
				$response['subTotal'] = $subTotal;
				$response['vat'] = $vat;
				$response['payable'] = $payable;
				echo json_encode($response);
			}
			else{
				$response['error'] = true;
				$response['message'] = "Shopping list cannot be empty!";
				echo json_encode($response);
			}

		}

		public function listRemainingItems($phone){
			$listMode = $phone.'_listmode';
			$stmt1 = $this->con->prepare("SELECT * FROM `$listMode` ORDER BY `productId` ASC");
			$stmt1->execute();
			$stmt1->store_result();
			if($stmt1->num_rows > 0){
				$response['error'] = true;
				echo json_encode($response);	
			}
			else{
				$response['error'] = false;
				echo json_encode($response);
			}
		}
		
		
		public function walletCheckAndCheckout($phone, $rating){
			date_default_timezone_set("Asia/Kolkata");
	        $time = date('H:i:s');
			$date = date('Y-m-d');

	        $connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

	        $shoppingMode = $phone.'_shoppingmode';
	        $listMode = $phone.'_listmode';
	        //fetch table rows from mysql db
	        $sql = "SELECT `balance` FROM `wallet` WHERE `phone` = '$phone'";
	        $result = mysqli_query($connection, $sql) or die("Error loading balance " . mysqli_error($connection));

	        $arrayBalance = mysqli_fetch_assoc($result);
	                foreach ($arrayBalance as $key => $value) {
	                  $balance = $value;
	                }

	        $sql1 = "SELECT SUM(total) FROM `$shoppingMode` WHERE 1";
	        $result = mysqli_query($connection, $sql1) or die("Error in Selecting " . mysqli_error($connection));
	                
	        $arrayBill = mysqli_fetch_assoc($result);
	        foreach ($arrayBill as $key => $value) {
	            $bill = $value;
	        }

	        $sql1 = "SELECT SUM(quantity) FROM `$shoppingMode` WHERE 1";
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

	        	$sql3 = "INSERT INTO `orderdetails` (`phone`,`orderId`, `category`, `productId`, `products`, `cost`, `weight`, `image`, `mfgDate`, `expDate`, `total`, `quantity`, `totalweight`, `paid`, `rating`) SELECT $phone, '$orderId', `category`,`productId`, `products`, `cost`, `weight`, `image`, `mfgDate`, `expDate`, `total`, `quantity`, `totalweight`, '$payable', '$rating' from $shoppingMode";
	        	$result3 = mysqli_query($connection, $sql3) or die("Error inserting in orderDetails " . mysqli_error($connection));
				$sql4 = "TRUNCATE $shoppingMode";
	            $result4 = mysqli_query($connection, $sql4) or die("Error emptying shoppingmode " . mysqli_error($connection));
	                    
	            $response['error'] = false;
	        }
	        else{
	        	$response['error'] = true;
	        }
	        echo json_encode($response);
	    }


	    public function previousOrderAvailable($phone){
	    	$stmt1 = $this->con->prepare("SELECT * FROM `orders` WHERE `phone` = '$phone'");
			$stmt1->execute();
			$stmt1->store_result();
			if($stmt1->num_rows > 0){
				$response['error'] = false;
				echo json_encode($response);
			}
			else{
				$response['error'] = true;
				echo json_encode($response);
			}
	    }
	    
	    public function previousOrderList($phone){
				$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
				
				$sql = "SELECT `orderId`, `orderDate`, `orderTime` FROM `orders` WHERE `phone` = '$phone'";
				$result = mysqli_query($connection, $sql) or die("Error " . mysqli_error($connection));
				
					$orders = array();
				    while($row =mysqli_fetch_assoc($result))
				    {
				        $orders[] = $row;
				    }

				   // header('Content-type: application/json');
				    echo json_encode(array('previousOrder' => $orders));
								
			
		}


		public function previousOrderDetails($orderId){

			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

		    //fetch table rows from mysql db
		    $sql = "SELECT * FROM `orderdetails` WHERE `orderId` = '$orderId'";
		    $result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

		    //create an array
		    $products = array();
		    while($row =mysqli_fetch_assoc($result))
		    {
		        $products[] = $row;
		    }

		    foreach($products as $key=>$value){
		    $favImage[$key] =  $products[$key];
		    $favImage[$key]['image'] = base64_encode($products[$key]['image']);
		    }
		    header('Content-type: application/json');
		    echo json_encode(array('favProducts' => $favImage));
   		}


   		public function recommenderBestSeller(){
			
			$stmt1 = $this->con->prepare("SELECT * FROM `orderdetails`");
			$stmt1->execute();
			$stmt1->store_result();
			if($stmt1->num_rows > 0){
				$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));				

				$sql = "SELECT DISTINCT `category`,`productId`, `products`, `cost`, `weight`, `image`,`mfgDate`,`expDate` FROM orderdetails WHERE products IN (select `products` from ( SELECT `products` FROM orderdetails GROUP BY `products` ORDER BY SUM(quantity) DESC LIMIT 10) temp_tab)";
				$result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

					$products = array();
				    while($row =mysqli_fetch_assoc($result))
				    {
				        $products[] = $row;
				    }

				    
				foreach($products as $key=>$value){
			    $best[$key] =  $products[$key];
			    $best[$key]['image'] = base64_encode($products[$key]['image']);
			    }
			    header('Content-type: application/json');
			    echo json_encode(array('bestSeller' => $best)); 
			}
			else{
				$response['error'] = true;
				$response['message'] = "No products found";
				echo json_encode($response);
			}   			
   		}


   		public function recommenderSimilarRated($phone){
			$product1 = array();
			$product2 = array();
			$product3 = array();
			$mProducts1 = array();
			$mProducts2 = array();
			$mProducts3 = array();

			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
   			$stmt = $this->con->prepare("SELECT * FROM `orders` WHERE phone = $phone");
   			$stmt->execute();
   			$stmt->store_result();
   			if ($stmt->num_rows > 0) {
   				$sql = "SELECT DISTINCT `rating` FROM orderdetails WHERE `phone` = $phone GROUP BY `rating` ORDER BY COUNT(*) DESC LIMIT 3";
				$result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));
		   		$ratingCount = array();
		   		while($row =mysqli_fetch_assoc($result))
				{
				    $ratingCount[] = $row;  
				}
				foreach($ratingCount as $key=>$value){
			    	$rating[$key] =  $ratingCount[$key]['rating'];	
				}

				$sql1 = "SELECT DISTINCT `category`,`productId`, `products`, `cost`, `weight`, `image`,`mfgDate`,`expDate` FROM orderdetails WHERE products IN (select `products` from ( SELECT `products` FROM orderdetails WHERE `rating`= $rating[0] AND NOT `phone` = $phone GROUP BY `products` ORDER BY SUM(quantity) DESC LIMIT 5) temp_tab)";
				$result1 = mysqli_query($connection, $sql1) or die("Error in Selecting " . mysqli_error($connection));
				$product1 = array();
				    while($row =mysqli_fetch_assoc($result1))
				    {
				        $product1[] = $row;
				    }

				foreach($product1 as $key=>$value){
			    $mProducts1[$key] =  $product1[$key];
			    $mProducts1[$key]['image'] = base64_encode($product1[$key]['image']);
			    }
			    
			    $sql2 = "SELECT DISTINCT `category`,`productId`, `products`, `cost`, `weight`, `image`,`mfgDate`,`expDate` FROM orderdetails WHERE products IN (select `products` from ( SELECT `products` FROM orderdetails WHERE `rating`= $rating[1] AND NOT `phone` = $phone GROUP BY `products` ORDER BY SUM(quantity) DESC LIMIT 3) temp_tab)";
				$result2 = mysqli_query($connection, $sql2) or die("Error in Selecting " . mysqli_error($connection));
				$product2 = array();
				    while($row =mysqli_fetch_assoc($result2))
				    {
				        $product2[] = $row;
				    }

				foreach($product2 as $key=>$value){
			    $mProducts2[$key] =  $product2[$key];
			    $mProducts2[$key]['image'] = base64_encode($product2[$key]['image']);
			    }

			    $sql3 = "SELECT DISTINCT `category`,`productId`, `products`, `cost`, `weight`, `image`,`mfgDate`,`expDate` FROM orderdetails WHERE products IN (select `products` from ( SELECT `products` FROM orderdetails WHERE `rating`= $rating[2] AND NOT `phone` = $phone GROUP BY `products` ORDER BY SUM(quantity) DESC LIMIT 2) temp_tab)";
				$result3 = mysqli_query($connection, $sql3) or die("Error in Selecting " . mysqli_error($connection));
				$product3 = array();
				    while($row =mysqli_fetch_assoc($result3))
				    {
				        $product3[] = $row;
				    }

				foreach($product3 as $key=>$value){
			    $mProducts3[$key] =  $product3[$key];
			    $mProducts3[$key]['image'] = base64_encode($product3[$key]['image']);
			    }
				
				$products = array_merge($mProducts1,$mProducts2,$mProducts3);
				
				echo json_encode(array('similarRated' => $products));
			}
			else{
				$sql4 = "SELECT DISTINCT `category`,`productId`, `products`, `cost`, `weight`, `image`,`mfgDate`,`expDate` FROM orderdetails WHERE products IN (select `products` from ( SELECT `products` FROM orderdetails GROUP BY `products` ORDER BY SUM(rating) DESC LIMIT 10) temp_tab)";
				$result4 = mysqli_query($connection, $sql4) or die("Error in Selecting " . mysqli_error($connection));
				$product4 = array();
				    while($row =mysqli_fetch_assoc($result4))
				    {
				        $product4[] = $row;
				    }

				foreach($product4 as $key=>$value){
			    	$mProducts4[$key] =  $product4[$key];
			    	$mProducts4[$key]['image'] = base64_encode($product4[$key]['image']);
			    }
				
				echo json_encode(array('similarRated' => $mProducts4));
   			}
   		}

			   			
   		
   	/*	public function recommenderSimilarProducts($phone){
   			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
   			$stmt = $this->con->prepare("SELECT * FROM `orders` WHERE phone = $phone");
   			$stmt->execute();
   			$stmt->store_result();
   			if ($stmt->num_rows > 0) {
   				$sql1 = "SELECT DISTINCT `category`,`productId`, `products`, `cost`, `weight`, `image`,`mfgDate`,`expDate` FROM orderdetails WHERE products IN (select `products` from ( SELECT `products` FROM orderdetails WHERE `phone`= $phone GROUP BY `products` ORDER BY SUM(quantity) DESC LIMIT 10) temp_tab)";
   				$result1 = mysqli_query($connection, $sql1) or die("Error in Selecting " . mysqli_error($connection));
				
   				$product1 = array();
				    while($row =mysqli_fetch_assoc($result1))
				    {
				        $product1[] = $row;
				    }

				foreach($product1 as $key=>$value){
			    	$mProducts1[$key] =  $product1[$key]['products'];
			    }
			    $finalOrderId = array();
			    foreach ($mProducts1 as $findAssociation) {
			    	$sql2 = "SELECT `orderId` FROM orderdetails WHERE products = '$findAssociation' AND NOT phone = '$phone'";
			    	$result2 = mysqli_query($connection, $sql2) or die("Error in Selecting " . mysqli_error($connection));
				
	   				$mOrderId = array();
					    while($row =mysqli_fetch_assoc($result2))
					    {
					        $mOrderId[] = $row;
					    }

					foreach($mOrderId as $key=>$value){
				    	$mOrderId[$key] =  $mOrderId[$key]['orderId'];
				    	array_push($finalOrderId, $mOrderId[$key]);
				    }
				    
				}

				$sql3 = "SELECT DISTINCT `category`,`productId`, `products`, `cost`, `weight`, `image`,`mfgDate`,`expDate` FROM orderdetails WHERE products IN (select `products` from ( SELECT `products` FROM orderdetails WHERE `orderId` IN (".implode(',', $finalOrderId).") GROUP BY `products` ORDER BY SUM(quantity) DESC LIMIT 10) temp_tab)";
				$result3 = mysqli_query($connection, $sql3) or die("Error in Selecting " . mysqli_error($connection));
				$product2 = array();
				while($row =mysqli_fetch_assoc($result3))
				{
				    $product2[] = $row;
			    }

				foreach($product2 as $key=>$value){
			    	$mProducts2[$key] =  $product2[$key];
			    	$mProducts2[$key]['image'] = base64_encode($product2[$key]['image']);
			    }
					
				echo json_encode(array('similarProducts' => $mProducts2));	
			}
				    
   				
   			else{
   				 $passPhone = (array)$phone;
   				$sql4 = "SELECT DISTINCT `category`,`productId`, `products`, `cost`, `weight`, `image`,`mfgDate`,`expDate` FROM orderdetails WHERE products IN (select `products` from ( SELECT `products` FROM orderdetails WHERE `orderId` IN (".implode(',', $passPhone).")GROUP BY `products` ORDER BY COUNT(`products`) DESC LIMIT 10) temp_tab)";
				$result3 = mysqli_query($connection, $sql4) or die("Error in Selecting " . mysqli_error($connection));
				$product2 = array();
				while($row =mysqli_fetch_assoc($result3))
				{
				    $product2[] = $row;
			    }
			    $mProducts2 = array();
				foreach($product2 as $key=>$value){
			    	$mProducts2[$key] =  $product2[$key];
			    	$mProducts2[$key]['image'] = base64_encode($product2[$key]['image']);
			    }
					
				echo json_encode(array('similarProducts' => $mProducts2));	
   			}	
   		}
*/


   		public function employeeOrderAvailable($barcode){
	    	date_default_timezone_set("Asia/Kolkata");
	        $date = date('Y-m-d');
			$stmt1 = $this->con->prepare("SELECT * FROM `orders` WHERE `phone` = '$barcode' AND `orderDate` = '$date' AND `checkout` = '0'");
			$stmt1->execute();
			$stmt1->store_result();
			if($stmt1->num_rows > 0){
				$response['error'] = false;
				echo json_encode($response);
			}
			else{
				$response['error'] = true;
				echo json_encode($response);
			}
	    }
	    
	    public function employeeOrderList($barcode){
	    	date_default_timezone_set("Asia/Kolkata");
	        $date = date('Y-m-d');
			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
				
			$sql = "SELECT `orderId`, `orderDate`, `orderTime` FROM `orders` WHERE `phone` = '$barcode' AND `orderDate` = '$date' ORDER BY `orderTime` DESC";
			$result = mysqli_query($connection, $sql) or die("Error " . mysqli_error($connection));
				
			$orders = array();
			while($row =mysqli_fetch_assoc($result))
			{
			    $orders[] = $row;
			}

			// header('Content-type: application/json');
			echo json_encode(array('employeeOrder' => $orders));
								
			
		}


		public function employeeOrderDetails($orderId){

			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

		    //fetch table rows from mysql db
		    $sql = "SELECT `products`, `quantity`, `total`, `totalweight` FROM `orderdetails` WHERE `orderId` = '$orderId' ORDER BY `productId`";
		    $result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

		    //create an array
		    $products = array();
		    while($row =mysqli_fetch_assoc($result))
		    {
		        $products[] = $row;
		    }

		    header('Content-type: application/json');
		    echo json_encode(array('productDetails' => $products));
		    
   		}

   		public function employeeOrderDetails1($orderId){

			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

		    $sql1 = "SELECT COUNT(products) AS 'TotalItems', SUM(`quantity`) AS 'TotalQuantity', round(SUM(`paid`), 2) AS 'TotalBill', round(SUM(`totalweight`), 2) AS 'TotalWeight' FROM `orderdetails` WHERE `orderId` = '$orderId'";
		    $result1 = mysqli_query($connection, $sql1) or die("Error in Selecting " . mysqli_error($connection));

		    $details = array();
			while($row =mysqli_fetch_assoc($result1))
		    {
		        $details[] = $row;
		    }

		    header('Content-type: application/json');
		    echo json_encode(array('employeeDetails' => $details));
		    
   		}


		public function employeeCheckout($orderId){
			$connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));
			$sql = "UPDATE `orders` SET `checkout`='1' WHERE `orderId` = '$orderId'";
			$result = mysqli_query($connection, $sql);
			if ($result) {
				$response['error'] = false;
				$response['message'] = "User check out complete.";
				echo json_encode($response);
			}
			else{
				$response['error'] = true;
				$response['message'] = "Internal error...";
				echo json_encode($response);	
			}

		}   	





	}
	
?>