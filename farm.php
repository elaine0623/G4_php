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
error_log('接收到的方法: ' . $method);
error_log('接收到的數據: ' . print_r($input, true));

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['action']) && $_GET['action'] === 'getRegions') {
                $returnData['data']['regions'] = getRegions($pdo);
            } else {
                $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
                $returnData['data'] = getAllFarms($pdo, $page, $limit);
            }
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
    $returnData['msg'] = '伺服器錯誤: ' . $e->getMessage();
}

echo json_encode($returnData);

function getRegions($pdo) {
    try {
        $sql = "SELECT city_no, city_name FROM farm_category ORDER BY city_no";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("獲取地區數據時發生錯誤: " . $e->getMessage());
        return [];
    }
}

function getAllFarms($pdo, $page = 1, $limit = 10) {
    $offset = ($page - 1) * $limit;
    $sql = "SELECT f.*, fc.city_name 
            FROM farm f 
            LEFT JOIN farm_category fc ON f.data_name = fc.city_name 
            ORDER BY f.f_no
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $farms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 獲取總記錄數
    $countSql = "SELECT COUNT(*) FROM farm";
    $countStmt = $pdo->query($countSql);
    $totalCount = $countStmt->fetchColumn();

    return [
        'list' => $farms,
        'total' => $totalCount,
        'page' => $page,
        'limit' => $limit
    ];
}

function validateFarmData($farmData) {
    $errors = [];
    if (empty($farmData['f_name'])) {
        $errors[] = '農場名稱不能為空';
    }
    if (empty($farmData['f_farmer'])) {
        $errors[] = '農場小農姓名不能為空';
    }
    if (empty($farmData['data_name'])) {
        $errors[] = '農場地區不能為空';
    }
    return $errors;
}

function saveFarm($pdo, $farmData) {
    try {
        $pdo->beginTransaction();
        
        $errors = validateFarmData($farmData);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }

        error_log('在 saveFarm 中接收到的農場數據: ' . print_r($farmData, true));

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
                        f_farmer = :f_farmer,
                        f_intro = :f_intro,
                        f_img = :f_img,
                        f_status = :f_status,
                        data_name = :data_name
                        WHERE f_no = :f_no";
        } else {
            // 插入新農場
            $farmSql = "INSERT INTO farm
                        (f_no, f_name, f_farmer, f_intro, f_img, f_status, data_name)
                        VALUES (:f_no, :f_name, :f_farmer, :f_intro, :f_img, :f_status, :data_name)";
        }

        $farmStmt = $pdo->prepare($farmSql);
        $result = $farmStmt->execute([
            ':f_no' => $farmData['f_no'],
            ':f_name' => $farmData['f_name'],
            ':f_farmer' => $farmData['f_farmer'],
            ':f_intro' => $farmData['f_intro'] ?? '',
            ':f_img' => $farmData['f_img'] ?? '',
            ':f_status' => $farmData['f_status'] ?? '1',
            ':data_name' => $farmData['data_name']
        ]);

        error_log('SQL 執行結果: ' . ($result ? '成功' : '失敗'));

        $pdo->commit();
        return ['success' => $result, 'message' => '農場保存成功。'];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('saveFarm 中的錯誤: ' . $e->getMessage());
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