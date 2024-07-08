<?php
try {
    require_once("./connect_cid101g4.php");
    $returnData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];
    // 獲取前端傳來的資料
    $data = json_decode(file_get_contents('php://input'), true);
    $userNo = $data['userNo'];
    $searchTerm = isset($data['searchTerm']) ? $data['searchTerm'] : '';
    $categoryFilter = isset($data['categoryFilter']) ? $data['categoryFilter'] : '';
    $page = isset($data['page']) ? intval($data['page']) : 1;
    $itemsPerPage = isset($data['itemsPerPage']) ? intval($data['itemsPerPage']) : 12;
    
    // 構建基本的 SQL 查詢
    $sql = "SELECT p.*, f.f_name, c.pc_name FROM product p
            JOIN farm f ON p.f_no = f.f_no
            JOIN product_category c ON p.pc_no = c.pc_no
            WHERE p.p_status = 1";
    
    // 添加搜尋條件
    if (!empty($searchTerm)) {
        $sql .= " AND (p.p_name LIKE :searchTerm OR p.p_info LIKE :searchTerm OR f.f_name LIKE :searchTerm OR c.pc_name LIKE :searchTerm)";
    }
    
    // 添加分類過濾
    if (!empty($categoryFilter)) {
        $sql .= " AND c.pc_name = :categoryFilter";
    }
    
    // 計算總項目數
    $countSql = $sql;
    $countStmt = $pdo->prepare($countSql);
    if (!empty($searchTerm)) {
        $countStmt->bindValue(':searchTerm', "%$searchTerm%", PDO::PARAM_STR);
    }
    if (!empty($categoryFilter)) {
        $countStmt->bindValue(':categoryFilter', $categoryFilter, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalItems = $countStmt->rowCount();
    $totalPages = ceil($totalItems / $itemsPerPage);

    // 添加分頁
    $sql .= " ORDER BY p.p_no DESC LIMIT :offset, :limit";

    $stmt = $pdo->prepare($sql);
    
    // 綁定參數
    if (!empty($searchTerm)) {
        $stmt->bindValue(':searchTerm', "%$searchTerm%", PDO::PARAM_STR);
    }
    if (!empty($categoryFilter)) {
        $stmt->bindValue(':categoryFilter', $categoryFilter, PDO::PARAM_STR);
    }
    $offset = ($page - 1) * $itemsPerPage;
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    
    $stmt->execute();
    $productData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 處理每個產品的圖片和收藏/購物車狀態
    foreach($productData as $key => $prod) {
        $productData[$key]['isImage1'] = false;
        $productData[$key]['isaddCart'] = false;
        $prodNo = $prod['p_no'];
        // 獲取產品圖片
        $sql3 = "SELECT pi_img FROM product_img WHERE p_no = :prodNo";
        $product_img = $pdo->prepare($sql3);
        $product_img->bindParam(':prodNo', $prodNo, PDO::PARAM_INT);
        $product_img->execute();
        $proDetails = $product_img->fetchAll(PDO::FETCH_ASSOC);
        $productData[$key]['p_img'] = array_column($proDetails, 'pi_img');
        // 獲取收藏和購物車狀態
        $sql4 = "SELECT * FROM member_favorite WHERE m_no = :userNo AND p_no = :prodNo";
        $favoriteCart = $pdo->prepare($sql4);
        $favoriteCart->bindParam(':userNo', $userNo, PDO::PARAM_INT);
        $favoriteCart->bindParam(':prodNo', $prodNo, PDO::PARAM_INT);
        $favoriteCart->execute();
        $favoriteCarts = $favoriteCart->fetchAll(PDO::FETCH_ASSOC);
        foreach($favoriteCarts as $fav) {
            if($fav['fav'] == 1) {
                $productData[$key]['isImage1'] = true;
            }
            if($fav['cart'] == 1) {
                $productData[$key]['isaddCart'] = true;
            }
        }
    }

    $returnData['data']['list'] = $productData;
    $returnData['data']['totalPages'] = $totalPages;
    $returnData['data']['totalCount'] = $totalItems;

} catch (Exception $e) {
    $returnData['code'] = 10003;
    $returnData['msg'] = $e->getMessage();
}

echo json_encode($returnData);
?>