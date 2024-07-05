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
    $sql = "DELETE  FROM activity  WHERE a_no = :a_no";
    $event = $pdo->prepare($sql);
    $event->bindValue(':a_no',$addEventData['a_no']);
    $event->execute();
} catch (Exception $e) {
    $recivedData['code'] = 10003;
    $recivedData['msg'] = $e->getMessage();
};
echo json_encode($recivedData, JSON_NUMERIC_CHECK);










?>