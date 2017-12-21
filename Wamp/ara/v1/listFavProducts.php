<?php
    //open connection to mysql db
    $connection = mysqli_connect("localhost","root","","ara") or die("Error " . mysqli_error($connection));

    //fetch table rows from mysql db
    $sql = "SELECT * FROM `products` WHERE `productId` BETWEEN 1000 AND 2000";
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
   
   // echo json_encode($emparray);

?>