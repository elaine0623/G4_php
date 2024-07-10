<?php
try {
    require_once('./connect_cid101g4.php');
    $recivedData = [
        'code' => 200,
        'msg' => '',
        'data' =>[]
    ];
    //抓前端傳回資料json
    $responseEventData = json_decode(file_get_contents('php://input'),true);
   //取得舊檔名
  $oldFileName = $responseEventData['oldFileName'];
  if(file_exists($oldFileName)) {
    if(unlink($oldFileName)) {
        echo "File deleted successfully.";
  }else {
    echo "File deleted successfully.";
  }
}else {
    throw new Exception("File does not exist.");
}
} catch (Exception $e) {
    $recivedData['code'] = 10003;
    $recivedData['msg'] = $e->getMessage();
};
echo json_encode($recivedData, JSON_NUMERIC_CHECK);










?>