<?php
session_start();
require_once '../includes/db_connect.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get filter parameters
$filterLab = isset($_GET['lab']) ? $_GET['lab'] : '';
$filterType = isset($_GET['type']) ? $_GET['type'] : '';
$filterDateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filterDateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause based on filters
$whereConditions = array();
if (!empty($filterLab)) {
    $whereConditions[] = "lab_id = '" . $conn->real_escape_string($filterLab) . "'";
}
if (!empty($filterType)) {
    $whereConditions[] = "action = '" . $conn->real_escape_string($filterType) . "'";
}
if (!empty($filterDateFrom)) {
    $whereConditions[] = "DATE(timestamp) >= '" . $conn->real_escape_string($filterDateFrom) . "'";
}
if (!empty($filterDateTo)) {
    $whereConditions[] = "DATE(timestamp) <= '" . $conn->real_escape_string($filterDateTo) . "'";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Fetch filtered logs
$result = $conn->query("
    SELECT 
        id,
        lab_id as laboratory,
        action as entry_type,
        DATE_FORMAT(CONVERT_TZ(timestamp, '+00:00', '+08:00'), '%m-%d-%y') AS date,
        DATE_FORMAT(CONVERT_TZ(timestamp, '+00:00', '+08:00'), '%h:%i %p') AS time,
        user_name,
        method
    FROM access_logs
    $whereClause
    ORDER BY timestamp DESC
    LIMIT $limit OFFSET $offset
");


$logs = $result->fetch_all(MYSQLI_ASSOC);

// Get total count for pagination with filters
$count_result = $conn->query("SELECT COUNT(*) as total FROM access_logs $whereClause");
$count_row = $count_result->fetch_assoc();
$total_logs = $count_row['total'];
$total_pages = ceil($total_logs / $limit);

// Get unique labs for filter dropdown
$labs_result = $conn->query("SELECT DISTINCT lab_id FROM access_logs ORDER BY lab_id");
$labs = $labs_result->fetch_all(MYSQLI_ASSOC);

// Get unique action types for filter dropdown
$types_result = $conn->query("SELECT DISTINCT action FROM access_logs ORDER BY action");
$types = $types_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADE - Logs</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .filter-section {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .filter-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 500;
            color: #555;
            margin-bottom: 6px;
        }

        .filter-group input,
        .filter-group select {
            padding: 9px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 13px;
            background: white;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #c00;
            box-shadow: 0 0 0 2px rgba(204, 0, 0, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 15px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .btn-filter {
            background: #c00;
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn-filter:hover {
            background: #a00;
        }

        .btn-clear {
            background: #e0e0e0;
            color: #333;
            border: none;
            padding: 9px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-clear:hover {
            background: #d0d0d0;
        }

        .filter-active {
            display: inline-block;
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 13px;
            color: #856404;
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
            <div class="header">
                <h1 class="page-title">Logs</h1>
                <div class="user-profile">
                    <button class="add-btn" onclick="toggleFilterPanel()">🔍 Filter Logs</button>
                </div>
            </div>

            <!-- Added filter panel -->
            <div id="filterPanel" class="filter-section" style="display: none;">
                <div class="filter-title">
                    <i class="fas fa-filter"></i> Filter Logs
                </div>
                
                <form method="GET" id="filterForm">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="filterLab">Laboratory</label>
                            <select name="lab" id="filterLab">
                                <option value="">All Laboratories</option>
                                <?php foreach ($labs as $lab): ?>
                                    <option value="<?= htmlspecialchars($lab['lab_id']) ?>" <?= $filterLab === $lab['lab_id'] ? 'selected' : '' ?>>
                                        Lab <?= htmlspecialchars($lab['lab_id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filterType">Entry Type</label>
                            <select name="type" id="filterType">
                                <option value="">All Types</option>
                                <?php foreach ($types as $t): ?>
                                    <option value="<?= htmlspecialchars($t['action']) ?>" <?= $filterType === $t['action'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['action']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="dateFrom">Date From</label>
                            <input type="date" name="date_from" id="dateFrom" value="<?= htmlspecialchars($filterDateFrom) ?>">
                        </div>

                        <div class="filter-group">
                            <label for="dateTo">Date To</label>
                            <input type="date" name="date_to" id="dateTo" value="<?= htmlspecialchars($filterDateTo) ?>">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">Apply Filters</button>
                        <a href="logs.php" class="btn-clear" style="text-decoration: none; display: inline-block;">Clear Filters</a>
                    </div>
                </form>
            </div>

            <!-- Show active filters indicator -->
            <?php if (!empty($filterLab) || !empty($filterType) || !empty($filterDateFrom) || !empty($filterDateTo)): ?>
                <div class="filter-active">
                    <strong>Active Filters:</strong>
                    <?php if (!empty($filterLab)): ?>Lab: <?= htmlspecialchars($filterLab) ?> <?php endif; ?>
                    <?php if (!empty($filterType)): ?>| Type: <?= htmlspecialchars($filterType) ?> <?php endif; ?>
                    <?php if (!empty($filterDateFrom)): ?>| From: <?= htmlspecialchars($filterDateFrom) ?> <?php endif; ?>
                    <?php if (!empty($filterDateTo)): ?>| To: <?= htmlspecialchars($filterDateTo) ?> <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="table-wrapper">
                <div class="pagination-controls">
                    <button class="pagination-btn" onclick="previousPage()" <?php echo $page <= 1 ? 'disabled' : ''; ?>>◀ Previous</button>
                    <span class="pagination-info">Page <?php echo $page; ?> of <?php echo $total_pages ?: 1; ?> (Total: <?php echo $total_logs; ?> entries)</span>
                    <button class="pagination-btn" onclick="nextPage()" <?php echo $page >= $total_pages ? 'disabled' : ''; ?>>Next ▶</button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Laboratory</th>
                            <th>Entry Type</th>
                            <th>User</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (!empty($logs)) {
                                foreach ($logs as $log) {
                                    echo "
                                        <tr>
                                            <td>" . htmlspecialchars($log['laboratory']) . "</td>
                                            <td>" . htmlspecialchars($log['entry_type']) . "</td>
                                            <td>" . htmlspecialchars($log['user_name'] ?? '-') . "</td>
                                            <td>" . htmlspecialchars($log['method'] ?? '-') . "</td>
                                            <td>" . htmlspecialchars($log['date']) . "</td>
                                            <td>" . htmlspecialchars($log['time']) . "</td>
                                        </tr>
                                    ";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center; color:#999;'>No logs found matching your filters</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="logs-actions">
                <button class="btn-export" onclick="exportLogsHistory()">
                    <i class="fas fa-download"></i> Export Logs
                </button>
                <button class="btn-delete" onclick="deleteLogsHistory()">
                    <i class="fas fa-trash"></i> Delete Logs
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleFilterPanel() {
            const panel = document.getElementById('filterPanel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }

        function previousPage() {
            const currentPage = <?php echo $page; ?>;
            if (currentPage > 1) {
                const params = new URLSearchParams(window.location.search);
                params.set('page', currentPage - 1);
                window.location.href = '?' + params.toString();
            }
        }

        function nextPage() {
            const currentPage = <?php echo $page; ?>;
            const totalPages = <?php echo $total_pages ?: 1; ?>;
            if (currentPage < totalPages) {
                const params = new URLSearchParams(window.location.search);
                params.set('page', currentPage + 1);
                window.location.href = '?' + params.toString();
            }
        }

        function exportLogsHistory() {
            // Get current filter parameters
            const params = new URLSearchParams(window.location.search);
            const exportUrl = 'export-logs.php?' + params.toString();
            window.location.href = exportUrl;
        }

        function deleteLogsHistory() {
            if (confirm('Are you sure you want to delete these logs? This action cannot be undone.')) {
                const params = new URLSearchParams(window.location.search);
                const deleteUrl = 'delete-logs.php?' + params.toString();
                window.location.href = deleteUrl;
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
