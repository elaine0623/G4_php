<?php
try {
    require_once('./connect_cid101g4.php');
    $recivedData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];

    if (isset($_FILES['m_img'])) {
        switch ($_FILES['m_img']['error']) {
            case UPLOAD_ERR_OK:
                // $dir = 'D:/G4-frontend-1/G4_frontend/src/assets/image/'; //找不到路徑QQ
                $dir = '../images/assets'; 
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true); 
                }
                $fileName = $_FILES['m_img']['name'];
                $from = $_FILES['m_img']['tmp_name'];
                $to = "$dir/$fileName";
                if (copy($from, $to)) {
                    $recivedData['msg'] = "上傳成功!!";
                    $recivedData['data']['filePath'] = $to;
                } else {
                    $recivedData['code'] = 10001;
                    $recivedData['msg'] = "上傳失敗";
                }
                break;
            case UPLOAD_ERR_INI_SIZE:
                $recivedData['code'] = 10002;
                $recivedData['msg'] = "上傳檔案太大, 不得超過" . ini_get("upload_max_filesize");
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $recivedData['code'] = 10003;
                $recivedData['msg'] = "上傳檔案太大不得超過" . $_POST["MAX_FILE_SIZE"];
                break;
            case UPLOAD_ERR_PARTIAL:
                $recivedData['code'] = 10004;
                $recivedData['msg'] = "上傳檔案不完整";
                break;
            case UPLOAD_ERR_NO_FILE:
                $recivedData['code'] = 10005;
                $recivedData['msg'] = "未挑選檔案";
                break;
            default:
                $recivedData['code'] = 10006;
                $recivedData['msg'] = "請通知網站維護人員";
        }
    } else {
        $recivedData['code'] = 10007;
        $recivedData['msg'] = "未找到上傳的文件。";
    }

} catch (Exception $e) {
    $recivedData['code'] = 10008;
    $recivedData['msg'] = $e->getMessage();
}
header('Content-Type: application/json');
echo json_encode($recivedData, JSON_NUMERIC_CHECK);
?>
