<?php
try {
    require_once("./connect_cid101g4.php");
    $returnData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];
    //抓前端傳來的資料
    $data = json_decode(file_get_contents('php://input'), true);
    //定義page變數 =前端傳來頁碼
    $page = $data['page'] - 1;
    $limit = 3;
    $size = $page * $limit;
    //執行分頁查詢sql
    // $sql = "SELECT * FROM product WHERE p_status = 1  ORDER BY p_no desc limit $limit OFFSET $size";
    $sql = "SELECT *FROM  product p JOIN farm f ON p.f_no =f.f_no 
    JOIN product_category C ON p.pc_no  =  c.pc_no
    WHERE p_status = 1 
    ORDER BY p_no desc 
    limit $limit OFFSET $size";
    $product = $pdo->prepare($sql);
    $product->execute();
    //查詢所有有效商品筆數
    $sql2 = "SELECT * FROM product WHERE p_status = 1";
    $stmt = $pdo->prepare($sql2);
    $stmt->execute();
    //抓取資料庫商品資料
    $productData = $product->fetchAll(PDO::FETCH_ASSOC);
    foreach($productData as $key => $prod){
        $prodNo = $prod['p_no'];
        $sql3 = "SELECT `pi_img` FROM product_img WHERE p_no = $prodNo";
        $product_img = $pdo->prepare($sql3);
        $product_img->execute();
        $proDetails = $product_img->fetchAll(PDO::FETCH_ASSOC);
        foreach($proDetails as $proDetail){
            $productData[$key]['p_img'][] = $proDetail['pi_img'];//第0~N張照片放進[]裡(類似array.push)
        }
    }
    $returnData['data']['list'] = $productData;
    $returnData['data']['totalCount'] = $stmt->rowCount();
} catch (Exception $e) { // 更廣泛地捕獲異常
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData);
