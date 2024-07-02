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
    
    // 或去當前時間
    $currentDateTime = date('Y-m-d H:i:s');
    
    // 插入活動訂單資料
    $sql = "INSERT INTO `activity-orderlists` (`a_no`, `m_no`, `ao_count`, `ao_status`, `a_date`, `ao_ordertime`, `ao_totalfee`) VALUES (:a_no, :m_no, :ao_count, :ao_status, :a_date, :ao_ordertime, :ao_totalfee)";
    $aOrder = $pdo->prepare($sql);
    $aOrder->bindValue(':a_no', $data['a_no']);
    $aOrder->bindValue(':m_no', $data['m_no']);
    $aOrder->bindValue(':ao_count', $data['ao_count']);
    $aOrder->bindValue(':ao_status', $data['ao_status']);
    $aOrder->bindValue(':a_date', $data['a_date']);
    $aOrder->bindValue(':ao_ordertime', $currentDateTime);
    $aOrder->bindValue(':ao_totalfee', $data['ao_totalfee']);
    $aOrder->execute();
    
    // 獲取剛插入的記錄
    $ao_no = $pdo->lastInsertId();
    $sq12 = "SELECT * FROM `activity-orderlists` WHERE `ao_no` = :ao_no";
    $member2 = $pdo->prepare($sq12);
    $member2->bindValue(':ao_no', $ao_no);
    $member2->execute();
    $returnData['data'] = $member2->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) { // 更廣泛地捕獲異常
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData, JSON_NUMERIC_CHECK);
?>
