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
    
    $checkdata = "SELECT p_no FROM member_favorite WHERE `p_no`=:p_no AND `m_no`=:userNo";
    $stmt1 = $pdo->prepare($checkdata);
    $stmt1->bindParam(':p_no', $data['p_no']);
    $stmt1->bindParam(':userNo', $data['userNo']);
    $stmt1->execute();
    if ($stmt1->rowCount()==0) {
        $sqladd = "INSERT INTO `member_favorite` (`p_no`,`fav`,`cart`,`m_no`)VALUES(:p_no,:isImage1,:isaddCart,:userNo)";
        $stmt2 = $pdo->prepare($sqladd);
        $stmt2->bindParam(':p_no', $data['p_no']);
        $stmt2->bindParam(':isImage1', $data['isImage1']);
        $stmt2->bindParam(':isaddCart', $data['isaddCart']);
        $stmt2->bindParam(':userNo', $data['userNo']);
        $stmt2->execute();
    }
    else{
        $sql = "UPDATE member_favorite SET `fav`=:isImage1 ,`cart`=:isaddCart WHERE `p_no`=:p_no AND `m_no`=:userNo";
        $member = $pdo->prepare($sql);
        $member->bindValue(':isImage1', $data['isImage1']);
        $member->bindValue(':isaddCart', $data['isaddCart']);
        $member->bindValue(':p_no', $data['p_no']);
        $member->bindValue(':userNo', $data['userNo']);
        $member->execute();
        $returnData['msg'] = "資料庫已更新";
        //回傳data資料
        $result = "SELECT `fav`,`cart` FROM member_favorite WHERE `p_no`=:p_no AND `m_no`=:userNo";
        $stmt = $pdo->prepare($result);
        $stmt->bindValue(':p_no', $data['p_no']);
        $stmt->bindValue(':userNo', $data['userNo']);
        $stmt->execute();
        $returnData['data']['list'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) { // 更廣泛地捕獲異常
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData);
