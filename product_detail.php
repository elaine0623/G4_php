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
    $userNo = $data['userNo'];
    $prodNo = $data['p_no'];
    //執行分頁查詢sql
    // $sql = "SELECT * FROM product WHERE p_status = 1  ORDER BY p_no desc limit $limit OFFSET $size";
    $sql = "SELECT *
FROM product p
JOIN farm f ON p.f_no = f.f_no 
JOIN product_category c ON p.pc_no = c.pc_no
WHERE p.p_no = $prodNo";
    $product = $pdo->prepare($sql);
    $product->execute();
    //抓取資料庫商品資料
    $productData = $product->fetchAll(PDO::FETCH_ASSOC);
    foreach($productData as $key => $prod){
        $prodNo = $prod['p_no'];
        $sql3 = "SELECT `pi_img` FROM product_img WHERE p_no = $prodNo";
        $product_img = $pdo->prepare($sql3);
        $product_img->execute();
        $proDetails = $product_img->fetchAll(PDO::FETCH_ASSOC);
        $productData[$key]['isaddCart'] = false;
        foreach($proDetails as $proDetail){
            $productData[$key]['p_img'][] = $proDetail['pi_img'];//第0~N張照片放進[]裡(類似array.push)
        }
            //fetch會員資料庫購物車及收藏商品內容
            $sql4 = "SELECT * FROM member_favorite WHERE m_no  = '$userNo' AND p_no = $prodNo";
            $favoriteCart = $pdo->prepare($sql4);
            $favoriteCart->execute();
            $favoriteCarts = $favoriteCart->fetchAll(PDO::FETCH_ASSOC);
            foreach($favoriteCarts as $fav){
                if($fav['fav'] == 1 && $fav['p_no'] == $prodNo){
                    $productData[$key]['isImage1'] = true;
                }
                if($fav['cart'] == 1 && $fav['p_no'] == $prodNo){
                    $productData[$key]['isaddCart'] = true;
                }
            }
    }
    
    $returnData['data']['list'] = $productData;
} catch (Exception $e) { // 更廣泛地捕獲異常
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData);
