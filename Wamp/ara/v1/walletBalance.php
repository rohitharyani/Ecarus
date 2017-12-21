<?php

    require_once '../includes/DbOperations.php';
    
    $response = array();
    if($_SERVER['REQUEST_METHOD']=='POST'){

        if(isset($_POST['phone'])){

            walletBalance($_POST['phone']);
        }
        else{
            $response['error'] = true;
            $response['message'] = "Invalid credentials";
            echo json_encode($response);
        }
    }

    
    function walletBalance($phone){
        $connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

        //fetch table rows from mysql db
        $sql = "SELECT * FROM `wallet` WHERE `phone` = '$phone'";
        $result = mysqli_query($connection, $sql) or die("Error loading balance " . mysqli_error($connection));

        //create an array
        $balance = array();
        while($row =mysqli_fetch_assoc($result))
        {
            $balance[] = $row;
        }

        
        header('Content-type: application/json');
        echo json_encode(array('walletBalance' => $balance));
    }
?>