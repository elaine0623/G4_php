<?php
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
    // SQL查詢
    $sql = "SELECT po.*, od.*, p.*, pi.*
    FROM p_orders po  
    JOIN `order-details` od ON po.po_no = od.po_no
    JOIN product p ON p.p_no = od.p_no
    JOIN product_img pi ON pi.p_no = p.p_no 
    WHERE po.m_no = :m_no
    GROUP BY po.po_no, od.po_no 
    ORDER BY po.po_time DESC";

    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':m_no', $data['m_no']);
    $stmt->execute();
    $pOrdersRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 將查詢結果賦值給返回資料
    $returnData['data']['list'] = $pOrdersRows;
} catch (Exception $e) {
    // 捕獲異常並設置錯誤代碼和錯誤信息
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

// 將返回資料編碼為JSON並輸出
echo json_encode($returnData);
?>