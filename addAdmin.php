<?php
try {
    require_once("./connect_cid101g4.php");
    $returnData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];

    // 抓前端傳來的資料
    $data = json_decode(file_get_contents('php://input'), true);

    // 自動新增 adminAccount
    $newAccount = addAdminAccount();

    // 插入新帳號
    $sql = "INSERT INTO `admin` (`am_account`, `am_password`, `am_level`, `am_status`) VALUES (:am_account, :am_password, :am_level, :am_status)";
    $admin = $pdo->prepare($sql);
    $admin->bindValue(':am_account', $newAccount);
    $admin->bindValue(':am_password', $data['am_password']);
    $admin->bindValue(':am_level', $data['am_level']);
    $admin->bindValue(':am_status', $data['am_status']);
    $admin->execute();

    // 查詢新插入的帳號
    // $account = "SELECT `am_account` FROM admin WHERE am_account = :am_account";
    // $stmt = $pdo->prepare($account);
    // $stmt->bindParam(':am_account', $newAccount);
    // $stmt->execute();
    // $returnData['data'] = $stmt->fetch(PDO::FETCH_ASSOC);
        

} catch (Exception $e) {
    // 更廣泛地捕獲異常
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData, JSON_NUMERIC_CHECK);

function addAdminAccount() {
    global $pdo;
    $sql2 = "SELECT `am_no` FROM admin ORDER BY `am_no` DESC LIMIT 1";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    $data = $stmt2->fetch(PDO::FETCH_ASSOC);
    $id = (int)$data['am_no'] + 1;
    $id = 'admin' . str_pad($id, 3, '0', STR_PAD_LEFT);
    return $id;
}
