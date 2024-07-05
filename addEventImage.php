<?php
try {
    require_once('./connect_cid101g4.php');
    $recivedData = [
        'code' => 200,
        'msg' => '',
        'data' =>[]
    ];
    //抓前端傳回資料json
    // $addEventData = json_decode(file_get_contents('php://input'),true);
    if($_FILES['a_img']['error'] === 0) {
        $dir = '../images/assets';
        if(! file_exists($dir)) {
            mkdir('../images/assets');
        };
        $fileName = $_FILES['a_img']['name'];
        $from = $_FILES['a_img']['tmp_name'];
        $to = "$dir/$fileName";
        copy($from,$to);
        echo "上傳成功~";
    }else {
        echo"上傳失敗~";
    }

} catch (Exception $e) {
    $recivedData['code'] = 10003;
    $recivedData['msg'] = $e->getMessage();
};
echo json_encode($recivedData, JSON_NUMERIC_CHECK);










?>