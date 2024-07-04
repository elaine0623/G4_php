<?php
// farm.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once("./connect_cid101g4.php");

// 啟用錯誤報告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$returnData = [
    'code' => 200,
    'msg' => '',
    'data' => []
];

// 獲取請求方法和數據
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// 記錄接收到的數據
error_log('Received method: ' . $method);
error_log('Received data: ' . print_r($input, true));

try {
    switch ($method) {
        case 'GET':
            $returnData['data']['list'] = getAllFarms($pdo);
            break;
        case 'POST':
            $result = saveFarm($pdo, $input);
            $returnData['msg'] = $result['message'];
            if (!$result['success']) {
                $returnData['code'] = 400;
            }
            break;
        case 'PUT':
            parse_str($_SERVER['QUERY_STRING'], $queryParams);
            $farmId = $queryParams['id'] ?? null;
            if ($farmId) {
                $input['f_no'] = $farmId;
                $result = saveFarm($pdo, $input);
                $returnData['msg'] = $result['message'];
                if (!$result['success']) {
                    $returnData['code'] = 400;
                }
            } else {
                $returnData['code'] = 400;
                $returnData['msg'] = '缺少農場 ID';
            }
            break;
        case 'DELETE':
            parse_str($_SERVER['QUERY_STRING'], $queryParams);
            $farmId = $queryParams['id'] ?? null;
            if ($farmId) {
                $result = deleteFarm($pdo, $farmId);
                $returnData['msg'] = $result['message'];
                if (!$result['success']) {
                    $returnData['code'] = 400;
                }
            } else {
                $returnData['code'] = 400;
                $returnData['msg'] = '缺少農場 ID';
            }
            break;
        default:
            $returnData['code'] = 405;
            $returnData['msg'] = '無效的請求方法';
    }
} catch (Exception $e) {
    $returnData['code'] = 500;
    $returnData['msg'] = '服務器錯誤: ' . $e->getMessage();
}

echo json_encode($returnData);

function getAllFarms($pdo) {
    $sql = "SELECT * FROM farm ORDER BY f_no";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function saveFarm($pdo, $farmData) {
  try {
      $pdo->beginTransaction();
      
      error_log('Farm data received in saveFarm: ' . print_r($farmData, true));

      // 如果沒有 f_no，則生成一個新的唯一 f_no
      if (empty($farmData['f_no'])) {
          $maxSql = "SELECT MAX(f_no) AS max_f_no FROM farm";
          $maxStmt = $pdo->query($maxSql);
          $maxFno = $maxStmt->fetchColumn();
          $farmData['f_no'] = $maxFno ? $maxFno + 1 : 1;
      }

      // 檢查農場是否已存在
      $checkSql = "SELECT COUNT(*) FROM farm WHERE f_no = :f_no";
      $checkStmt = $pdo->prepare($checkSql);
      $checkStmt->execute([':f_no' => $farmData['f_no']]);
      $exists = $checkStmt->fetchColumn();

      if ($exists) {
          // 更新現有農場
          $farmSql = "UPDATE farm SET 
                      f_name = :f_name,
                      f_loc = :f_loc, 
                      f_farmer = :f_farmer,
                      f_intro = :f_intro,
                      f_img = :f_img,
                      f_status = :f_status
                      WHERE f_no = :f_no";
      } else {
          // 插入新農場
          $farmSql = "INSERT INTO farm
                      (f_no, f_name, f_loc, f_farmer, f_intro, f_img, f_status)
                      VALUES (:f_no, :f_name, :f_loc, :f_farmer, :f_intro, :f_img, :f_status)";
      }

      $farmStmt = $pdo->prepare($farmSql);
      $result = $farmStmt->execute([
          ':f_no' => $farmData['f_no'],
          ':f_name' => $farmData['f_name'] ?? '',
          ':f_loc' => $farmData['f_loc'] ?? '',
          ':f_farmer' => $farmData['f_farmer'] ?? '',
          ':f_intro' => $farmData['f_intro'] ?? '',
          ':f_img' => $farmData['f_img'] ?? '',
          ':f_status' => $farmData['f_status'] ?? '1'
      ]);

      error_log('SQL execution result: ' . ($result ? 'Success' : 'Failure'));

      $pdo->commit();
      return ['success' => $result, 'message' => '農場保存成功。'];
  } catch (Exception $e) {
      $pdo->rollBack();
      error_log('Error in saveFarm: ' . $e->getMessage());
      return ['success' => false, 'message' => '保存農場時發生錯誤: ' . $e->getMessage()];
  }
}
function deleteFarm($pdo, $farmId) {
    try {
        $pdo->beginTransaction();

        $sql = "DELETE FROM farm WHERE f_no = :f_no";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':f_no' => $farmId]);

        $pdo->commit();
        return ['success' => true, 'message' => '農場刪除成功。'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => '刪除農場時發生錯誤: ' . $e->getMessage()];
    }
}
?>