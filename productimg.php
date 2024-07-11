<?php
// productimg.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once('./connect_cid101g4.php');
    $receivedData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];

    // 檢查是否有文件上傳
    if (!isset($_FILES['pi_img'])) {
        throw new Exception("未上傳任何文件");
    }

    // 設置文件上傳目錄
    //$uploadDir = 'D:\g4_0607\G4_frontend\src\assets\image';
    $uploadDir =  '../images';

    // 檢查目錄是否存在,不存在則創建
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception("創建上傳目錄失敗");
        }
    }

    // 處理產品圖片上傳
    handleImageUpload('pi_img', $uploadDir, $receivedData);

    // 返回結果
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
 * @param array $receivedData 返回的數據
 * @throws Exception
 */
function handleImageUpload($fileKey, $uploadDir, &$receivedData) {
    // 支持多文件上傳
    $files = $_FILES[$fileKey];
    $fileCount = is_array($files['name']) ? count($files['name']) : 1;

    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
        $fileError = is_array($files['error']) ? $files['error'][$i] : $files['error'];
        $fileTmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];

        switch($fileError) {
            case UPLOAD_ERR_OK:
                $destination = "$uploadDir/$fileName";

                // 移動上傳的文件到指定目錄
                if (move_uploaded_file($fileTmpName, $destination)) {
                    $receivedData['data'][$fileKey][] = [
                        'fileName' => $fileName,
                        'path' => $destination
                    ];
                    $receivedData['msg'] .= "$fileName 上傳成功!! ";
                } else {
                    throw new Exception("$fileName 文件移動失敗");
                }
                break;
            case UPLOAD_ERR_INI_SIZE:
                throw new Exception("$fileName 上傳文件太大, 不得超過" . ini_get("upload_max_filesize"));
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception("$fileName 上傳文件太大不得超過" . $_POST["MAX_FILE_SIZE"]);
            case UPLOAD_ERR_PARTIAL:
                throw new Exception("$fileName 上傳文件不完整");
            case UPLOAD_ERR_NO_FILE:
                throw new Exception("$fileName 未選擇文件");
            default:
                throw new Exception("$fileName 上傳時發生未知錯誤");
        }
    }
}
?>