<?php
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST');
// header("Access-Control-Allow-Headers: X-Requested-With");

try {
    require_once("./connect_cid101g4.php");
    $returnData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];
    //抓前端傳來的資料
    $data = json_decode(file_get_contents('php://input'), true);
    //查詢資料庫內是否有相同帳號,使用prepare語法重新編議:m_account參數(比對前端帳號欄位$data['email'])
    $checkAccount = "SELECT m_account FROM member WHERE m_account = :m_account";
    $stmt = $pdo->prepare($checkAccount);
    $stmt->bindParam(':m_account', $data['email']);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        // 如果email已存在，返回錯誤消息
        $returnData['code'] = 10001;
        $returnData['msg'] = "此email已被使用，請重新輸入";
    }else{
        $sql = "INSERT INTO `member` (`m_no`,`m_name`, `m_account`, `m_password`) VALUES (:m_no, :m_name, :m_account, :m_password)";
        $member = $pdo->prepare($sql);
        $member->bindValue(':m_no', addMemberNo());
        $member->bindValue(':m_name', $data['name']);
        $member->bindValue(':m_account', $data['email']);
        $member->bindValue(':m_password', md5($data['psw']));
        $member->execute();

        $account = "SELECT `m_name` FROM member WHERE m_account = :m_account";
        $stmt = $pdo->prepare($account);
        $stmt->bindParam(':m_account', $data['email']);
        $stmt->execute();
        $returnData['data'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { // 更廣泛地捕獲異常
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData);

function addMemberNo(){
    $sql2 ="SELECT COUNT(`m_account`) FROM member WHERE m_status = 1"
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    // 獲取查詢結果
    $count = $stmt2->fetchColumn();
    
    // 打印查詢結果
    echo $count;
    return 'ddd';
    
}