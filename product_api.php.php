<?php
// product_api.php

// 設置 HTTP 頭
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 啟用錯誤報告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        handleGet($pdo, $returnData);
        break;
    case 'POST':
        handlePost($pdo, $returnData);
        break;
    case 'DELETE':
        handleDelete($pdo, $returnData);
        break;
    case 'OPTIONS':
        // 處理預檢請求
        header("HTTP/1.1 200 OK");
        exit();
    default:
        $returnData['code'] = 405;
        $returnData['msg'] = '無效的請求方法';
}

// 輸出 JSON 結果
echo json_encode($returnData);

// 處理 GET 請求
function handleGet($pdo, &$returnData) {
    try {
        // 執行查詢 SQL，獲取商品列表
        $sql = "SELECT p.*, f.f_name, c.pc_name, GROUP_CONCAT(pi.pi_img) as pi_img
                FROM product p
                JOIN farm f ON p.f_no = f.f_no
                JOIN product_category c ON p.pc_no = c.pc_no
                LEFT JOIN product_img pi ON p.p_no = pi.p_no
                GROUP BY p.p_no
                ORDER BY p.p_no DESC";
        $product = $pdo->prepare($sql);
        $product->execute();

        // 查詢所有商品筆數
        $sql2 = "SELECT COUNT(*) AS total FROM product";
        $stmt = $pdo->prepare($sql2);
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        // 抓取數據庫商品數據
        $productData = $product->fetchAll(PDO::FETCH_ASSOC);
        foreach ($productData as &$item) {
            $item['pi_img'] = $item['pi_img'] ? explode(',', $item['pi_img']) : [];
        }

        $returnData['data']['list'] = $productData;
        $returnData['data']['totalCount'] = $totalCount;
    } catch (Exception $e) {
        $returnData['code'] = 10003;
        $returnData['msg'] = $e->getMessage();
    }
}

// 處理 POST 請求
function handlePost($pdo, &$returnData) {
    $isUpdate = isset($_POST['isUpdate']) && $_POST['isUpdate'] === '1';
    
    if ($isUpdate) {
        $result = updateProduct($pdo, $_POST);
    } else {
        $result = saveProduct($pdo, $_POST);
    }
    
    $returnData['msg'] = $result['message'];
    if (!$result['success']) {
        $returnData['code'] = 400;
    }
}

// 處理 DELETE 請求
function handleDelete($pdo, &$returnData) {
    $productId = $_GET['id'] ?? null;
    if ($productId) {
        $result = deleteProduct($pdo, $productId);
        $returnData['msg'] = $result['message'];
        if (!$result['success']) {
            $returnData['code'] = 400;
        }
    } else {
        $returnData['code'] = 400;
        $returnData['msg'] = '缺少商品 ID';
    }
}

// 保存商品的函數
function saveProduct($pdo, $productData) {
    try {
        $pdo->beginTransaction();

        // 插入商品基本信息
        $sql = "INSERT INTO product (p_name, p_info, p_fee, p_unit, f_no, pc_no, p_popular) 
                VALUES (:p_name, :p_info, :p_fee, :p_unit, :f_no, :pc_no, :p_popular)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':p_name' => $productData['p_name'] ?? '',
            ':p_info' => $productData['p_info'] ?? '',
            ':p_fee' => floatval($productData['p_fee'] ?? 0),
            ':p_unit' => $productData['p_unit'] ?? '',
            ':f_no' => $productData['f_no'] ?? '',
            ':pc_no' => $productData['pc_no'] ?? '',
            ':p_popular' => $productData['p_popular'] ?? '1'
        ]);

        $p_no = $pdo->lastInsertId();

        // 插入商品圖片
        if (isset($productData['pi_img']) && is_array($productData['pi_img'])) {
            $sql = "INSERT INTO product_img (p_no, pi_img) VALUES (:p_no, :pi_img)";
            $stmt = $pdo->prepare($sql);
            foreach ($productData['pi_img'] as $img) {
                if (!empty($img)) {
                    $stmt->execute([
                        ':p_no' => $p_no,
                        ':pi_img' => $img
                    ]);
                }
            }
        }

        $pdo->commit();
        return ['success' => true, 'message' => '商品保存成功。'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => '保存商品時發生錯誤: ' . $e->getMessage()];
    }
}

// 更新商品的函數
function updateProduct($pdo, $productData) {
    try {
        $pdo->beginTransaction();

        // 更新商品基本信息
        $sql = "UPDATE product SET 
                p_name = :p_name, 
                p_info = :p_info, 
                p_fee = :p_fee, 
                p_unit = :p_unit, 
                f_no = :f_no, 
                pc_no = :pc_no,
                p_popular = :p_popular
                WHERE p_no = :p_no";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':p_no' => $productData['p_no'],
            ':p_name' => $productData['p_name'] ?? '',
            ':p_info' => $productData['p_info'] ?? '',
            ':p_fee' => floatval($productData['p_fee'] ?? 0),
            ':p_unit' => $productData['p_unit'] ?? '',
            ':f_no' => $productData['f_no'] ?? '',
            ':pc_no' => $productData['pc_no'] ?? '',
            ':p_popular' => $productData['p_popular'] ?? '1'
        ]);

        // 處理圖片
        if (isset($productData['pi_img']) && is_array($productData['pi_img'])) {
            // 刪除舊的圖片
            $deleteSql = "DELETE FROM product_img WHERE p_no = :p_no";
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->execute([':p_no' => $productData['p_no']]);

            // 插入新的圖片
            $insertSql = "INSERT INTO product_img (p_no, pi_img) VALUES (:p_no, :pi_img)";
            $insertStmt = $pdo->prepare($insertSql);
            
            foreach ($productData['pi_img'] as $img) {
                if (!empty($img)) {
                    $insertStmt->execute([
                        ':p_no' => $productData['p_no'],
                        ':pi_img' => $img
                    ]);
                }
            }
        }

        $pdo->commit();
        return ['success' => true, 'message' => '商品更新成功。'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => '更新商品時發生錯誤: ' . $e->getMessage()];
    }
}

// 刪除商品的函數
function deleteProduct($pdo, $productId) {
    try {
        $pdo->beginTransaction();

        // 首先刪除商品圖片
        $sql = "DELETE FROM product_img WHERE p_no = :p_no";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':p_no' => $productId]);

        // 然後刪除商品
        $sql = "DELETE FROM product WHERE p_no = :p_no";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':p_no' => $productId]);

        $pdo->commit();
        return ['success' => true, 'message' => '商品刪除成功。'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => '刪除商品時發生錯誤: ' . $e->getMessage()];
    }
}