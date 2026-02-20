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

// Handle delete from GET parameter (for navigation from schedule page)
if (isset($_GET['delete'])) {
    $student_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM participants WHERE id = ?");
    $stmt->bind_param('i', $student_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Participant deleted successfully';
    }
    $stmt->close();
    header('Location: participant-registration.php');
    exit;
}

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
                    $_SESSION['success_message'] = 'Participant added successfully';
                    header('Location: participant-registration.php');
                    exit;
                }
            }
        } elseif ($_POST['action'] === 'delete_student' && isset($_POST['student_id'])) {
            $student_id = (int)$_POST['student_id'];
            $stmt = $conn->prepare("DELETE FROM participants WHERE id = ?");
            $stmt->bind_param('i', $student_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Participant deleted successfully';
            }
            $stmt->close();
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
    <link href="../assets/css/participant-registration.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message" style="background: #d4edda; border-left: 4px solid #28a745; color: #155724; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <span><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
                    <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; cursor: pointer; font-weight: bold; color: #155724; font-size: 18px;">×</button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
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
