<?php
// get_question.php

// 設置 HTTP 頭
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

// 獲取 POST 數據
$postData = json_decode(file_get_contents('php://input'), true);
$action = $postData['action'] ?? '';

// 處理不同的操作
switch ($action) {
    case 'getQuestions':
        $returnData['data']['list'] = getAllQuestions($pdo);
        break;
    case 'saveQuestion':
        $result = saveQuestion($pdo, $postData['question']);
        $returnData['msg'] = $result['message'];
        if (!$result['success']) {
            $returnData['code'] = 400;
        }
        break;
    case 'deleteQuestion':
        $result = deleteQuestion($pdo, $postData['questionNo']);
        $returnData['msg'] = $result['message'];
        if (!$result['success']) {
            $returnData['code'] = 400;
        }
        break;
    default:
        $returnData['code'] = 400;
        $returnData['msg'] = '無效的操作';
}

// 輸出 JSON 結果
echo json_encode($returnData);

// 獲取所有問題的函數
function getAllQuestions($pdo) {
    $sql = "SELECT q.*, o.q_options, o.q_answer, o.q_img 
            FROM question_game q 
            JOIN options_game o ON q.q_no = o.q_no
            ORDER BY q.q_no, o.q_options";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedQuestions = [];
    $currentQuestion = null;

    foreach ($results as $row) {
        if (!$currentQuestion || $currentQuestion['no'] !== $row['q_no']) {
            if ($currentQuestion) {
                $formattedQuestions[] = $currentQuestion;
            }
            $currentQuestion = [
                'no' => $row['q_no'],
                'question' => $row['q_question'],
                'options' => [],
                'answer' => $row['q_explainimg_options'],
                'correctAnswer' => '', // 將在選項循環中設置
                'answer_image' => $row['q_explainimg_img'],
                'explanation' => $row['q_explainimg']
            ];
        }

        $currentQuestion['options'][] = [
            'key' => $row['q_options'],
            'text' => $row['q_answer'],
            'img' => $row['q_img']
        ];

        if ($row['q_options'] === $currentQuestion['answer']) {
            $currentQuestion['correctAnswer'] = $row['q_answer'];
        }
    }

    if ($currentQuestion) {
        $formattedQuestions[] = $currentQuestion;
    }

    return $formattedQuestions;
}

// 保存問題的函數
function saveQuestion($pdo, $questionData) {
    try {
        $pdo->beginTransaction();

        // 檢查問題是否已存在
        $checkSql = "SELECT COUNT(*) FROM question_game WHERE q_no = :q_no";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([':q_no' => $questionData['no']]);
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            // 更新現有問題
            $questionSql = "UPDATE question_game SET 
                            q_question = :question, 
                            q_explainimg_options = :answer, 
                            q_explainimg_img = :answer_image, 
                            q_explainimg = :explanation
                            WHERE q_no = :q_no";
        } else {
            // 插入新問題
            $questionSql = "INSERT INTO question_game 
                            (q_no, q_question, q_explainimg_options, q_explainimg_img, q_explainimg) 
                            VALUES (:q_no, :question, :answer, :answer_image, :explanation)";
        }

        $questionStmt = $pdo->prepare($questionSql);
        $questionStmt->execute([
            ':q_no' => $questionData['no'],
            ':question' => $questionData['question'],
            ':answer' => $questionData['answer'],
            ':answer_image' => $questionData['answer_image'],
            ':explanation' => $questionData['explanation']
        ]);

        // 刪除舊的選項（如果存在）
        $deleteOptionsSql = "DELETE FROM options_game WHERE q_no = :q_no";
        $deleteOptionsStmt = $pdo->prepare($deleteOptionsSql);
        $deleteOptionsStmt->execute([':q_no' => $questionData['no']]);

        // 插入新的選項
        $optionsSql = "INSERT INTO options_game (q_no, q_options, q_answer, q_img) 
                       VALUES (:q_no, :q_options, :q_answer, :q_img)";
        $optionsStmt = $pdo->prepare($optionsSql);
        foreach ($questionData['options'] as $option) {
            $optionsStmt->execute([
                ':q_no' => $questionData['no'],
                ':q_options' => $option['key'],
                ':q_answer' => $option['text'],
                ':q_img' => $option['img']
            ]);
        }

        $pdo->commit();
        return ['success' => true, 'message' => '問題保存成功。'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => '保存問題時發生錯誤: ' . $e->getMessage()];
    }
}

// 刪除問題的函數
function deleteQuestion($pdo, $questionNo) {
    try {
        $pdo->beginTransaction();

        // 刪除問題
        $deleteQuestionSql = "DELETE FROM question_game WHERE q_no = :q_no";
        $deleteQuestionStmt = $pdo->prepare($deleteQuestionSql);
        $deleteQuestionStmt->execute([':q_no' => $questionNo]);

        // 刪除相關的選項
        $deleteOptionsSql = "DELETE FROM options_game WHERE q_no = :q_no";
        $deleteOptionsStmt = $pdo->prepare($deleteOptionsSql);
        $deleteOptionsStmt->execute([':q_no' => $questionNo]);

        $pdo->commit();
        return ['success' => true, 'message' => '問題刪除成功。'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => '刪除問題時發生錯誤: ' . $e->getMessage()];
    }
}
?>