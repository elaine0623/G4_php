<?php
try {
    require_once("./connect_cid101g4.php");
    $returnData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];
    //抓前端傳來的資料
    $data = json_decode(file_get_contents('php://input'), true);
    $checkAccount = "SELECT * FROM member WHERE m_account = :m_account AND m_password = :m_password";
    $stmt = $pdo->prepare($checkAccount);
    $stmt->bindValue(':m_account', $data['acc']);
    $stmt->bindValue(':m_password', md5($data['lpsw']));
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        //判斷是否停權
        $permission = $userData['m_status'];
        if ($permission == 0) {
            $returnData['code'] = 10004;
            $returnData['msg'] = "此帳號已停權";
        }else{
            session_start();
            $_SESSION = $userData;
            $returnData['code'] = 200;
            $returnData['data'] = $_SESSION;
        }
    }else{
        $returnData['code'] = 10002;
        $returnData['msg'] = "帳號或密碼錯誤";
    }
} catch (Exception $e) { // 更廣泛地捕獲異常
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData);
