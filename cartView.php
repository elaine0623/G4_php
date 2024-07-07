<?php
try {
    require_once("./connect_cid101g4.php");
    $returnData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];
    
    // 抓前端傳來的資料
    $data = json_decode(file_get_contents('php://input'), true);
    $userNo = $data["userNo"];
    $sql ="SELECT p_no FROM `member_favorite` WHERE m_no='$userNo' AND cart=1";
    $cart = $pdo->prepare($sql);
    $cart->execute();
    $carts = $cart->fetchall(PDO::FETCH_ASSOC);
    $productIN = implode(',' ,array_column($carts, 'p_no'));
    $sql ="SELECT * FROM product p JOIN farm f ON p.f_no = f.f_no WHERE p_no in ($productIN) ";
    $cart = $pdo->prepare($sql);
    $cart->execute();
    $productData = $cart->fetchall(PDO::FETCH_ASSOC);

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
}
catch (Exception $e) {
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData, JSON_NUMERIC_CHECK);
?>
