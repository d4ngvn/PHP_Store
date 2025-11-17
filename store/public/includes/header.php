<?php
// Luôn gọi file này ở đầu để bắt đầu session
require_once __DIR__ . '/../../core/functions.php'; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Trang Bán Hàng'; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
          
    <link rel="stylesheet" href="assets/css/custom.css"> 
</head>
<body class="bg-light">

<?php include 'nav.php'; // Chèn thanh điều hướng ?>

<div class="container mt-4"> <main>