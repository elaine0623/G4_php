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
    $sql = "UPDATE activity SET a_no = :a_no , c_no = :c_no, a_img = :a_img, a_name = :a_name , a_loc = :a_loc, a_max= :a_max, a_fee = :a_fee, a_time = :a_time, a_start_date = :a_start_date, a_end_date = :a_end_date, a_teacher = :a_teacher, a_signups = :a_signups, a_signupe = :a_signupe, a_info = :a_info, a_info1 = :a_info1, a_info2= :a_info2, a_info3= :a_info3, a_rules1 = :a_rules1, a_rules2 = :a_rules2, a_rules3 =:a_rules3, a_status = :a_status WHERE a_no = :a_no";

    $event = $pdo->prepare($sql);
    $event->bindValue(':a_no',$addEventData['a_no']);
    $event->bindValue(':c_no',$addEventData['c_no']);
    $event->bindValue(':a_img',$addEventData['a_img']);
    $event->bindValue(':a_name',$addEventData['a_name']);
    $event->bindValue(':a_loc',$addEventData['a_loc']);
    $event->bindValue(':a_max',$addEventData['a_max']);
    $event->bindValue(':a_fee',$addEventData['a_fee']);
    $event->bindValue(':a_time',$addEventData['a_time']);
    $event->bindValue(':a_start_date',$addEventData['a_start_date']);
    $event->bindValue(':a_end_date',$addEventData['a_end_date']);
    $event->bindValue(':a_teacher',$addEventData['a_teacher']);
    $event->bindValue(':a_signups',$addEventData['a_signups']);
    $event->bindValue(':a_signupe',$addEventData['a_signupe']);
    $event->bindValue(':a_info',$addEventData['a_info']);
    $event->bindValue(':a_info1',$addEventData['a_info1']);
    $event->bindValue(':a_info2',$addEventData['a_info2']);
    $event->bindValue(':a_info3',$addEventData['a_info3']);
    $event->bindValue(':a_rules1',$addEventData['a_rules1']);
    $event->bindValue(':a_rules2',$addEventData['a_rules2']);
    $event->bindValue(':a_rules3',$addEventData['a_rules3']);
    $event->bindValue(':a_status',$addEventData['a_status']);
    $event->execute();
} catch (Exception $e) {
    $recivedData['code'] = 10003;
    $recivedData['msg'] = $e->getMessage();
};
echo json_encode($recivedData, JSON_NUMERIC_CHECK);










?>