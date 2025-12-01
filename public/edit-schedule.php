<?php
session_start();
require_once '../includes/db_connect.php';

$userRole = $_SESSION['user_role'] ?? 'teacher';

// Only technicians can edit schedules
if ($userRole !== 'technician') {
    header('Location: dashboard.php');
    exit();
}

$scheduleId = $_GET['id'] ?? null;

if (!$scheduleId) {
    die("Schedule ID not provided.");
}

// Fetch schedule details
$query = "SELECT * FROM schedules WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $scheduleId);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();
$stmt->close();

if (!$schedule) {
    die("Schedule not found.");
}

// Get unique lab rooms
$labs_query = "SELECT DISTINCT room FROM schedules ORDER BY room";
$labs_result = $conn->query($labs_query);
$labs = $labs_result->fetch_all(MYSQLI_ASSOC);

$days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADE - Edit Schedule</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .edit-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .edit-header {
            margin-bottom: 30px;
        }

        .edit-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #c62828;
            box-shadow: 0 0 0 3px rgba(198, 40, 40, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel {
            background: #e0e0e0;
            color: #333;
        }

        .btn-cancel:hover {
            background: #d0d0d0;
        }

        .btn-publish {
            background: #c62828;
            color: white;
        }

        .btn-publish:hover {
            background: #b71c1c;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(198, 40, 40, 0.3);
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
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
            <div class="edit-container">
                <div class="edit-header">
                    <h1><i class="fas fa-edit"></i> Edit Schedule</h1>
                    <p>Update the schedule details and publish changes</p>
                </div>

                <form id="editScheduleForm" method="POST" action="update-schedule.php">
                    <input type="hidden" name="scheduleId" value="<?= $scheduleId ?>">

                    <div class="form-group">
                        <label>Course Code</label>
                        <input type="text" name="courseCode" value="<?= htmlspecialchars($schedule['course_code']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Course Name</label>
                        <input type="text" name="courseName" value="<?= htmlspecialchars($schedule['course_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Instructor</label>
                        <input type="text" name="instructor" value="<?= htmlspecialchars($schedule['instructor']) ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Day of Week</label>
                            <select name="dayOfWeek" required>
                                <?php foreach ($days as $day): ?>
                                    <option value="<?= $day ?>" <?= strtolower($schedule['day']) === $day ? 'selected' : '' ?>>
                                        <?= ucfirst($day) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Room</label>
                            <select name="room" required>
                                <?php foreach ($labs as $lab): ?>
                                    <option value="<?= $lab['room'] ?>" <?= $schedule['room'] === $lab['room'] ? 'selected' : '' ?>>
                                        Lab <?= $lab['room'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" name="startTime" value="<?= substr($schedule['start_time'], 0, 5) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" name="endTime" value="<?= substr($schedule['end_time'], 0, 5) ?>" required>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="button" class="btn btn-cancel" onclick="window.history.back()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-publish">
                            <i class="fas fa-save"></i> Publish Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
