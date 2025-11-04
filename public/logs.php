<?php
session_start();
require_once '../includes/db_connect.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$result = $conn->query("
    SELECT 
        id,
        lab_id as laboratory,
        action as entry_type,
        DATE_FORMAT(timestamp, '%m-%d-%y') as date,
        DATE_FORMAT(timestamp, '%h:%i %p') as time
    FROM access_logs
    ORDER BY timestamp DESC
    LIMIT $limit OFFSET $offset
");

$logs = $result->fetch_all(MYSQLI_ASSOC);

// Get total count for pagination
$count_result = $conn->query("SELECT COUNT(*) as total FROM access_logs");
$count_row = $count_result->fetch_assoc();
$total_logs = $count_row['total'];
$total_pages = ceil($total_logs / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADE - Logs</title>
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
                <h1 class="page-title">Logs</h1>
                <div class="user-profile">
                    <button class="add-btn" onclick="openExportModal()">Technicians ▼</button>
                </div>
            </div>

            <div class="table-wrapper">
                <div class="pagination-controls">
                    <button class="pagination-btn" onclick="previousPage()" <?php echo $page <= 1 ? 'disabled' : ''; ?>>◀ Previous</button>
                    <span class="pagination-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                    <button class="pagination-btn" onclick="nextPage()" <?php echo $page >= $total_pages ? 'disabled' : ''; ?>>Next ▶</button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Laboratory</th>
                            <th>Entry Type</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($logs as $log) {
                                echo "
                                    <tr>
                                        <td>{$log['laboratory']}</td>
                                        <td>{$log['entry_type']}</td>
                                        <td>{$log['date']}</td>
                                        <td>{$log['time']}</td>
                                    </tr>
                                ";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="logs-actions">
                <button class="btn-export" onclick="exportLogsHistory()">
                    <i class="fas fa-download"></i> Export Logs History
                </button>
                <button class="btn-delete" onclick="deleteLogsHistory()">
                    <i class="fas fa-trash"></i> Delete Logs History
                </button>
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

        function exportLogsHistory() {
            alert('Exporting logs history...');
        }

        function deleteLogsHistory() {
            if (confirm('Are you sure you want to delete all logs history?')) {
                alert('Logs history deleted');
            }
        }

        function openExportModal() {
            alert('Technician filter modal would open here');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
