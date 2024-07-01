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
    // UPDATE member SET m_name="安安" WHERE m_id=3;
    $sql = "UPDATE `member` SET `m_name`=:m_name, `m_phone`=:m_phone, `m_password`=:m_password WHERE
    `m_id`=:m_id";
    $member = $pdo->prepare($sql);
    $member->bindValue(':m_id', $data['m_id']);
    $member->bindValue(':m_name', $data['name']);
    $member->bindValue(':m_phone', $data['phone']);
    $member->bindValue(':m_password', md5($data['psw']));
    $member->execute();

    $sq12 ="SELECT * FROM`member`WHERE `m_id`=:m_id";
    $member2 = $pdo->prepare($sq12);
    $member2->bindValue(':m_id', $data['m_id']);
    $member2->execute();
    $returnData['data'] = $member2->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) { // 更廣泛地捕獲異常
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData,JSON_NUMERIC_CHECK);