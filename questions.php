<?php

try {
    // 引入資料庫連接文件
    require_once("./connect_cid101g4.php");

    // 定義 SQL 查詢，選取所有問題及其對應選項，並依問題編號和選項順序排列
    $sql = "SELECT * FROM question_game q 
            JOIN options_game o ON q.q_no = o.q_no
            ORDER BY q.q_no, o.q_answer";
    // 預備和執行 SQL 語句
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    // 將查詢結果以關聯陣列形式取出
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 初始化格式化問題數據的陣列
    $formattedQuestions = [];

    // 計算查詢結果的筆數
    $recordCount = count($results);
    // 循環遍歷查詢結果，步長為 4（每個問題有 4 個選項）
    for ($i = 0; $i < $recordCount; $i += 4) {
        // 初始化當前問題數據
        $currentQuestion = [
            'no' => $results[$i]['q_no'], // 問題編號
            'question' => $results[$i]['q_question'], // 問題內容
            'options' => [], // 初始化選項陣列
            'answer' => $results[$i]['q_explainimg_options'], // 正確選項
            'answer_image' => $results[$i]['q_explainimg_img'], // 解釋圖片
            'explanation' => $results[$i]['q_explainimg'] // 解釋文本
        ];
        
        // 循環添加每個問題的 4 個選項
        for ($j = $i; $j < $i + 4; $j++) {
            $currentQuestion['options'][] = [
                'key' => $results[$j]['q_options'], // 選項鍵值
                'text' => $results[$j]['q_answer'], // 選項文本
                'img' => $results[$j]['q_img'] // 選項圖片
            ];
        }

        // 按 key 排序 options 陣列
        usort($currentQuestion['options'], function($a, $b) {
            return strcmp($a['key'], $b['key']);
        });

        // 將當前完整的問題數據加入到格式化問題數組中
        $formattedQuestions[] = $currentQuestion;
    }

    // 返回 JSON 格式的數據，並確保不轉義 Unicode 字符
    echo json_encode($formattedQuestions, JSON_UNESCAPED_UNICODE);
} catch(PDOException $e) {
    // 捕捉例外並返回錯誤信息
    echo json_encode(['error' => $e->getMessage()]);
}
?>
