<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: schedule-management.php');
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    
    // Add Technician
    if (isset($_POST['addTechnician'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $pin = $_POST['pin'];
        $user_id = 'T' . rand(1000, 9999);
        
        // Check if PIN already exists for any technician
        $checkStmt = $conn->prepare("SELECT id, pin FROM users WHERE user_type = 'TECHNICIAN'");
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        $pinExists = false;
        while ($user = $result->fetch_assoc()) {
            if (password_verify($pin, $user['pin'])) {
                $pinExists = true;
                break;
            }
        }
        $checkStmt->close();
        
        if ($pinExists) {
            echo json_encode(['success' => false, 'message' => 'This PIN is already in use. Please choose a different PIN.']);
            exit();
        }
        
        // Encrypt the PIN using bcrypt
        $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (user_id, user_type, first_name, last_name, email, pin, access_level, is_active) VALUES (?, 'TECHNICIAN', ?, ?, ?, ?, 'TECHNICIAN', 1)");
        $stmt->bind_param("sssss", $user_id, $first_name, $last_name, $email, $hashedPin);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Technician added successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add technician. Please try again.']);
        }
        $stmt->close();
        exit();
    }
    
    // Edit Technician
    if (isset($_POST['editTechnician'])) {
        $id = $_POST['id'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $pin = $_POST['pin'];
        
        // Check if PIN already exists for any OTHER technician
        $checkStmt = $conn->prepare("SELECT id, pin FROM users WHERE user_type = 'TECHNICIAN' AND id != ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        $pinExists = false;
        while ($user = $result->fetch_assoc()) {
            if (password_verify($pin, $user['pin'])) {
                $pinExists = true;
                break;
            }
        }
        $checkStmt->close();
        
        if ($pinExists) {
            echo json_encode(['success' => false, 'message' => 'This PIN is already in use by another technician. Please choose a different PIN.']);
            exit();
        }
        
        // Encrypt the PIN using bcrypt
        $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, pin = ? WHERE id = ? AND user_type = 'TECHNICIAN'");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $hashedPin, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Technician updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update technician. Please try again.']);
        }
        $stmt->close();
        exit();
    }
}

// Delete Technician (Keep as GET for now)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND user_type = 'TECHNICIAN'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: technician-panel.php");
    exit();
}

$result = $conn->query("SELECT id, first_name, last_name, email, pin, user_id, created_at FROM users WHERE user_type = 'TECHNICIAN' ORDER BY id DESC");
$technicians = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SADE - Technician Panel</title>
  <link rel="icon" type="image/png" href="../assets/images/sade-logo.png">
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="../assets/css/technician-panel.css" rel="stylesheet">
  <link href="../assets/css/schedule-management.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Notification styles */
    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 10000;
      display: none;
      min-width: 300px;
      animation: slideIn 0.3s ease-out;
    }
    
    .notification.success {
      background-color: #d4edda;
      border-left: 4px solid #28a745;
      color: #155724;
    }
    
    .notification.error {
      background-color: #f8d7da;
      border-left: 4px solid #dc3545;
      color: #721c24;
    }
    
    .notification .close-btn {
      float: right;
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: inherit;
      opacity: 0.7;
      padding: 0;
      margin-left: 10px;
    }
    
    .notification .close-btn:hover {
      opacity: 1;
    }
    
    @keyframes slideIn {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    @keyframes slideOut {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(400px);
        opacity: 0;
      }
    }
    
    .notification.hiding {
      animation: slideOut 0.3s ease-out;
    }
  </style>
</head>

<body>
  <!-- Notification container -->
  <div id="notification" class="notification"></div>

  <!-- Sidebar -->
  <?php include '../includes/sidebar.php'; ?>

  <!-- Main Panel -->
  <div class="main-container">
    <div class="main-content">
      <div class="header-container">
        <div class="header-left">
          <h1 class="page-title"><i class="fas fa-keyboard"></i> Technician Panel</h1>
        </div>
        <div class="header-right">
          <div class="user-profile">
            <button class="add-btn" onclick="openAddModal()">+ Add Technician</button>
          </div>
        </div>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>PIN</th>
              <th>User ID</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="technicianTableBody">
            <?php foreach ($technicians as $tech): ?>
            <tr>
              <td><b><?= htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']); ?></b></td>
              <td><?= htmlspecialchars($tech['email'] ?? 'N/A'); ?></td>
              <td>
                <?php 
                // Check if PIN is encrypted (60 characters and starts with $2y$)
                if (strlen($tech['pin']) === 60 && substr($tech['pin'], 0, 4) === '$2y$') {
                    echo '<span style="color: #666;">••••</span>';
                } else {
                    echo '<span style="color: #dc3545; font-weight: bold;">' . htmlspecialchars($tech['pin']) . '</span>';
                }
                ?>
              </td>
              <td><?= htmlspecialchars($tech['user_id']); ?></td>
              <td>
                <button class="btn" onclick='openEditModal(<?= json_encode($tech["id"]) ?>, <?= json_encode($tech["first_name"]) ?>, <?= json_encode($tech["last_name"]) ?>, <?= json_encode($tech["email"] ?? "") ?>)'><i class="fas fa-edit"></i> EDIT</button>
                <a href="?delete=<?= $tech['id'] ?>" onclick="return confirm('Delete this technician?')">
                  <button class="btn btn-delete"><i class="fas fa-trash"></i> DELETE</button>
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
      <form id="addForm">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <div class="pin-input-wrapper">
          <input type="password" id="addPin" name="pin" placeholder="4-digit PIN" pattern="\d{4}" maxlength="4" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
          <button type="button" class="eye-toggle" onclick="togglePinVisibility('addPin', this)">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <button type="submit" class="btn">Add</button>
        <button type="button" class="btn btn-delete" onclick="closeModal('addModal')">Cancel</button>
      </form>
    </div>
  </div>

  <!-- Edit Technician Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <h3>Edit Technician</h3>
      <form id="editForm">
        <input type="hidden" name="id" id="editId">
        <input type="text" name="first_name" id="editFirstName" placeholder="First Name" required>
        <input type="text" name="last_name" id="editLastName" placeholder="Last Name" required>
        <input type="email" name="email" id="editEmail" placeholder="Email" required>
        <div class="pin-input-wrapper">
          <input type="password" id="editPin" name="pin" placeholder="4-digit PIN" pattern="\d{4}" maxlength="4" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
          <button type="button" class="eye-toggle" onclick="togglePinVisibility('editPin', this)">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <button type="submit" class="btn">Save</button>
        <button type="button" class="btn btn-delete" onclick="closeModal('editModal')">Cancel</button>
      </form>
    </div>
  </div>

  <script>
    function openAddModal() {
      document.getElementById("addModal").style.display = "block";
      document.getElementById("addForm").reset();
    }
    
    function openEditModal(id, firstName, lastName, email) {
      document.getElementById("editModal").style.display = "block";
      document.getElementById("editId").value = id;
      document.getElementById("editFirstName").value = firstName;
      document.getElementById("editLastName").value = lastName;
      document.getElementById("editEmail").value = email;
      document.getElementById("editPin").value = "";
    }
    
    function closeModal(id) {
      document.getElementById(id).style.display = "none";
    }
    
    function togglePinVisibility(inputId, button) {
      const input = document.getElementById(inputId);
      const icon = button.querySelector('i');
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
    
    function showNotification(message, type) {
      const notification = document.getElementById('notification');
      notification.className = 'notification ' + type;
      notification.innerHTML = `
        <button class="close-btn" onclick="hideNotification()">&times;</button>
        <strong>${type === 'success' ? '✓ Success' : '✗ Error'}</strong><br>
        ${message}
      `;
      notification.style.display = 'block';
      
      // Auto-hide after 5 seconds
      setTimeout(() => {
        hideNotification();
      }, 5000);
    }
    
    function hideNotification() {
      const notification = document.getElementById('notification');
      notification.classList.add('hiding');
      setTimeout(() => {
        notification.style.display = 'none';
        notification.classList.remove('hiding');
      }, 300);
    }
    
    // Handle Add Technician Form Submission
    document.getElementById('addForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      formData.append('addTechnician', '1');
      
      fetch('technician-panel.php', {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          closeModal('addModal');
          // Reload page after 1.5 seconds to show updated list
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          showNotification(data.message, 'error');
        }
      })
      .catch(error => {
        showNotification('An error occurred. Please try again.', 'error');
      });
    });
    
    // Handle Edit Technician Form Submission
    document.getElementById('editForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      formData.append('editTechnician', '1');
      
      fetch('technician-panel.php', {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          closeModal('editModal');
          // Reload page after 1.5 seconds to show updated list
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          showNotification(data.message, 'error');
        }
      })
      .catch(error => {
        showNotification('An error occurred. Please try again.', 'error');
      });
    });
    
    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
      }
    }
  </script>
</body>
</html>
<?php $conn->close(); ?>
