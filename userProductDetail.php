<?php
try {
    // 包含資料庫連接設定
    require_once("./connect_cid101g4.php");

    // 設定返回資料的初始值
    $returnData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];

    // 取得並解析輸入的JSON資料
    $data = json_decode(file_get_contents('php://input'), true);

    // 檢查輸入資料是否存在
    if (isset($data['m_no']) && isset($data['po_no'])) {
        // SQL查詢
        $sql = "SELECT po.*, od.*, p.*, pi.*, f.*, m.m_account
                FROM p_orders po
                JOIN `order-details` od ON po.po_no = od.po_no
                JOIN product p ON p.p_no = od.p_no
                JOIN farm f ON f.f_no = p.f_no
                JOIN member m ON m.m_no = po.m_no
                LEFT JOIN (
                    SELECT * FROM product_img
                    GROUP BY p_no
                ) pi ON pi.p_no = p.p_no
                WHERE po.m_no = :m_no AND po.po_no = :po_no";

        // 準備 SQL 語句
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':m_no', $data['m_no']);
        $stmt->bindValue(':po_no', $data['po_no']);
        $stmt->execute();

        // 獲取查詢結果
        $pOrdersRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 將查詢結果賦值給返回資料
        $returnData['data']['list'] = $pOrdersRows;
    } else {
        throw new Exception('m_no or po_no not provided');
    }
} catch (PDOException $e) {
    // 捕獲資料庫操作異常並設置錯誤代碼和錯誤信息
    $returnData['code'] = 10001;
    $returnData['msg'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    // 捕獲其他異常並設置錯誤代碼和錯誤信息
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

// 將返回資料編碼為JSON並輸出
echo json_encode($returnData);
?>
