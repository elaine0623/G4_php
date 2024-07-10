<?php
try {
    require_once('./connect_cid101g4.php');
    $recivedData = [
        'code' => 200,
        'msg' => '',
        'data' =>[]
    ];
    //抓前端傳回資料json
    $addEventData = json_decode(file_get_contents('php://input'),true);
    //MYSQL指令，新增活動資料至後台
    $sql = "UPDATE member SET m_img = :m_img WHERE m_no = :m_no";

    $event = $pdo->prepare($sql);
    $event->bindValue(':m_no',$addEventData['m_no']);
    $event->bindValue(':m_img',$addEventData['m_img']);
    $event->execute();
} catch (Exception $e) {
    $recivedData['code'] = 10003;
    $recivedData['msg'] = $e->getMessage();
};
echo json_encode($recivedData, JSON_NUMERIC_CHECK);










?>