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
    if (!isset($data['ao_no']) || !isset($data['ao_status'])) {
        $returnData['code'] = 10004;
        $returnData['msg'] = '缺少必需的參數';
        echo json_encode($returnData, JSON_NUMERIC_CHECK);
        exit();
    }

    // 更新活動狀態
    $sql = "UPDATE activity_orderlists SET ao_status = :ao_status WHERE ao_no = :ao_no";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ao_status', $data['ao_status']);
    $stmt->bindParam(':ao_no', $data['ao_no']);
    $stmt->execute();

    //檢查是否更新
    if ($stmt->rowCount() > 0) {
        $returnData['msg'] = "更新成功";
    } else {
        $returnData['code'] = 10001;
        $returnData['msg'] = "更新失敗或無變化";
    }

    // 獲取當前活動的a_attendee值
    $sqlSelect = "SELECT `a_attendee` FROM `activity` WHERE `a_no` = :a_no";
    $stmtSelect = $pdo->prepare($sqlSelect);
    $stmtSelect->bindValue(':a_no', $data['a_no']);
    $stmtSelect->execute();
    $currentAttendee = $stmtSelect->fetchColumn();
    
    // 調試信息
    if ($currentAttendee === false) {
        throw new Exception("無法獲取當前活動的a_attendee值");
    }
    if (!isset($data['ao_count'])) {
            throw new Exception("ao_count未設置");
    }
    // 計算新的a_attendee值
    $newAttendee = $currentAttendee - $data['ao_count'];
        
    // 更新活動的a_attendee值
    $sqlUpdate = "UPDATE `activity` SET `a_attendee` = :newAttendee WHERE `a_no` = :a_no";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->bindValue(':newAttendee', $newAttendee);
    $stmtUpdate->bindValue(':a_no', $data['a_no']);
    $stmtUpdate->execute();
} catch (Exception $e) {
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData, JSON_NUMERIC_CHECK);
?>