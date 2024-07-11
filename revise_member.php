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
    if(!isset($data['name']) || empty( $data['name'])){
        $returnData['code'] = 10005;
        $returnData['msg'] = "姓名不得為空";
    }else{
        if(isset($data['psw']) && !empty($data['psw'])){
            $sql = "UPDATE `member` SET `m_name`=:m_name, `m_phone`=:m_phone, `m_password`=:m_password, `m_birth`=:m_birth, `m_add`=:m_add WHERE `m_id`=:m_id";
            $member = $pdo->prepare($sql);
            $member->bindValue(':m_id', $data['m_id']);
            $member->bindValue(':m_name', $data['name']);
            $member->bindValue(':m_phone', $data['phone']);
            $member->bindValue(':m_password', md5($data['psw']));
            $member->bindValue(':m_birth', $data['m_birth']);
            $member->bindValue(':m_add', $data['m_add']);
            $member->execute();
            $returnData['msg'] ='更新成功!';
        }else{
            $sql = "UPDATE `member` SET `m_name`=:m_name, `m_phone`=:m_phone, `m_birth`=:m_birth, `m_add`=:m_add WHERE `m_id`=:m_id";
            $member = $pdo->prepare($sql);
            $member->bindValue(':m_id', $data['m_id']);
            $member->bindValue(':m_name', $data['name']);
            $member->bindValue(':m_phone', $data['phone']);
            $member->bindValue(':m_birth', $data['m_birth']);
            $member->bindValue(':m_add', $data['m_add']);
            $member->execute();
            $returnData['msg'] ='更新成功!';
        }

        $sq12 ="SELECT * FROM`member`WHERE `m_id`=:m_id";
        $member2 = $pdo->prepare($sq12);
        $member2->bindValue(':m_id', $data['m_id']);
        $member2->execute();
        $returnData['data'] = $member2->fetch(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) { // 更廣泛地捕獲異常
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData);