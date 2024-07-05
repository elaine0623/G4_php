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

    // 檢查參數是否存在
    if (!isset($data['po_no']) || !isset($data['po_status'])) {
        $returnData['code'] = 10004;
        $returnData['msg'] = '缺少必需的參數';
        echo json_encode($returnData, JSON_NUMERIC_CHECK);
        exit();
    }

    // 更新活動狀態
    $sql = "UPDATE p_orders SET po_status = :po_status WHERE po_no = :po_no";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':po_status', $data['po_status']);
    $stmt->bindParam(':po_no', $data['po_no']);
    $stmt->execute();

    //檢查是否更新
    if ($stmt->rowCount() > 0) {
        $returnData['msg'] = "更新成功";
    } else {
        $returnData['code'] = 10001;
        $returnData['msg'] = "更新失敗或無變化";
    }

} catch (Exception $e) {
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData, JSON_NUMERIC_CHECK);
?>