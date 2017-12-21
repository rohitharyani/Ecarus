<?php

	require_once '../includes/DbOperations.php';
	
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){

			$db = new DbOperation();

			$db->recommenderBestSeller();
	}


	


				
