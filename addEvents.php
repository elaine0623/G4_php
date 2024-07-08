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
    $sql = "INSERT INTO `activity` (`a_no`, `c_no`, `a_img`, `a_name`, `a_loc`, `a_max`, `a_fee`, `a_time`, `a_start_date`, `a_end_date`, `a_teacher`, `a_signups`, `a_signupe`, `a_info`, `a_info1`, `a_info2`, `a_info3`, `a_rules1`, `a_rules2`, `a_rules3`, `a_status`) VALUES (:a_no, :c_no, :a_img, :a_name, :a_loc, :a_max, :a_fee, :a_time, :a_start_date, :a_end_date, :a_teacher, :a_signups, :a_signupe, :a_info, :a_info1, :a_info2, :a_info3, :a_rules1, :a_rules2, :a_rules3, :a_status) " ;

    // INSERT INTO `activity` (`a_no`, `c_no`, `a_name`, `a_img`, `a_loc`, `a_max`, `a_fee`, `a_date`, `a_time`, `a_teacher`, `a_signups`, `a_signupe`, `a_info`, `a_info1`, `a_info2`, `a_info3`, `a_rules1`, `a_rules2`, `a_rules3`, `a_status`, `a_attendee`, `a_start_date`, `a_end_date`, `isVisable`) VALUES ('6', '講座', '安安', '安安.jpg', '桃園市', '2', '2', '2024-07-04', '2024-07-03 05:31:26.000000', '安安', '2024-07-29', '2024-07-26', '安安', '安安', '安', '安', '安', '安', '安', '1', '20', '2024-07-18', '2024-07-21', 'true');


    $event = $pdo->prepare($sql);
    $event->bindValue(':a_no',$addEventData['a_no']);
    $event->bindValue(':c_no',$addEventData['c_no']);
    $event->bindValue(':a_img',$addEventData['a_img']);//回來為檔名
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