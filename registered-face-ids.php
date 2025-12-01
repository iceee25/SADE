<?php
session_start();
require_once '../includes/db_connect.php';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: registered-face-ids.php");
    exit();
}

// Handle add faculty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['faculty_name'])) {
    $faculty_name = $_POST['faculty_name'];
    $first_name = explode(' ', $faculty_name)[0] ?? '';
    $last_name = implode(' ', array_slice(explode(' ', $faculty_name), 1)) ?? '';
    $user_id = 'F' . rand(1000, 9999);
    $pin = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $conn->prepare("INSERT INTO users (user_id, user_type, first_name, last_name, pin, access_level, is_active) VALUES (?, 'FACULTY', ?, ?, ?, 'FACULTY', 1)");
    $stmt->bind_param("ssss", $user_id, $first_name, $last_name, $pin);
    $stmt->execute();
    $stmt->close();
    header("Location: registered-face-ids.php");
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$query = "SELECT id, first_name, last_name, user_id, is_active FROM users WHERE user_type = 'FACULTY' ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
$face_ids = $result->fetch_all(MYSQLI_ASSOC);

$count_result = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'FACULTY'");
$count_row = $count_result->fetch_assoc();
$total_face_ids = $count_row['total'];
$total_pages = ceil($total_face_ids / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SADE - Registered Face IDs</title>
  <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
  <div class="main-container">
    
    <!-- Sidebar -->
    <aside class="sidebar">
      <?php include '../includes/sidebar.php'; ?>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <div class="header">
        <h1 class="page-title">Registered Face IDs</h1>
        <div class="user-profile">
          <button id="openModal" class="add-btn">+ Add Faculty</button>
        </div>
      </div>

      <!-- Table -->
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>User ID</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
              foreach ($face_ids as $faculty) {
                $statusClass = $faculty['is_active'] ? 'active' : 'inactive';
                $statusText = $faculty['is_active'] ? 'ACTIVE' : 'INACTIVE';
                echo "
                  <tr>
                    <td>{$faculty['first_name']} {$faculty['last_name']}</td>
                    <td>{$faculty['user_id']}</td>
                    <td><span class='status $statusClass'>$statusText</span></td>
                    <td>
                      <form method='POST' style='display:inline;' onsubmit='return confirm(\"Delete this faculty?\");'>
                        <input type='hidden' name='delete_id' value='{$faculty['id']}'>
                        <button type='submit' class='delete-btn'>DELETE</button>
                      </form>
                    </td>
                  </tr>
                ";
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add Faculty Modal -->
  <div id="facultyModal" class="modal">
    <div class="modal-content">
      <h2>Add New Faculty</h2>

      <form method="POST">
        <label for="facultyName">Faculty Name</label>
        <input type="text" id="facultyName" name="faculty_name" placeholder="Input faculty name" required>

        <div class="modal-actions">
          <button type="submit" class="add">Add</button>
          <button type="button" class="cancel" id="closeModal">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById("facultyModal");
    const openBtn = document.getElementById("openModal");
    const closeBtn = document.getElementById("closeModal");

    openBtn.onclick = () => modal.style.display = "flex";
    closeBtn.onclick = () => modal.style.display = "none";
    window.onclick = (e) => { if (e.target == modal) modal.style.display = "none"; };
  </script>
</body>
</html>
<?php $conn->close(); ?>
