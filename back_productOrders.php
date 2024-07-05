<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 添加錯誤日誌
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once("./connect_cid101g4.php");

    $returnData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];

    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'fetch_orders':
            $sql = "SELECT p.po_no, p.m_no, p.po_name, m.m_phone, p.po_address, p.po_time, 
                           p.po_status, p.po_total, p.c_no, p.po_discount, p.po_finalprice, 
                           DATE_ADD(p.po_time, INTERVAL 7 DAY) AS po_deliverdate 
                    FROM p_orders p 
                    LEFT JOIN member m ON p.m_no = m.m_no 
                    ORDER BY p.po_time DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $returnData['data'] = $orders;
            $returnData['msg'] = '訂單列表獲取成功';
            break;

        case 'view_order':
            $sql = "SELECT p.*, m.m_phone, od.p_no, od.p_fee, od.o_quatity, pr.p_name,
                           DATE_ADD(p.po_time, INTERVAL 7 DAY) AS po_deliverdate
                    FROM p_orders p 
                    LEFT JOIN member m ON p.m_no = m.m_no 
                    LEFT JOIN `order-details` od ON p.po_no = od.po_no 
                    LEFT JOIN product pr ON od.p_no = pr.p_no
                    WHERE p.po_no = :po_no";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':po_no', $data['po_no']);
            $stmt->execute();
            $order = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            if ($order) {
                $processedOrder = $order[0];  // 取得基本訂單信息
                $processedOrder['items'] = [];  // 新增一個項目來存儲訂單商品
        
                foreach ($order as $item) {
                    $processedOrder['items'][] = [
                        'p_no' => $item['p_no'],
                        'p_name' => $item['p_name'],
                        'p_fee' => $item['p_fee'],
                        'o_quatity' => $item['o_quatity']
                    ];
                }
        
                $returnData['data'] = $processedOrder;
                $returnData['msg'] = '訂單詳情獲取成功';
            } else {
                $returnData['code'] = 404;
                $returnData['msg'] = '訂單不存在';
            }
            break;

        case 'update_order_status':
            $sql = "UPDATE p_orders SET po_status = :status WHERE po_no = :po_no";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':status', $data['po_status']);
            $stmt->bindValue(':po_no', $data['po_no']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $returnData['msg'] = '訂單狀態更新成功';
            } else {
                $returnData['code'] = 404;
                $returnData['msg'] = '訂單不存在或狀態未變更';
            }
            break;

        case 'cancel_order':
            $sql = "UPDATE p_orders SET po_status = 4 WHERE po_no = :po_no AND (po_status = 0 OR po_status = 3)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':po_no', $data['po_no']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $returnData['msg'] = '訂單已成功註銷';
            } else {
                $returnData['code'] = 400;
                $returnData['msg'] = '訂單註銷失敗，可能訂單不存在或已不是待配送狀態';
            }
            break;

        default:
            $returnData['code'] = 400;
            $returnData['msg'] = '無效的操作類型';
            break;
    }

} catch (Exception $e) {
    $returnData['code'] = 500;
    $returnData['msg'] = $e->getMessage();
    error_log("Error in back_productOrders.php: " . $e->getMessage());
}

echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
?>