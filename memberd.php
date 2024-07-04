<?php
// memberd.php

// 設置 HTTP 頭
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 引入數據庫連接文件
require_once("./connect_cid101g4.php");

// 初始化返回數據結構
$returnData = [
    'code' => 200,
    'msg' => '',
    'data' => []
];

// 獲取請求方法
$method = $_SERVER['REQUEST_METHOD'];

// 處理不同的請求方法
switch ($method) {
    case 'GET':
        // 獲取會員列表
        $returnData['data']['list'] = getAllMembers($pdo);
        break;
    case 'POST':
        // 更新會員狀態
        $postData = json_decode(file_get_contents('php://input'), true);
        if ($postData['action'] === 'updateStatus') {
            $result = updateMemberStatus($pdo, $postData['m_no'], $postData['m_status']);
            $returnData['msg'] = $result['message'];
            if (!$result['success']) {
                $returnData['code'] = 400;
            }
        } else {
            $returnData['code'] = 400;
            $returnData['msg'] = '無效的操作';
        }
        break;
    default:
        $returnData['code'] = 405;
        $returnData['msg'] = '無效的請求方法';
}

// 輸出 JSON 結果
echo json_encode($returnData);

// 獲取所有會員的函數
function getAllMembers($pdo) {
    try {
        $sql = "SELECT m_no, m_name, m_birth, m_account, m_phone, m_add, m_status FROM member ORDER BY m_no";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // 記錄錯誤並返回空數組
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

// 更新會員狀態的函數
function updateMemberStatus($pdo, $m_no, $m_status) {
    try {
        $sql = "UPDATE member SET m_status = :m_status WHERE m_no = :m_no";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':m_status' => $m_status,
            ':m_no' => $m_no
        ]);

        if ($result) {
            return ['success' => true, 'message' => '會員狀態更新成功。'];
        } else {
            return ['success' => false, 'message' => '會員狀態更新失敗。'];
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => '更新會員狀態時發生錯誤: ' . $e->getMessage()];
    }
}
?>