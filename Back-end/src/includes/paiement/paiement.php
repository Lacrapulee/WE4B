<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../articles_functions.php';
require_once __DIR__ . '/../payment_page_functions.php';

$articleId = $_GET['id'] ?? ($_POST['article_id'] ?? null);
$viewData = buildPaymentPageViewData($pdo, $articleId, $_SERVER['REQUEST_METHOD'], $_POST);
http_response_code($viewData['statusCode']);

$product = $viewData['product'];
?>