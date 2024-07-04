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
    $sql = "SELECT * FROM activity_orderlists ao  
    JOIN activity a ON ao.a_no = a.a_no
    WHERE m_no = :m_no";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':m_no', $data['m_no']);
    $stmt->execute();
    $aOrdersRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 將查詢結果賦值給返回資料
    $returnData['data']['list'] = $aOrdersRows;
} catch (Exception $e) {
    // 捕獲異常並設置錯誤代碼和錯誤信息
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

// 將返回資料編碼為JSON並輸出
echo json_encode($returnData);
?>