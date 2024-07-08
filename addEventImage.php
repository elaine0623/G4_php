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
    // if($_FILES['a_img']['error'] === 0) {
    //     $dir = '../G4_backend/src/assets/image';
    //     if(!file_exists($dir)) {
    //         mkdir('../G4_backend/src/assets/image');
    //     };
    //     $fileName = $_FILES['a_img']['name'];
    //     $from = $_FILES['a_img']['tmp_name'];
    //     $to = "$dir/$fileName";
    //     copy($from,$to);
    //     echo "上傳成功~";
    // }else {
    //     echo"上傳失敗~";
    // }
    switch($_FILES['a_img']['error']) {
        case UPLOAD_ERR_OK:
            // $dir ='../G4_backend/src/assets/image';
            $dir ='../images/assets';
            if(!file_exists($dir)) {
                // mkdir("../G4_backend/src/assets/image");
                mkdir("../images/assets");
            };
            $fileName = $_FILES['a_img']['name'];
            $from = $_FILES['a_img']['tmp_name'];
            $to = "$dir/$fileName";
            // exit($to."===");
            copy($from,$to);
            echo "上傳成功!!";
            break;
            case UPLOAD_ERR_INI_SIZE:
                echo "上傳檔案太大, 不得超過", ini_get("upload_max_filesize"), "<br>"; 
                break;
            case UPLOAD_ERR_FORM_SIZE:
                echo "上傳檔案太大不得超過", $_POST["MAX_FILE_SIZE"], "<br>";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "上傳檔案不完整<br>";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "未挑選檔案<br>";
            default:
                echo "請通知網站維護人員<br>";
    }

} catch (Exception $e) {
    $recivedData['code'] = 10003;
    $recivedData['msg'] = $e->getMessage();
};
echo json_encode($recivedData, JSON_NUMERIC_CHECK);










?>