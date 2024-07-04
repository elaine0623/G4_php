<?php
// product_api.php

// 設置 HTTP 頭
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
        try {
            // 執行查詢 SQL，不使用 LIMIT
            $sql = "SELECT p.*, f.f_name, c.pc_name, pi.pi_img
                    FROM product p
                    JOIN farm f ON p.f_no = f.f_no
                    JOIN product_category c ON p.pc_no = c.pc_no
                    LEFT JOIN product_img pi ON p.p_no = pi.p_no
                    ORDER BY p.p_no DESC";
            $product = $pdo->prepare($sql);
            $product->execute();

            // 查詢所有商品筆數
            $sql2 = "SELECT COUNT(*) AS total FROM product";
            $stmt = $pdo->prepare($sql2);
            $stmt->execute();
            $totalCount = $stmt->fetchColumn();

            // 抓取資料庫商品資料
            $productData = $product->fetchAll(PDO::FETCH_ASSOC);
            $formattedData = [];
            foreach ($productData as $product) {
                $p_no = $product['p_no'];
                if (!isset($formattedData[$p_no])) {
                    $formattedData[$p_no] = $product;
                    $formattedData[$p_no]['p_img'] = [];
                }
                if ($product['pi_img']) {
                    $formattedData[$p_no]['p_img'][] = $product['pi_img'];
                }
            }

            $returnData['data']['list'] = array_values($formattedData);
            $returnData['data']['totalCount'] = $totalCount;
        } catch (Exception $e) {
            $returnData['code'] = 10003;
            $returnData['msg'] = $e->getMessage();
        }
        break;

    case 'POST':
        $postData = json_decode(file_get_contents('php://input'), true);
        $result = saveProduct($pdo, $postData);
        $returnData['msg'] = $result['message'];
        if (!$result['success']) {
            $returnData['code'] = 400;
        }
        break;

    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $queryParams);
        $productId = $queryParams['id'] ?? null;
        if ($productId) {
            $postData = json_decode(file_get_contents('php://input'), true);
            $postData['p_no'] = $productId;
            
            // 檢查是否只更新 p_popular
            if (count($postData) === 2 && isset($postData['p_popular'])) {
                $result = updateProductPopular($pdo, $productId, $postData['p_popular']);
            } else {
                $result = updateProduct($pdo, $postData);
            }
            
            $returnData['msg'] = $result['message'];
            if (!$result['success']) {
                $returnData['code'] = 400;
            }
        } else {
            $returnData['code'] = 400;
            $returnData['msg'] = '缺少商品 ID';
        }
        break;

    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'], $queryParams);
        $productId = $queryParams['id'] ?? null;
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
        break;

    default:
        $returnData['code'] = 405;
        $returnData['msg'] = '無效的請求方法';
}

// 輸出 JSON 結果
echo json_encode($returnData);

// 保存商品的函數
function saveProduct($pdo, $productData)
{
    try {
        // 開始事務
        $pdo->beginTransaction();

        // 插入商品主表
        $sql = "INSERT INTO product (p_name, p_info, p_fee, p_unit, f_no, pc_no, p_popular) 
                VALUES (:p_name, :p_info, :p_fee, :p_unit, :f_no, :pc_no, :p_popular)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':p_name' => $productData['p_name'],
            ':p_info' => $productData['p_info'],
            ':p_fee' => $productData['p_fee'],
            ':p_unit' => $productData['p_unit'],
            ':f_no' => $productData['f_no'],
            ':pc_no' => $productData['pc_no'],
            ':p_popular' => $productData['p_popular'] ?? '1'  // 默認為一般商品
        ]);

        // 獲取插入的商品編號
        $p_no = $pdo->lastInsertId();

        // 插入商品圖片
        $sql = "INSERT INTO product_img (p_no, pi_img) VALUES (:p_no, :pi_img)";
        $stmt = $pdo->prepare($sql);
        foreach ($productData['p_img'] as $img) {
            if (!empty($img)) {
                $stmt->execute([
                    ':p_no' => $p_no,
                    ':pi_img' => $img
                ]);
            }
        }

        // 提交事務
        $pdo->commit();

        return ['success' => true, 'message' => '商品保存成功。'];
    } catch (Exception $e) {
        // 回滾事務
        $pdo->rollBack();
        return ['success' => false, 'message' => '保存商品時發生錯誤: ' . $e->getMessage()];
    }
}

// 更新商品的函數
function updateProduct($pdo, $productData)
{
    try {
        // 開始事務
        $pdo->beginTransaction();

        // 更新商品主表
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
            ':p_name' => $productData['p_name'],
            ':p_info' => $productData['p_info'],
            ':p_fee' => $productData['p_fee'],
            ':p_unit' => $productData['p_unit'],
            ':f_no' => $productData['f_no'],
            ':pc_no' => $productData['pc_no'],
            ':p_popular' => $productData['p_popular'] ?? '1'  // 如果沒有提供，默認為一般商品
        ]);

        // 刪除原有商品圖片
        $sql = "DELETE FROM product_img WHERE p_no = :p_no";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':p_no' => $productData['p_no']]);

        // 插入新的商品圖片
        $sql = "INSERT INTO product_img (p_no, pi_img) VALUES (:p_no, :pi_img)";
        $stmt = $pdo->prepare($sql);
        foreach ($productData['p_img'] as $img) {
            if (!empty($img)) {
                $stmt->execute([
                    ':p_no' => $productData['p_no'],
                    ':pi_img' => $img
                ]);
            }
        }

        // 提交事務
        $pdo->commit();

        return ['success' => true, 'message' => '商品更新成功。'];
    } catch (Exception $e) {
        // 回滾事務
        $pdo->rollBack();
        return ['success' => false, 'message' => '更新商品時發生錯誤: ' . $e->getMessage()];
    }
}

// 刪除商品的函數
function deleteProduct($pdo, $productId)
{
    try {
        // 開始事務
        $pdo->beginTransaction();

        // 刪除商品圖片
        $sql = "DELETE FROM product_img WHERE p_no = :p_no";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':p_no' => $productId]);

        // 刪除商品主表
        $sql = "DELETE FROM product WHERE p_no = :p_no";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':p_no' => $productId]);

        // 提交事務
        $pdo->commit();

        return ['success' => true, 'message' => '商品刪除成功。'];
    } catch (Exception $e) {
        // 回滾事務
        $pdo->rollBack();
        return ['success' => false, 'message' => '刪除商品時發生錯誤: ' . $e->getMessage()];
    }
}

// 更新商品熱門度的函數
function updateProductPopular($pdo, $productId, $popular)
{
    try {
        $sql = "UPDATE product SET p_popular = :p_popular WHERE p_no = :p_no";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':p_popular' => $popular,
            ':p_no' => $productId
        ]);

        if ($result) {
            return ['success' => true, 'message' => '商品熱門度更新成功。'];
        } else {
            return ['success' => false, 'message' => '商品熱門度更新失敗。'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => '更新商品熱門度時發生錯誤: ' . $e->getMessage()];
    }
}
?>