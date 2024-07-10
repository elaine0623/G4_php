<?php
try {
    require_once('./connect_cid101g4.php');
    $recivedData = [
        'code' => 200,
        'msg' => '',
        'data' =>[]
    ];
    //MYSQL指令，把後臺活動資料拉來php(以row and column形式)
    $sql = "SELECT * FROM activity WHERE a_status = 1" ;
    $events = $pdo->prepare($sql);
    $events->execute();
    //抓取資料庫以上線的全部資料
    //
    $eventsData = $events->fetchAll(PDO::FETCH_ASSOC);
    $recivedData['data']['list'] = $eventsData;
} catch (Exception $e) {
    $recivedData['code'] = 10003;
    $recivedData['msg'] = $e->getMessage();
};
echo json_encode($recivedData);











?>