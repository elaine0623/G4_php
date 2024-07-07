<?php
try {
    require_once('./connect_cid101g4.php');
    $recivedData = [
        'code' => 200,
        'msg' => '',
        'data' =>[]
    ];
    $sql = "SELECT * FROM new";
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