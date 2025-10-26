<?php
// Pastikan variabel $filter dan $search tersedia (dikirim dari Controller)
$filter = $filter ?? 'all';
$search = $_GET['search'] ?? '';
// Catatan: Variabel $todos diasumsikan tersedia dari Controller
?>
<!DOCTYPE html>
<html>
<head>
    <title>Aplikasi Todolist Niel | Rapi & Modern</title>
    <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/vendor/bootstrap-icons-1.13.1/bootstrap-icons.min.css" rel="stylesheet" />
    
    <link href="/assets/css/style.css" rel="stylesheet" />
    
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    </head>
<body>
<div class="container py-5">
    
    <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate_title'): ?>
    <div class="alert alert-danger alert-dismissible fade show main-card mx-auto mb-4" role="alert">
        <strong>❌ Gagal!</strong> Judul Todo harus unik. Coba judul lain.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-4">
            
            <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                <h2 class="todo-header"><i class="bi bi-list-check me-2"></i> Daftar Aktivitas</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodo">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Todo
                </button>
            </div>
            
            <div class="row my-4 g-3 align-items-center">
                
                <div class="col-lg-6">
                    <div class="input-group">
                         <span class="input-group-text bg-light text-secondary"><i class="bi bi-funnel"></i> Filter:</span>
                        <a href="?filter=all&search=<?= htmlspecialchars($search) ?>" 
                           class="btn <?= $filter === 'all' ? 'btn-dark' : 'btn-outline-dark' ?> btn-sm">Semua</a>
                        <a href="?filter=finished&search=<?= htmlspecialchars($search) ?>" 
                           class="btn <?= $filter === 'finished' ? 'btn-success' : 'btn-outline-success' ?> btn-sm">Selesai</a>
                        <a href="?filter=unfinished&search=<?= htmlspecialchars($search) ?>" 
                           class="btn <?= $filter === 'unfinished' ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm">Belum Selesai</a>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Cari berdasarkan judul..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-secondary" type="submit"><i class="bi bi-search"></i></button>
                            <?php if (!empty($search)): ?>
                                <a href="?filter=<?= htmlspecialchars($filter) ?>" class="btn btn-outline-secondary" title="Reset Pencarian"><i class="bi bi-x"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="list-group list-group-flush border rounded-3 p-0">
                <div id="todoTableBody">
                    <?php if (!empty($todos)): ?>
                        <?php foreach ($todos as $i => $todo): 
                            $is_finished = $todo['is_finished'] === 't' || $todo['is_finished'] == 1;
                        ?>
                        <div class="todo-item list-group-item list-group-item-action <?= $is_finished ? 'is-finished' : '' ?>" data-id="<?= $todo['id'] ?>">
                            
                            <span class="sortable-handle">☰</span>
                            <span class="text-muted me-3" style="width: 25px;"><?= $i + 1 ?>.</span>
                            
                            <span class="badge me-3 p-2 <?= $is_finished ? 'bg-success' : 'bg-danger' ?>">
                                <?= $is_finished ? '<i class="bi bi-check"></i> Selesai' : '<i class="bi bi-clock"></i> Pending' ?>
                            </span>

                            <div class="todo-item-title me-3" title="<?= htmlspecialchars($todo['title']) ?>">
                                <?= htmlspecialchars($todo['title']) ?>
                            </div>
                            
                            <div class="todo-item-date me-3">
                                <i class="bi bi-calendar-event me-1"></i> <?= date('d M Y', strtotime($todo['created_at'])) ?>
                            </div>
                            
                            <div class="todo-item-actions">
                                <button class="btn btn-sm btn-outline-info" title="Detail"
                                    onclick="showModalDetailTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['title'])) ?>', '<?= htmlspecialchars(addslashes($todo['description'] ?? '')) ?>', <?= (int)$is_finished ?>, '<?= date('d F Y H:i', strtotime($todo['created_at'])) ?>', '<?= date('d F Y H:i', strtotime($todo['updated_at'])) ?>')">
                                    <i class="bi bi-eye"></i> 
                                </button>
                                <button class="btn btn-sm btn-outline-warning" title="Ubah"
                                    onclick="showModalEditTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['title'])) ?>', '<?= htmlspecialchars(addslashes($todo['description'] ?? '')) ?>', <?= (int)$is_finished ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"
                                    onclick="showModalDeleteTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['title'])) ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="todo-item text-center text-muted py-5">
                            <i class="bi bi-list-task me-2 fs-4"></i>
                            <?php if (!empty($search)): ?>
                                Tidak ada hasil untuk pencarian "<?= htmlspecialchars($search) ?>" dengan filter "<?= htmlspecialchars($filter) ?>".
                            <?php else: ?>
                                Daftar Todo kosong. Yuk, tambahkan aktivitas barumu!
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div> </div>
    </div>
</div>

<div class="modal fade" id="addTodo" tabindex="-1" aria-labelledby="addTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addTodoLabel"><i class="bi bi-plus-circle me-2"></i> Tambah Data Todo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?page=create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputTitle" class="form-label">Judul Aktivitas <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" id="inputTitle"
                            placeholder="Contoh: Belajar membuat aplikasi website" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputDescription" class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" id="inputDescription" rows="3"
                            placeholder="Detail tentang aktivitas ini..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="selectIsFinished" class="form-label">Status Awal</label>
                        <select class="form-select" name="is_finished" id="selectIsFinished">
                            <option value="0" selected>Belum Selesai</option> <option value="1">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editTodo" tabindex="-1" aria-labelledby="editTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editTodoLabel"><i class="bi bi-pencil-square me-2"></i> Ubah Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?page=update" method="POST">
                <input name="id" type="hidden" id="inputEditTodoId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputEditTitle" class="form-label">Judul Aktivitas <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" id="inputEditTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputEditDescription" class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" id="inputEditDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="selectEditIsFinished" class="form-label">Status</label>
                        <select class="form-select" name="is_finished" id="selectEditIsFinished">
                            <option value="0">Belum Selesai</option>
                            <option value="1">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-arrow-repeat"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="detailTodo" tabindex="-1" aria-labelledby="detailTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detailTodoLabel"><i class="bi bi-card-list me-2"></i> Detail Todo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 id="detailTitle" class="text-primary fw-bold"></h4>
                <p class="text-muted small border-bottom pb-2">ID: <span id="detailId"></span></p>
                
                <h6>Deskripsi:</h6>
                <p id="detailDescription" class="alert alert-light border-start border-3 border-primary shadow-sm p-3"></p>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h6>Status:</h6>
                        <p><span id="detailStatusBadge" class="badge p-2"></span></p>
                    </div>
                </div>

                <div class="row border-top pt-3 mt-3">
                    <div class="col-md-6">
                        <h6 class="text-secondary">Dibuat:</h6>
                        <p id="detailCreatedAt" class="small"></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-secondary">Diperbarui:</h6>
                        <p id="detailUpdatedAt" class="small"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteTodo" tabindex="-1" aria-labelledby="deleteTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteTodoLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                    <div>
                        Kamu akan menghapus todo **<strong class="text-danger" id="deleteTodoTitle"></strong>**.
                        Apakah kamu yakin? Tindakan ini tidak dapat dibatalkan.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a id="btnDeleteTodo" class="btn btn-danger"><i class="bi bi-trash-fill"></i> Ya, Tetap Hapus</a>
            </div>
        </div>
    </div>
</div>

<script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>
<script>
    // URL endpoint API untuk sorting. Sesuaikan jika Anda menggunakan file/endpoint yang berbeda.
    const SORTING_API_URL = 'index.php?page=updateSorting';

    /**
     * Menampilkan modal Edit Todo
     * @param {number} todoId 
     * @param {string} title 
     * @param {string} description 
     * @param {number} isFinished - 0 (Belum) atau 1 (Selesai)
     */
    function showModalEditTodo(todoId, title, description, isFinished) {
        document.getElementById("inputEditTodoId").value = todoId;
        document.getElementById("inputEditTitle").value = title;
        document.getElementById("inputEditDescription").value = description;
        document.getElementById("selectEditIsFinished").value = isFinished;
        var myModal = new bootstrap.Modal(document.getElementById("editTodo"));
        myModal.show();
    }

    /**
     * Menampilkan modal Detail Todo
     */
    function showModalDetailTodo(todoId, title, description, isFinished, createdAt, updatedAt) {
        document.getElementById("detailId").innerText = todoId;
        document.getElementById("detailTitle").innerText = title;
        document.getElementById("detailDescription").innerHTML = description ? description.replace(/\n/g, '<br>') : "Tidak ada deskripsi."; // Tambahkan support line break
        
        const statusBadge = document.getElementById("detailStatusBadge");
        statusBadge.innerText = isFinished ? 'Selesai' : 'Belum Selesai';
        statusBadge.className = isFinished ? 'badge bg-success p-2' : 'badge bg-danger p-2';

        document.getElementById("detailCreatedAt").innerText = createdAt;
        document.getElementById("detailUpdatedAt").innerText = updatedAt;

        var myModal = new bootstrap.Modal(document.getElementById("detailTodo"));
        myModal.show();
    }

    /**
     * Menampilkan modal Delete Todo
     * @param {number} todoId 
     * @param {string} title 
     */
    function showModalDeleteTodo(todoId, title) {
        document.getElementById("deleteTodoTitle").innerText = title;
        // Mempertahankan filter/search saat redirect delete
        const currentUrlParams = window.location.search;
        document.getElementById("btnDeleteTodo").setAttribute("href", `?page=delete&id=${todoId}&redirect_params=${encodeURIComponent(currentUrlParams)}`);
        var myModal = new bootstrap.Modal(document.getElementById("deleteTodo"));
        myModal.show();
    }
    
    // ====================================
    // SortableJS Implementation
    // ====================================
    document.addEventListener('DOMContentLoaded', (event) => {
        // Target div.list-group yang berisi item-item
        const todoTableBody = document.getElementById('todoTableBody'); 
        if (todoTableBody) {
            Sortable.create(todoTableBody, {
                handle: '.sortable-handle', // Area yang bisa di-drag
                animation: 150,
                onEnd: function (evt) {
                    const newOrder = [];
                    // Iterasi melalui div.todo-item
                    todoTableBody.querySelectorAll('.todo-item').forEach(function(row, index) { 
                        const todoId = row.getAttribute('data-id');
                        if (todoId) {
                            newOrder.push({
                                id: todoId,
                                order: index + 1
                            });
                        }
                    });

                    updateSorting(newOrder);
                }
            });
        }
    });

    /**
     * Mengirim data sorting yang baru ke API
     */
    function updateSorting(newOrder) {
        fetch(SORTING_API_URL, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify({ order: newOrder })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Sorting berhasil diperbarui di database.');
            } else {
                console.error('Gagal memperbarui sorting:', data);
            }
        })
        .catch(error => {
            console.error('Error saat mengirim data sorting:', error);
        });
    }
</script>
</body>
</html>