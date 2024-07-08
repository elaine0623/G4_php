<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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
                           p.po_deliverdate
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
            $sql = "SELECT p.*, m.m_phone, od.p_no, od.p_fee, od.o_quatity, pr.p_name
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
            $pdo->beginTransaction();
            
            try {
                // 首先獲取當前訂單狀態
                $sqlGetCurrentStatus = "SELECT po_status FROM p_orders WHERE po_no = :po_no";
                $stmtGetCurrentStatus = $pdo->prepare($sqlGetCurrentStatus);
                $stmtGetCurrentStatus->bindValue(':po_no', $data['po_no']);
                $stmtGetCurrentStatus->execute();
                $currentStatus = $stmtGetCurrentStatus->fetchColumn();

                // 更新訂單狀態
                $sqlUpdateStatus = "UPDATE p_orders SET po_status = :status WHERE po_no = :po_no";
                $stmtUpdateStatus = $pdo->prepare($sqlUpdateStatus);
                $stmtUpdateStatus->bindValue(':status', $data['po_status']);
                $stmtUpdateStatus->bindValue(':po_no', $data['po_no']);
                $stmtUpdateStatus->execute();

                // 如果新狀態為 1 且之前不是 1，則更新 po_deliverdate 為當前日期
                if ($data['po_status'] == 1 && $currentStatus != 1) {
                    $sqlUpdateDeliverDate = "UPDATE p_orders SET po_deliverdate = NOW() WHERE po_no = :po_no";
                    $stmtUpdateDeliverDate = $pdo->prepare($sqlUpdateDeliverDate);
                    $stmtUpdateDeliverDate->bindValue(':po_no', $data['po_no']);
                    $stmtUpdateDeliverDate->execute();
                }

                $pdo->commit();
                $returnData['msg'] = '訂單狀態更新成功';
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
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