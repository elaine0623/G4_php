<?php
try {
    require_once('./connect_cid101g4.php');
    $recivedData = [
        'code' => 200,
        'msg' => '',
        'data' =>[]
    ];
    //抓前端傳回資料json
    $addNewsData = json_decode(file_get_contents('php://input'),true);
    //MYSQL指令，新增活動資料至後台
    $sql = "UPDATE news SET n_no = :n_no , n_time = :n_time, n_topic = :n_topic, n_article = :n_article , n_link = :n_link, n_status = :n_status, n_img= :n_img WHERE n_no = :n_no";

    $news = $pdo->prepare($sql);
    $news->bindValue(':n_no',$addNewsData['n_no']);
    $news->bindValue(':n_time',$addNewsData['n_time']);
    $news->bindValue(':n_img',$addNewsData['n_img']);
    $news->bindValue(':n_article',$addNewsData['n_article']);
    $news->bindValue(':n_topic',$addNewsData['n_topic']);
    $news->bindValue(':n_status',$addNewsData['n_status']);
    $news->bindValue(':n_link',$addNewsData['n_link']);
    $news->execute();
} catch (Exception $e) {
    $recivedData['code'] = 10003;
    $recivedData['msg'] = $e->getMessage();
};
echo json_encode($recivedData, JSON_NUMERIC_CHECK);










?>