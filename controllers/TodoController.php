<?php
// File: controllers/TodoController.php

require_once (__DIR__ . '/../models/TodoModel.php');

class TodoController
{
    public function index()
    {
        // Ambil filter dan search dari GET
        $filter = $_GET['filter'] ?? 'all'; 
        $search = $_GET['search'] ?? '';
        
        $todoModel = new TodoModel();
        // Memanggil fungsi model yang mendukung filter, search, dan sorting
        $todos = $todoModel->getFilteredAndSearchedTodos($filter, $search); 
        
        // Kirim $todos, $filter, dan $search ke view
        include (__DIR__ . '/../views/TodoView.php');
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            // Ambil status awal dari form, default 0 (Belum Selesai)
            $is_finished = (int) ($_POST['is_finished'] ?? 0); 
            
            $todoModel = new TodoModel();

            // Validasi: Judul tidak boleh kosong
            if (empty(trim($title))) {
                 header('Location: index.php?error=empty_title');
                 exit;
            }
            
            // Validasi: Judul Unik
            if ($todoModel->checkDuplicateTitle($title)) {
                header('Location: index.php?error=duplicate_title');
                exit;
            }

            // Panggil Model dengan parameter lengkap
            $todoModel->createTodo($title, $description, $is_finished); 
        }
        header('Location: index.php');
        exit;
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            // Status diubah dari form edit
            $is_finished = (int) ($_POST['is_finished'] ?? 0); 

            if ($id === null || empty(trim($title))) {
                 header('Location: index.php?error=invalid_data');
                 exit;
            }

            $todoModel = new TodoModel();

            // Validasi: Judul Unik (kecualikan item yang sedang di-update)
            if ($todoModel->checkDuplicateTitle($title, $id)) {
                header('Location: index.php?error=duplicate_title');
                exit;
            }
            
            // Panggil Model dengan parameter lengkap
            $todoModel->updateTodo($id, $title, $description, $is_finished);
        }
        header('Location: index.php');
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $todoModel = new TodoModel();
            $todoModel->deleteTodo($id);
        }
        
        // Mempertahankan filter/search setelah delete (diatur dari JS di View)
        $redirectParams = $_GET['redirect_params'] ?? '';
        
        // Jika parameter redirect tersedia, gunakan. Jika tidak, redirect ke index biasa.
        if (!empty($redirectParams)) {
            // decode agar karakter seperti & kembali menjadi &
            $redirectUrl = 'index.php' . urldecode($redirectParams);
        } else {
            $redirectUrl = 'index.php';
        }

        header('Location: ' . $redirectUrl);
        exit;
    }
    
    public function updateSorting()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Ambil data JSON dari body request
            $data = json_decode(file_get_contents('php://input'), true);
            
            $success = false;
            if (isset($data['order']) && is_array($data['order'])) {
                $todoModel = new TodoModel();
                // Panggil fungsi model untuk pembaruan batch sorting
                $success = $todoModel->updateBatchSortingOrder($data['order']);
            }
            
            // Output JSON response
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
        } else {
            http_response_code(405); // Method Not Allowed
        }
        exit;
    }
}