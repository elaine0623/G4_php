<?php 
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