<?php
try {
    require_once('./connect_cid101g4.php');
    $receivedData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];

    // 檢查是否有文件上傳
    if (!isset($_FILES['f_img'])) {
        throw new Exception("未上傳任何文件");
    }

    // 設置文件上傳目錄
    // $uploadDir = 'D:/G4-frontend-1/G4_frontend/src/assets/image';
    $uploadDir = '../images/farm-img';
    
    // 檢查目錄是否存在，不存在則創建
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception("創建上傳目錄失敗");
        }
    }

    // 處理圖片上傳
    if (isset($_FILES['f_img'])) {
        handleImageUpload('f_img', $uploadDir, $receivedData);
    }

    // 回傳結果
    echo json_encode($receivedData, JSON_NUMERIC_CHECK);
} catch (Exception $e) {
    // 錯誤處理
    $receivedData['code'] = 10003;
    $receivedData['msg'] = $e->getMessage();
    echo json_encode($receivedData, JSON_NUMERIC_CHECK);
}

/**
 * 處理圖片上傳的函數
 * @param string $fileKey 文件鍵名
 * @param string $uploadDir 上傳目錄
 * @param array $receivedData 回傳的數據
 * @throws Exception
 */
function handleImageUpload($fileKey, $uploadDir, &$receivedData) {
    switch($_FILES[$fileKey]['error']) {
        case UPLOAD_ERR_OK:
            $fileName = $_FILES[$fileKey]['name'];
            $tmpName = $_FILES[$fileKey]['tmp_name'];
            $destination = "$uploadDir/$fileName";

            // 移動上傳的文件到指定目錄
            if (move_uploaded_file($tmpName, $destination)) {
                $receivedData['data'][$fileKey] = [
                    'fileName' => $fileName,
                    'path' => $destination
                ];
                $receivedData['msg'] .= "$fileKey 上傳成功!! ";
            } else {
                throw new Exception("$fileKey 文件移動失敗");
            }
            break;
        case UPLOAD_ERR_INI_SIZE:
            throw new Exception("$fileKey 上傳檔案太大, 不得超過" . ini_get("upload_max_filesize"));
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception("$fileKey 上傳檔案太大不得超過" . $_POST["MAX_FILE_SIZE"]);
        case UPLOAD_ERR_PARTIAL:
            throw new Exception("$fileKey 上傳檔案不完整");
        case UPLOAD_ERR_NO_FILE:
            throw new Exception("$fileKey 未挑選檔案");
        default:
            throw new Exception("$fileKey 上傳時發生未知錯誤");
    }
}
?>
