<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_role'])) {
    header('Location: signin.php');
    exit;
}

if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="student_template.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID Number', 'Full Name', 'Email']);
    fputcsv($output, ['2022178651', 'Sample Student', 'student@example.com']);
    fclose($output);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] === 0) {
        $csv_file = fopen($file['tmp_name'], 'r');
        $skip_header = true;
        $imported_count = 0;
        
        while (($row = fgetcsv($csv_file)) !== false) {
            if ($skip_header) {
                $skip_header = false;
                continue;
            }
            
            if (count($row) >= 3) {
                $id_number = trim($row[0]);
                $full_name = trim($row[1]);
                $email = trim($row[2]);
                $user_type = 'Student';
                $status = 'PRESENT';
                
                if ($id_number && $full_name && $email) {
                    $stmt = $conn->prepare("
                        INSERT INTO participants (id_number, full_name, user_type, email, status) 
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), email = VALUES(email)
                    ");
                    $stmt->bind_param('sssss', $id_number, $full_name, $user_type, $email, $status);
                    if ($stmt->execute()) {
                        $imported_count++;
                    }
                    $stmt->close();
                }
            }
        }
        
        fclose($csv_file);
        $_SESSION['csv_message'] = "Successfully imported $imported_count students";
        header('Location: participant-registration.php');
        exit;
    }
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch participants
$result = $conn->query("
    SELECT 
        id,
        id_number,
        full_name,
        user_type,
        email,
        status
    FROM participants
    ORDER BY id DESC
    LIMIT $limit OFFSET $offset
");

$participants = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Get total count for pagination
$count_result = $conn->query("SELECT COUNT(*) as total FROM participants");
$count_row = $count_result->fetch_assoc();
$total_participants = $count_row['total'];
$total_pages = ceil($total_participants / $limit);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_student') {
            $id_number = $_POST['id_number'] ?? '';
            $full_name = $_POST['full_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $user_type = 'Student';
            $status = 'PRESENT';
            
            if ($id_number && $full_name && $email) {
                $stmt = $conn->prepare("INSERT INTO participants (id_number, full_name, user_type, email, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('sssss', $id_number, $full_name, $user_type, $email, $status);
                if ($stmt->execute()) {
                    header('Location: participant-registration.php');
                    exit;
                }
            }
        } elseif ($_POST['action'] === 'delete_student' && isset($_POST['student_id'])) {
            $student_id = (int)$_POST['student_id'];
            $stmt = $conn->prepare("DELETE FROM participants WHERE id = ?");
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            header('Location: participant-registration.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADE</title>
    <link rel="icon" type="image/png" href="/assets/images/sade-logo.png">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .registration-container {
            background: white;
            border-radius: 16px;
            border: 2px solid #b30000;
            padding: 40px;
            margin: 20px;
        }

        .registration-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .registration-title {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .registration-logo {
            width: 40px;
            height: 40px;
            background: #b30000;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .role-selector {
            background: #b30000;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        .registration-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .collective-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }

        .btn-csv-download {
            background: #b30000;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }

        .btn-csv-download:hover {
            background: #a10000;
        }

        .btn-upload {
            background: #10b981;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }

        .btn-upload:hover {
            background: #059669;
        }

        .registration-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #666;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #b30000;
            background: white;
        }

        .form-input::placeholder {
            color: #ccc;
        }

        .btn-add-student {
            background: #10b981;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }

        .btn-add-student:hover {
            background: #059669;
        }

        .participants-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .participants-table thead {
            background: #f5f5f5;
        }

        .participants-table th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e5e7eb;
            font-size: 13px;
        }

        .participants-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }

        .status-present {
            background: #dcfce7;
            color: #166534;
        }

        .status-absent {
            background: #f3f4f6;
            color: #6b7280;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 12px;
            transition: background 0.3s;
        }

        .btn-edit {
            background: #e5e7eb;
            color: #666;
        }

        .btn-edit:hover {
            background: #d1d5db;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .pagination-info {
            font-size: 13px;
            color: #666;
        }

        .pagination-btn {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .pagination-btn:hover:not(:disabled) {
            border-color: #b30000;
            color: #b30000;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Add styles for CSV upload modal and success message */
        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .success-message.hidden {
            display: none;
        }

        #csv-file-input {
            display: none;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <?php include '../includes/sidebar.php'; ?>
        </aside>

        <div class="main-content">
            <!-- Added user dropdown to header -->
            <div class="header-top">
                <div></div>
                <div class="header-right">
                    <?php include '../includes/user-dropdown.php'; ?>
                </div>
            </div>

            <div class="registration-container">
                <!-- Add success message display -->
                <?php if (isset($_SESSION['csv_message'])): ?>
                <div class="success-message">
                    <span><?php echo htmlspecialchars($_SESSION['csv_message']); ?></span>
                    <button onclick="this.parentElement.classList.add('hidden')" style="background: none; border: none; cursor: pointer; font-weight: bold;">×</button>
                </div>
                <?php unset($_SESSION['csv_message']); ?>
                <?php endif; ?>

                <div class="registration-header">
                    <div class="registration-title">
                        <div class="registration-logo">◀</div>
                        Participant Registration
                    </div>
                    <button class="role-selector">Technician▼</button>
                </div>

                <div class="registration-section">
                    <div class="section-title">Collective Student Registration</div>
                    <div class="collective-buttons">
                        <!-- Update download button to trigger CSV download -->
                        <a href="?download=csv" class="btn-csv-download">
                            <i class="fas fa-download"></i> Download CSV Template
                        </a>
                        <!-- Update upload button to trigger file input -->
                        <button class="btn-upload" onclick="document.getElementById('csv-file-input').click();">
                            <i class="fas fa-upload"></i> Upload File
                        </button>
                        <!-- Hidden file input for CSV upload -->
                        <form id="csv-upload-form" method="POST" enctype="multipart/form-data" style="display: none;">
                            <input type="file" id="csv-file-input" name="csv_file" accept=".csv" onchange="document.getElementById('csv-upload-form').submit();">
                        </form>
                    </div>
                </div>

                <div class="registration-section">
                    <div class="section-title">Individual Student Registration</div>
                    <form class="registration-form" method="POST">
                        <div class="form-group">
                            <label class="form-label">ID Number</label>
                            <input type="text" class="form-input" name="id_number" placeholder="2022178651" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-input" name="full_name" placeholder="Full Name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input" name="email" placeholder="name@example.com" required>
                            <small style="color: #999;">Please enter a valid email address.</small>
                        </div>
                        <input type="hidden" name="action" value="add_student">
                        <button type="submit" class="btn-add-student">
                            <i class="fas fa-plus"></i> Add Student
                        </button>
                    </form>
                </div>

                <table class="participants-table">
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Full Name</th>
                            <th>User Type</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($participant['id_number']); ?></td>
                            <td><?php echo htmlspecialchars($participant['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($participant['user_type']); ?></td>
                            <td><?php echo htmlspecialchars($participant['email']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($participant['status']); ?>">
                                    <?php echo htmlspecialchars($participant['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-edit">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_student">
                                        <input type="hidden" name="student_id" value="<?php echo $participant['id']; ?>">
                                        <button type="submit" class="btn-delete" onclick="return confirm('Delete this participant?');">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <span class="pagination-info">Lorem ipsum</span>
                    <div>
                        <button class="pagination-btn" onclick="previousPage()" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                            ◀ Previous
                        </button>
                        <button class="pagination-btn" onclick="nextPage()" <?php echo $page >= $total_pages ? 'disabled' : ''; ?> style="margin-left: 10px;">
                            Next ▶
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previousPage() {
            const currentPage = <?php echo $page; ?>;
            if (currentPage > 1) {
                window.location.href = '?page=' + (currentPage - 1);
            }
        }

        function nextPage() {
            const currentPage = <?php echo $page; ?>;
            const totalPages = <?php echo $total_pages; ?>;
            if (currentPage < totalPages) {
                window.location.href = '?page=' + (currentPage + 1);
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
