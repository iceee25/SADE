<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: schedule-management.php');
    exit();
}

// Delete Technician
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND user_type = 'TECHNICIAN'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: technician-panel.php");
    exit();
}

// Add Technician
if (isset($_POST['addTechnician'])) {
    $name = $_POST['name'];
    $pin = $_POST['pin'];
    $first_name = explode(' ', $name)[0] ?? '';
    $last_name = implode(' ', array_slice(explode(' ', $name), 1)) ?? '';
    $user_id = 'T' . rand(1000, 9999);
    
    $stmt = $conn->prepare("INSERT INTO users (user_id, user_type, first_name, last_name, pin, access_level, is_active) VALUES (?, 'TECHNICIAN', ?, ?, ?, 'TECHNICIAN', 1)");
    $stmt->bind_param("ssss", $user_id, $first_name, $last_name, $pin);
    $stmt->execute();
    $stmt->close();
    header("Location: technician-panel.php");
    exit();
}

// Edit Technician
if (isset($_POST['editTechnician'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $pin = $_POST['pin'];
    $first_name = explode(' ', $name)[0] ?? '';
    $last_name = implode(' ', array_slice(explode(' ', $name), 1)) ?? '';
    
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, pin = ? WHERE id = ? AND user_type = 'TECHNICIAN'");
    $stmt->bind_param("sssi", $first_name, $last_name, $pin, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: technician-panel.php");
    exit();
}

$result = $conn->query("SELECT id, first_name, last_name, pin, user_id, created_at FROM users WHERE user_type = 'TECHNICIAN' ORDER BY id DESC");
$technicians = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SADE - Technician Panel</title>
  <link href="../assets/css/style.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: #fff;
      margin: 0;
      padding: 0;
      display: flex;
    }
    .main-container {
      flex: 1;
      display: flex;
    }
    .main-content {
      flex: 1;
      padding: 30px 50px;
    }
    h1.page-title {
      color: #900;
      margin-bottom: 20px;
    }
    .table-wrapper {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th {
      background: #c00;
      color: white;
      padding: 12px;
      text-align: left;
    }
    td {
      padding: 12px;
      border-bottom: 1px solid #eee;
    }
    tr:hover {
      background: #fff5f5;
    }
    .add-btn {
      background: #c00;
      color: #fff;
      padding: 10px 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin-bottom: 20px;
    }
    .add-btn:hover {
      background: #a00;
    }
    .btn {
      background: #c00;
      border: none;
      color: white;
      padding: 6px 14px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
    }
    .btn-delete {
      background: #900;
    }
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      padding-top: 100px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background: rgba(0,0,0,0.5);
    }
    .modal-content {
      background: #fff;
      margin: auto;
      padding: 20px;
      border-radius: 10px;
      width: 400px;
    }
    input {
      width: 100%;
      padding: 8px;
      margin: 5px 0 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <?php include '../includes/sidebar.php'; ?>

  <!-- Main Panel -->
  <div class="main-container">
    <div class="main-content">
      <h1 class="page-title"><i class="fas fa-keyboard"></i> Technician Panel</h1>
      <button class="add-btn" onclick="openAddModal()">+ Add Technician</button>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>PIN</th>
              <th>User ID</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($technicians as $tech): ?>
            <tr>
              <td><b><?= htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?></b></td>
              <td><?= htmlspecialchars($tech['pin']); ?></td>
              <td><?= htmlspecialchars($tech['user_id']); ?></td>
              <td>
                <button class="btn" onclick='openEditModal("<?= $tech["id"] ?>", "<?= $tech["first_name"] ?> <?= $tech["last_name"] ?>", "<?= $tech["pin"] ?>")'>EDIT</button>
                <a href="?delete=<?= $tech['id'] ?>" onclick="return confirm('Delete this technician?')">
                  <button class="btn btn-delete">DELETE</button>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add Technician Modal -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <h3>Add Technician</h3>
      <form method="POST">
        <input type="text" name="name" placeholder="Technician Name" required>
        <input type="number" name="pin" placeholder="4-digit PIN" min="0" max="9999" required>
        <button type="submit" class="btn" name="addTechnician">Add</button>
        <button type="button" class="btn btn-delete" onclick="closeModal('addModal')">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Edit Technician Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <h3>Edit Technician</h3>
      <form method="POST">
        <input type="hidden" name="id" id="editId">
        <input type="text" name="name" id="editName" required>
        <input type="number" name="pin" id="editPin" min="0" max="9999" required>
        <button type="submit" class="btn" name="editTechnician">Save</button>
        <button type="button" class="btn btn-delete" onclick="closeModal('editModal')">Cancel</button>
      </form>
    </div>
  </div>

  <script>
    function openAddModal() {
      document.getElementById("addModal").style.display = "block";
    }
    function openEditModal(id, name, pin) {
      document.getElementById("editModal").style.display = "block";
      document.getElementById("editId").value = id;
      document.getElementById("editName").value = name;
      document.getElementById("editPin").value = pin;
    }
    function closeModal(id) {
      document.getElementById(id).style.display = "none";
    }
    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
      }
    }
  </script>
</body>
</html>
<?php $conn->close(); ?>
