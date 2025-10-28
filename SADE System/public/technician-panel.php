<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SADE - Technician Panel</title>
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
        <h1 class="page-title">Technician Panel</h1>
        <div class="user-profile">
          <button id="openModal" class="add-btn">+ Add Technician</button>
        </div>
      </div>

      <!-- Table -->
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>PIN</th>
              <th>Last Room Entry</th>
              <th>Action</th>
            </tr>
          </thead>
         <tbody>
        <?php
        $faces = [
        [
            'name' => 'Jericho Co Leng',
            'code' => '0804',
            'last_entry_time' => '2025-10-24 14:45:00',
            'last_entry_room' => 'Room 1811'
        ],
        [
            'name' => 'Leonard Josh Rosales',
            'code' => '0811',
            'last_entry_time' => '2025-10-23 09:12:00',
            'last_entry_room' => 'Room 1812'
        ],
        [
            'name' => 'Govinda Borinaga',
            'code' => '0525',
            'last_entry_time' => '2025-10-22 18:30:00',
            'last_entry_room' => 'Room 1812'
        ]
        ];
        foreach ($faces as $face) {
            $formattedDate = date("M j, Y — g:i A", strtotime($face['last_entry_time']));
         echo "
    <tr>
        <td>" . htmlspecialchars($face['name']) . "</td>
        <td>" . htmlspecialchars($face['code']) . "</td>
        <td>
            $formattedDate<br>
            <small>" . htmlspecialchars($face['last_entry_room']) . "</small>
        </td>
        <td>
        <button class='edit-btn'>EDIT</button>
        <button class='delete-btn'>DELETE</button>
        </td>
    </tr>";
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

      <label for="facultyCode">Passkay PIN</label>
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
