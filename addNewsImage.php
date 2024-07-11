<?php
try {
    require_once('./connect_cid101g4.php');
    $recivedData = [
        'code' => 200,
        'msg' => '',
        'data' =>[]
    ];
    switch($_FILES['n_img']['error']) {
        case UPLOAD_ERR_OK:
            $dir ='../images/news-images';
            // $dir ='../images/assets';
            if(!file_exists($dir)) {
                mkdir("../images/news-images");
                // mkdir("../images/assets");
            };
            $fileName = $_FILES['n_img']['name'];
            $from = $_FILES['n_img']['tmp_name'];
            $to = "$dir/$fileName";
            copy($from,$to);
            echo "上傳成功!!";
            $recivedData['msg'] = '上傳成功!!';
            break;
            case UPLOAD_ERR_INI_SIZE:
                echo "上傳檔案太大, 不得超過", ini_get("upload_max_filesize"), "<br>"; 
                $recivedData['msg'] = '上傳檔案太大!!,請確認檔案是否小於等於2M';
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