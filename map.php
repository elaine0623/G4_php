<?php
header('Content-Type: application/json');

try {
    require_once('./connect_cid101g4.php');
    
    $receivedData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];

    $sql = "SELECT *
            FROM farm f
            WHERE f.f_status = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $farmsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $receivedData['data']['list'] = $farmsData;

} catch (PDOException $e) {
    $receivedData['code'] = 10003;
    $receivedData['msg'] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $receivedData['code'] = 10004;
    $receivedData['msg'] = "General error: " . $e->getMessage();
}

echo json_encode($receivedData);
?>