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
    if (!isset($data['am_no']) || !isset($data['am_status'])) {
        $returnData['code'] = 10004;
        $returnData['msg'] = '缺少必需的參數';
        echo json_encode($returnData, JSON_NUMERIC_CHECK);
        exit();
    }

    // 更新管理員狀態
    $sql = "UPDATE admin SET am_status = :am_status WHERE am_no = :am_no";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':am_status', $data['am_status']);
    $stmt->bindParam(':am_no', $data['am_no']);
    $stmt->execute();

    // 檢查是否更新
    if ($stmt->rowCount() > 0) {
        $returnData['msg'] = "更新成功";
    } else {
        $returnData['code'] = 10001;
        $returnData['msg'] = "更新失败或无变化";
    }
} catch (Exception $e) {
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData, JSON_NUMERIC_CHECK);
?>