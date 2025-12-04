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
  <link href="../assets/css/technician-panel.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
