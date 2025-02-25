<?php
// Konfigurasi Database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "todolist_ukkdinda";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Buat tabel database otomatis dengan script PHP
$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    tasks_name VARCHAR(255) NOT NULL,
    status_tasks ENUM('Biasa', 'Cukup', 'Penting') DEFAULT 'Cukup',
    status_completed ENUM('Selesai', 'Belum Selesai') DEFAULT 'Belum Selesai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tasks_date DATE
)";

$conn->query($sql);

// Penambahan Data Baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tasks'])) {
    $tasks_name = $conn->real_escape_string($_POST['tasks_name']);
    $status_tasks = $conn->real_escape_string($_POST['status_tasks']);
    $status_complated = $conn->real_escape_string($_POST['status_completed']);
    $tasks_date = $conn->real_escape_string($_POST['tasks_date']);

    if (!empty($tasks_name) && !empty($status_tasks) && !empty($status_complated) && !empty($tasks_date)) {
        $stmt = $conn->prepare("INSERT INTO tasks (tasks_name, status_tasks, status_completed, tasks_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $tasks_name, $status_tasks, $status_complated, $tasks_date);
        $stmt->execute();
        $stmt->close();
    }
}

// Update Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_tasks'])) {
    $id = (int)$_POST['tasks_id'];
    $tasks_name = $conn->real_escape_string($_POST['tasks_name']);
    $status_tasks = $conn->real_escape_string($_POST['status_tasks']);
    $status_complated = $conn->real_escape_string($_POST['status_completed']);
    $tasks_date = $conn->real_escape_string($_POST['tasks_date']);

    if (!empty($tasks_name) && !empty($status_tasks) && !empty($tasks_date)) {
        $stmt = $conn->prepare("UPDATE tasks SET tasks_name=?, status_tasks=?, status_completed=?, tasks_date=? WHERE id=?");
        $stmt->bind_param("ssssi", $tasks_name, $status_tasks, $status_complated, $tasks_date, $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle deleting a task
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch all tasks
$result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="aset/style.css" rel="stylesheet">
    <title>TO-DO List UKK RPL 2025 Paket 2</title>
</head>
<body>

<h1> APLIKASI TO-DO List UKK paket2 RPL 2025 </h1>

<!-- Form untuk menambah tugas -->
<div class="form-container">
    <form method="POST" action="">
        <input type="text" name="tasks_name" placeholder="Tambahkan tugas baru..." required>

        <select name="status_tasks" required>
            <option value="Biasa">Biasa</option>
            <option value="Cukup">Cukup</option>
            <option value="Penting">Penting</option>
        </select>

        <select name="status_completed" required>
            <option value="Belum Selesai">Belum Selesai</option>
            <option value="Selesai">Selesai</option>
        </select>

        <input type="date" name="tasks_date" required>

        <button type="submit" name="add_tasks">Tambah List</button>
    </form>
</div>

<!-- Menampilkan daftar tugas -->
<div class="tasks-list">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="tasks-item" data-status="<?= $row['status_completed'] ?>">
            <form method="POST" action="">
                <input type="hidden" name="tasks_id" value="<?= $row['id'] ?>">
                <input type="text" name="tasks_name" value="<?= htmlspecialchars($row['tasks_name']) ?>" required>

                <select name="status_tasks" required>
                    <option value="Biasa" <?= $row['status_tasks'] == 'Biasa' ? 'selected' : '' ?>>Biasa</option>
                    <option value="Cukup" <?= $row['status_tasks'] == 'Cukup' ? 'selected' : '' ?>>Cukup</option>
                    <option value="Penting" <?= $row['status_tasks'] == 'Penting' ? 'selected' : '' ?>>Penting</option>
                </select>

                <select name="status_completed" required>
                    <option value="Belum Selesai" <?= $row['status_completed'] == 'Belum Selesai' ? 'selected' : '' ?>>Belum Selesai</option>
                    <option value="Selesai" <?= $row['status_completed'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>

                <input type="date" name="tasks_date" value="<?= $row['tasks_date'] ?>" required>
                <button type="submit" name="edit_tasks">Edit</button>
            </form>

            <!-- Form untuk menghapus tugas -->
            <form method="GET" action="" onsubmit="return confirm('Apakah yakin menghapus tugas ini?');">
                <input type="hidden" name="delete" value="<?= $row['id'] ?>">
                <button type="submit" class="delete" style="background-color: red; color: white; border: none; padding: 5px 10px; cursor: pointer;">Hapus</button>
            </form>

            <div class="tasks_date">Due Date: <?= date('d/m/Y', strtotime($row['tasks_date'])) ?></div>
        </div>
    <?php endwhile; ?>
</div>
    </body>
</html>

<?php
$conn->close();
?>
