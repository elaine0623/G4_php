<?php
try {
    require_once('./connect_cid101g4.php');
    $recivedData = [
        'code' => 200,
        'msg' => '',
        'data' =>[]
    ];
    $sql = "SELECT * 
    FROM news 
    WHERE n_status = 1 
    ORDER BY n_time DESC 
    LIMIT 4";
    $events = $pdo->prepare($sql);
    $events->execute();
    $eventsData = $events->fetchAll(PDO::FETCH_ASSOC);
    $recivedData['data']['list'] = $eventsData;
} catch (Exception $e) {
    $recivedData['code'] = 10003;
    $recivedData['msg'] = $e->getMessage();
};
echo json_encode($recivedData);











?>