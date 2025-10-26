<?php
// File: public/index.php

// Pastikan session dimulai untuk penanganan error/notifikasi di masa depan (opsional, tapi baik)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 'index';
}

// Pastikan path ini benar berdasarkan struktur folder Anda
include ('../controllers/TodoController.php');

$todoController = new TodoController();
switch ($page) {
    case 'index':
        $todoController->index();
        break;
    case 'create':
        $todoController->create();
        break;
    case 'update':
        $todoController->update();
        break;
    case 'delete':
        $todoController->delete();
        break;
    // Endpoint API untuk Drag and Drop Sorting
    case 'updateSorting':
        $todoController->updateSorting();
        break;
    default:
        // Default ke halaman index jika 'page' tidak dikenal
        $todoController->index();
        break;
}