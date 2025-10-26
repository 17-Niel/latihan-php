<?php
// File: models/TodoModel.php

require_once (__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    public function __construct()
    {
        // Inisialisasi koneksi database PostgreSQL
        $this->conn = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASSWORD);
        if (!$this->conn) {
            die('Koneksi database gagal. Pastikan config.php dan ekstensi php-pgsql sudah benar.');
        }
    }

    public function getFilteredAndSearchedTodos($filter, $search)
    {
        $whereClauses = [];
        $params = [];
        $paramIndex = 1;

        // 1. Logika Filter (is_finished)
        if ($filter === 'finished') {
            $whereClauses[] = 'is_finished = TRUE'; 
        } elseif ($filter === 'unfinished') {
            $whereClauses[] = 'is_finished = FALSE'; 
        }

        // 2. Logika Pencarian (Search) - Cari di kolom title
        if (!empty($search)) {
            // Gunakan ILIKE untuk pencarian case-insensitive
            $whereClauses[] = 'title ILIKE $' . $paramIndex++;
            $params[] = '%' . $search . '%'; 
        }

        $where = count($whereClauses) > 0 ? ' WHERE ' . implode(' AND ', $whereClauses) : '';

        // 3. Logika Sorting - Selalu urutkan berdasarkan sorting_order
        $query = 'SELECT * FROM todo' . $where . ' ORDER BY sorting_order ASC';

        $result = pg_query_params($this->conn, $query, $params);
        $todos = []; 
        
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }
    
    public function checkDuplicateTitle($title, $id = null)
    {
        
        // Query akan membandingkan LOWER(title) dengan LOWER($1)
        $query = 'SELECT id FROM todo WHERE LOWER(title) = LOWER($1)';
        $params = [$title]; // $title tetap dikirim sebagai parameter
        $paramIndex = 2;
        
        if ($id !== null) {
            $query .= ' AND id != $' . $paramIndex++;
            $params[] = $id;
        }

        $result = pg_query_params($this->conn, $query, $params);
        return pg_num_rows($result) > 0;
    }
    
    public function createTodo($title, $description = '', $is_finished = 0) 
    {
        // Tentukan sorting_order baru (nilai terbesar saat ini + 1)
        $maxOrderResult = pg_query($this->conn, "SELECT COALESCE(MAX(sorting_order), 0) + 1 FROM todo");
        $newOrder = pg_fetch_result($maxOrderResult, 0, 0);

        // Tambahkan is_finished dan sorting_order ke query
        $query = 'INSERT INTO todo (title, description, is_finished, sorting_order) VALUES ($1, $2, $3, $4)';
        
        $result = pg_query_params($this->conn, $query, [$title, $description, $is_finished, $newOrder]); 
        
        return $result !== false;
    }

    public function updateTodo($id, $title, $description, $is_finished)
    {
        // Ubah kolom activity -> title, status -> is_finished, dan tambahkan description
        $query = 'UPDATE todo SET title=$1, description=$2, is_finished=$3 WHERE id=$4';
        
        $result = pg_query_params($this->conn, $query, [$title, $description, $is_finished, $id]);
        return $result !== false;
    }

    public function deleteTodo($id)
    {
        $query = 'DELETE FROM todo WHERE id=$1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return $result !== false;
    }

    public function updateBatchSortingOrder(array $newOrder)
    {
        // Query UNNEST untuk melakukan update massal yang efisien
        $sql = 'UPDATE todo AS t SET sorting_order = c.sorting_order 
                FROM (VALUES ';
        $values = [];
        $params = [];
        $paramIndex = 1;

        // Bangun bagian VALUES: ($1, $2), ($3, $4), ...
        foreach ($newOrder as $item) {
            $values[] = '($' . $paramIndex++ . ', $' . $paramIndex++ . ')';
            $params[] = $item['id'];
            $params[] = $item['order'];
        }

        $sql .= implode(', ', $values);
        // Penting: Konversi c.id ke integer agar sesuai dengan t.id (SERIAL)
        $sql .= ') AS c(id, sorting_order) WHERE c.id::integer = t.id';

        // Gunakan transaksi untuk atomisitas (jika gagal, semua dibatalkan)
        pg_query($this->conn, "BEGIN");
        $result = pg_query_params($this->conn, $sql, $params);
        
        if ($result) {
            pg_query($this->conn, "COMMIT");
            return true;
        } else {
            pg_query($this->conn, "ROLLBACK");
            return false;
        }
    }
}