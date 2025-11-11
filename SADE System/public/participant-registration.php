<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_role'])) {
    header('Location: signin.php');
    exit;
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch participants
$result = $conn->query("SELECT 
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
    <title>SADE - Participant Registration</title>
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

        /* Section Styling */
        .registration-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        /* Collective Registration Section */
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

        /* Individual Registration Form */
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

        /* Table Styling */
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

        /* Pagination */
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
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <?php include '../includes/sidebar.php'; ?>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <div class="registration-container">
                <!-- Header with title and role selector -->
                <div class="registration-header">
                    <div class="registration-title">
                        <div class="registration-logo">◀</div>
                        Participant Registration
                    </div>
                    <button class="role-selector">Technician▼</button>
                </div>

                <!-- Collective Student Registration Section -->
                <div class="registration-section">
                    <div class="section-title">Collective Student Registration</div>
                    <div class="collective-buttons">
                        <button class="btn-csv-download">
                            <i class="fas fa-download"></i> Download CSV Template
                        </button>
                        <button class="btn-upload">
                            <i class="fas fa-upload"></i> Upload File
                        </button>
                    </div>
                </div>

                <!-- Individual Student Registration Section -->
                <div class="registration-section">
                    <div class="section-title">Individual Student Registration</div>
                    <form class="registration-form" method="POST">
                        <div class="form-group">
                            <label class="form-label">ID Number</label>
                            <input type="text" class="form-input" name="id_number" placeholder="2020-123456" required>
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

                <!-- Participants Data Table -->
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

                <!-- Pagination Controls -->
                <div class="pagination">
                    <span class="pagination-info">Lorem ipsum</span>
                    <div>
                        <button class="pagination-btn" onclick="previousPage()" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                            ◀ Previous
                        </button>
                        <button class="pagination-btn" onclick="nextPage()" <?php echo $page >= $total_pages ? 'disabled' : ''; ?>" style="margin-left: 10px;">
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
