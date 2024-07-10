<?php
//會員收藏項目更新商品收藏狀態(取消或是加入購物車)
try {
    // 包含資料庫連接設定
    require_once("./connect_cid101g4.php");

    // 設定返回資料的初始值
    $returnData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data['type']==1){
        // SQL查詢-改變會員的商品收藏狀態
        $sql = "UPDATE `member_favorite` SET fav=0
        WHERE m_no=:m_no AND p_no=:p_no";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':m_no', $data['m_no']);
        $stmt->bindValue(':p_no', $data['p_noList'][0]);
        $stmt->execute();
        $pFavRows = $stmt->fetch(PDO::FETCH_ASSOC);
        $returnData['data']['list'] = $pFavRows;
    }
    elseif($data['type']==2){
        // SQL查詢-將會員的商品從收藏移除並加入購物車
        $turnintocart=$data['p_noList'];
        foreach ($turnintocart as $key => $item){
            $sql = "UPDATE `member_favorite` SET fav=0,cart=1
            WHERE m_no=:m_no AND p_no=$item";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':m_no', $data['m_no']);
            $stmt->execute();
        }
        $pCartRows = $stmt->fetchall(PDO::FETCH_ASSOC);
    }



    // 將查詢結果賦值給返回資料
} catch (Exception $e) {
    // 捕獲異常並設置錯誤代碼和錯誤信息
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

// 將返回資料編碼為JSON並輸出
echo json_encode($returnData);
?>