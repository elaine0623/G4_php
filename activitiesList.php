<?php
try {
    require_once('./connect_cid101g4.php');
    $recivedData = [
        'code' => 200,
        'msg' => '',
        'data' =>[]
    ];
    //MYSQL指令，把後臺活動資料拉來php(以row and column形式)
    $sql = "SELECT * FROM activity WHERE a_status = 1";
    $events = $pdo->prepare($sql);
    $events->execute();
    //抓取資料庫以上線的全部資料
    $eventsData = $events->fetchAll(PDO::FETCH_ASSOC);
    // print_r($eventsData);exit;顯示fetch回來的資料陣列
    // foreach($eventsData as $indx => $key) {
    //     $eventNo = $key['a_no'];
    //     $sql2 = "SELECT `a_start_date`, `a_end_date` 
    //     FROM activity 
    //     WHERE a_no = $eventNo";
    //     $eventDate = $pdo->prepare($sql2);
    //     $eventDate->execute();
    //     $date = $eventDate->fetchAll(PDO::FETCH_ASSOC);
    //     $recivedData['data']['list'] = $eventDate;
    // };
    $recivedData['data']['list'] = $eventsData;
} catch (Exception $e) {
    $recivedData['code'] = 10003;
    $recivedData['msg'] = $e->getMessage();
};
echo json_encode($recivedData);











?>