<?php
try {
    require_once("./connect_cid101g4.php");
    $returnData = [
        'code' => 200,
        'msg' => '',
        'data' => []
    ];
    
    // 抓前端傳來的資料
    $data = json_decode(file_get_contents('php://input'), true);
    
    // 或去當前時間
    $currentDateTime = date('Y-m-d H:i:s');
    
    // 插入商品訂單資料
    $sql = "INSERT INTO `p_orders` (`m_no`, `po_name`, `m_phone`, `po_address`, `po_status`, `po_total`, `c_no`, `po_time`, `po_discount`, `po_finalprice`) 
            VALUES (:m_no, :po_name, :m_phone, :po_address, :po_status, :po_total, :c_no, :po_time, :po_discount, :po_finalprice)";
    $pOrder = $pdo->prepare($sql);
    
    $pOrder->bindValue(':m_no', $data['m_no']);
    $pOrder->bindValue(':po_name', $data['po_name']);
    $pOrder->bindValue(':m_phone', $data['m_phone']);
    $pOrder->bindValue(':po_address', $data['po_address']);
    $pOrder->bindValue(':po_status', $data['po_status']);
    $pOrder->bindValue(':po_total', $data['po_total']);
    $pOrder->bindValue(':c_no', $data['c_no']);
    $pOrder->bindValue(':po_time', $currentDateTime);
    $pOrder->bindValue(':po_discount', $data['po_discount']);
    $pOrder->bindValue(':po_finalprice', $data['po_finalprice']);
    
    $pOrder->execute();
    
    // 獲取剛插入的記錄
    $po_no = $pdo->lastInsertId();
    $sq12 = "SELECT * FROM `p_orders` WHERE `po_no` = :po_no";
    $member2 = $pdo->prepare($sq12);
    $member2->bindValue(':po_no', $po_no);
    $member2->execute();
    $returnData['data'] = $member2->fetch(PDO::FETCH_ASSOC);

    $sq3 = "INSERT INTO `order-details` (`p_no`, `p_fee`, `o_quatity`, `po_no`) VALUES (:p_no, :p_fee, :o_quatity, :po_no)";
    $pOrderDetail = $pdo->prepare($sq3);

    foreach ($data['cartItems'] as $item) {
        $pOrderDetail->bindValue(':p_no', $item['p_no']);
        $pOrderDetail->bindValue(':p_fee', $item['p_fee']);
        $pOrderDetail->bindValue(':o_quatity', $item['count']);
        $pOrderDetail->bindValue(':po_no', $po_no);
        $pOrderDetail->execute();
        
        $sql5="UPDATE  member_favorite SET `cart`= 0 WHERE p_no=:p_no AND m_no =:m_no";
        $product =$pdo->prepare($sql5);
        $product->bindValue(':p_no', $item['p_no']);
        $product->bindValue(':m_no', $data['m_no']);
        $product->execute();
    }
      // 獲取剛插入的記錄
      $sq14 = "SELECT * FROM `order-details` WHERE `po_no` = :po_no";
      $member3 = $pdo->prepare($sq14);
      $member3->bindValue(':po_no', $po_no);
      $member3->execute();
      $returnData['data'] = $member3->fetchAll(PDO::FETCH_ASSOC);

  
  } catch (Exception $e) {
      $returnData['code'] = 10003;
      $returnData['msg'] = $e->getMessage();
  }

echo json_encode($returnData, JSON_NUMERIC_CHECK);
?>
