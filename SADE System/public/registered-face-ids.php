<?php
session_start();
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
              <th>Code</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $face_ids = [
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "REGISTERED"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "PENDING"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "REGISTERED"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "REGISTERED"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "REGISTERED"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "PENDING"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "PENDING"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "REGISTERED"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "PENDING"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "REGISTERED"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "REGISTERED"],
                ["name" => "Lorem Ipsum", "code" => "#******", "status" => "REGISTERED"]
              ];

              foreach ($face_ids as $face) {
                $statusClass = strtolower($face['status']);
                echo "
                  <tr>
                    <td>{$face['name']}</td>
                    <td>{$face['code']}</td>
                    <td><span class='status {$statusClass}'>{$face['status']}</span></td>
                    <td><button class='delete-btn'>DELETE</button></td>
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

      <label for="facultyName">Faculty Name</label>
      <input type="text" id="facultyName" placeholder="Input faculty name">

      <label for="facultyCode">Code for Registration</label>
      <input type="text" id="facultyCode" placeholder="*1435#">

      <div class="modal-actions">
        <button class="add">Add</button>
        <button class="cancel" id="closeModal">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    // Modal open/close logic
    const modal = document.getElementById("facultyModal");
    const openBtn = document.getElementById("openModal");
    const closeBtn = document.getElementById("closeModal");

    openBtn.onclick = () => modal.style.display = "flex";
    closeBtn.onclick = () => modal.style.display = "none";
    window.onclick = (e) => { if (e.target == modal) modal.style.display = "none"; };
  </script>
</body>
</html>
