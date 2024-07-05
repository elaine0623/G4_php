<?php 
//---------------開發階段
	header('Access-Control-Allow-Origin: *');
	// Specify which request methods are allowed
	header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');

	// Additional headers which may be sent along with the CORS request
	header('Access-Control-Allow-Headers: X-Requested-With,Authorization,Content-Type');

	// Set the age to 1 day to improve speed/caching.
	header('Access-Control-Max-Age: 86400');

	$dbname = "cid101_g4";
	$user = "root";
	$password = "";


	$dsn = "mysql:host=localhost;port=3306;dbname=$dbname;charset=utf8";

	// // 產品階段
	// $dbname = "tibamefe_cid103g1";
	// $user = "tibamefe_since2021";
	// $password = "vwRBSb.j&K#E";
	// $port = 3306;

//告訴pdo希望的物件的格式
	$options = array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, 
	PDO::ATTR_CASE=>PDO::CASE_LOWER);
//ATTR_CASE=>PDO::CASE_LOWER欄位名一律小寫

//建立pdo物件
	$pdo = new PDO($dsn, $user, $password, $options);	
?>