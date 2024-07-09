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
    
    if (!$data || !isset($data['m_no'])) {
        throw new Exception('Invalid input');
    }
    
    $sq1 ="SELECT * FROM `member` WHERE `m_no` = :m_no";
    $member = $pdo->prepare($sq1);
    $member->bindValue(':m_no', $data['m_no']);
    $member->execute();
    
    $memberData = $member->fetch(PDO::FETCH_ASSOC);
    
    if ($memberData) {
        $returnData['data'] = $memberData;
    } else {
        $returnData['code'] = 404;
        $returnData['msg'] = 'Member not found';
    }
    
} catch (Exception $e) { // 更廣泛地捕獲異常
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($returnData, JSON_NUMERIC_CHECK);
?>


