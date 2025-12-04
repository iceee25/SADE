<?php
session_start();
require_once '../includes/db_connect.php';

// Set default role to faculty if no session exists
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'faculty';
    $_SESSION['user_name'] = 'Faculty User';
}

$userRole = $_SESSION['user_role'] ?? 'faculty';

// Get selected room
$selected_room = $_GET['room'] ?? '1811';

$query = "SELECT id, course_code, course_name, instructor, day, room, start_time, end_time FROM schedules WHERE room = ? ORDER BY day, start_time";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selected_room);
$stmt->execute();
$result = $stmt->get_result();
$schedules = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique lab rooms from schedules
$labs_query = "SELECT DISTINCT room FROM schedules ORDER BY room";
$labs_result = $conn->query($labs_query);
$labs = $labs_result->fetch_all(MYSQLI_ASSOC);

$timeSlots = array(
    '07:00' => 0, '07:30' => 0.5, '08:00' => 1, '08:30' => 1.5,
    '09:00' => 2, '09:30' => 2.5, '10:00' => 3, '10:30' => 3.5, '11:00' => 4, '11:30' => 4.5,
    '12:00' => 5, '12:30' => 5.5, '13:00' => 6, '13:30' => 6.5, '14:00' => 7, '14:30' => 7.5,
    '15:00' => 8, '15:30' => 8.5, '16:00' => 9, '16:30' => 9.5, '17:00' => 10, '17:30' => 10.5,
    '18:00' => 11, '18:30' => 11.5, '19:00' => 12, '19:30' => 12.5, '20:00' => 13
);

$colors = array('schedule-blue', 'schedule-orange', 'schedule-red', 'schedule-purple', 'schedule-green');

function getSchedulePosition($startTime, $endTime, $timeSlots) {
    $startKey = date('H:i', strtotime($startTime));
    $endKey = date('H:i', strtotime($endTime));
    
    $startPos = isset($timeSlots[$startKey]) ? $timeSlots[$startKey] : 0;
    $endPos = isset($timeSlots[$endKey]) ? $timeSlots[$endKey] : 1;
    $height = max(($endPos - $startPos) * 50, 50);
    $top = $startPos * 50;
    
    return array('top' => $top, 'height' => $height);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADE</title>
    <link rel="icon" type="image/png" href="../assets/images/sade-logo.png">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/schedule-modal.css" rel="stylesheet">
    <link href="../assets/css/schedule-management.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <?php include '../includes/sidebar.php'; ?>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Added user dropdown to header -->
            <div class="header-top">
                <div class="page-header">
                    <h1 class="page-title">Schedule Management</h1>
                </div>
                <div class="header-right">
                    <?php include '../includes/user-dropdown.php'; ?>
                </div>
            </div>

            <div class="page-header">
                <div class="header-title">
                    <h1>Lab <?= htmlspecialchars($selected_room) ?> Schedule</h1>
                </div>
                <!-- Show add schedule button for both technician and faculty -->
                <button class="btn btn-primary" onclick="openAddScheduleModal()" style="margin-right: 8px;">
                    <i class="fas fa-plus"></i> Add Schedule
                </button>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="schedule-container">
                <div class="schedule-header">
                    <h2 class="schedule-title">Viewing: Lab <?= htmlspecialchars($selected_room) ?> Schedule</h2>
                </div>

                <!-- Calendar with fixed header and scrollable body -->
                <div class="calendar-wrapper">
                    <!-- Fixed Header Row -->
                    <div class="calendar-header-row">
                        <div class="calendar-header-cell"></div>
                        <div class="calendar-header-cell">Monday</div>
                        <div class="calendar-header-cell">Tuesday</div>
                        <div class="calendar-header-cell">Wednesday</div>
                        <div class="calendar-header-cell">Thursday</div>
                        <div class="calendar-header-cell">Friday</div>
                        <div class="calendar-header-cell">Saturday</div>
                    </div>

                    <!-- Calendar Body with Time Slots and Schedules -->
                    <div class="calendar-body">
                        <!-- Time Column -->
                        <div class="time-column">
                            <?php for ($h = 7; $h <= 20; $h++): ?>
                                <?php $time_12 = date('g:i A', strtotime("$h:00")); ?>
                                <div class="time-slot"><?= $time_12 ?></div>
                                <div class="time-slot">
                                    <?= date('g:i A', strtotime("$h:30")) ?>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <!-- Day Columns -->
                        <?php
                        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
                        foreach ($days as $dayIndex => $day):
                        ?>
                        <div class="day-column">
                            <?php
                            $dayColorIndex = 0;
                            foreach ($schedules as $schedule):
                                if (strtolower($schedule['day']) === $day):
                                    $position = getSchedulePosition($schedule['start_time'], $schedule['end_time'], $timeSlots);
                                    $colorClass = $colors[$dayColorIndex % count($colors)];
                                    $dayColorIndex++;
                                    $startTimeFormatted = date('g:i A', strtotime($schedule['start_time']));
                                    $endTimeFormatted = date('g:i A', strtotime($schedule['end_time']));
                                    
                                    // Count students for this schedule
                                    $count_query = "SELECT COUNT(*) as student_count FROM users WHERE user_type = 'STUDENT' AND is_active = 1";
                                    $count_result = $conn->query($count_query);
                                    $count_row = $count_result->fetch_assoc();
                                    $student_count = $count_row['student_count'] ?? 0;
                            ?>
                            <div class="schedule-block <?= $colorClass ?>" 
                                 style="top: <?= $position['top'] ?>px; height: <?= $position['height'] ?>px;"
                                 onclick="viewScheduleDetails(<?= $schedule['id'] ?>)"
                                 title="<?= htmlspecialchars($schedule['course_name']) ?>">
                                <div class="schedule-time"><?= $startTimeFormatted ?> - <?= $endTimeFormatted ?></div>
                                <div class="schedule-code">[<?= htmlspecialchars($schedule['course_code']) ?>]</div>
                                <div class="schedule-instructor"><?= htmlspecialchars($schedule['instructor']) ?></div>
                                <div class="schedule-students"><?= $student_count ?> Students</div>
                            </div>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="system-status">
        <div class="status-indicator"></div>
        <span>System Status: Online</span>
    </div>

    <!-- Add Schedule Modal -->
    <div id="addScheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Schedule</h3>
                <p class="modal-subtitle">Create a new class schedule for the selected laboratory</p>
            </div>

            <form id="addScheduleForm" method="POST" action="add-schedule.php">
                <div class="form-group">
                    <label class="form-label">Course Code</label>
                    <input type="text" class="form-input" name="courseCode" placeholder="e.g., CS101" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Course Name</label>
                    <input type="text" class="form-input" name="courseName" placeholder="e.g., Data Structures" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Instructor</label>
                    <input type="text" class="form-input" name="instructor" placeholder="e.g., Prof. Smith" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Day</label>
                        <select class="form-input" name="dayOfWeek" required>
                            <option value="">Select Day</option>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday">Friday</option>
                            <option value="saturday">Saturday</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Room</label>
                        <input type="text" class="form-input" name="room" value="<?= htmlspecialchars($selected_room) ?>" placeholder="e.g., 1811" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-input" name="startTime" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-input" name="endTime" required>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddScheduleModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedule Details Modal -->
    <div id="scheduleDetailsModal" class="modal">
        <div class="modal-content">
            <div class="tab-bar">
                <button class="tab-button active" onclick="switchTab('details')">Schedule Details</button>
                <button class="tab-button" onclick="switchTab('notifications')">Notifications</button>
                <!-- Add participants tab for accessing registrations -->
                <button class="tab-button" onclick="switchTab('participants')">Participants</button>
            </div>

            <div id="detailsContent" class="details-body">
                <div class="details-row">
                    <strong>Lab:</strong>
                    <span id="detailLab">-</span>
                </div>
                <div class="details-row">
                    <strong>Course:</strong>
                    <span id="detailCourse">-</span>
                </div>
                <div class="details-row">
                    <strong>Course Instructor:</strong>
                    <span id="detailInstructor">-</span>
                </div>
                <div class="details-row">
                    <strong>Date:</strong>
                    <span id="detailDay">-</span>
                </div>
                <div class="details-row">
                    <strong>Time:</strong>
                    <span id="detailTime">-</span>
                </div>
                <div class="details-row">
                    <strong>Duration:</strong>
                    <span id="detailDuration">-</span>
                </div>
                <div class="details-row">
                    <strong>Grace Period:</strong>
                    <span id="detailGrace">15 minutes</span>
                </div>
                <div class="details-row">
                    <strong>Absences Allowed:</strong>
                    <span id="detailAbsences">6 hours</span>
                </div>
            </div>

            <div id="notificationsContent" style="display: none;">
                <p>No active notifications for this schedule.</p>
            </div>

            <div id="participantsContent" style="display: none;">
                <!-- Expanded participants section with scrollable table -->
                <div class="participants-body">
                    <div id="participantsTableContainer">
                        <table class="participants-table">
                            <thead>
                                <tr>
                                    <th>Student Number</th>
                                    <th>Full Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="participantsTableBody">
                                <tr><td colspan="3" class="no-participants">Loading participants...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button onclick="closeScheduleDetails()"><i class="fas fa-check"></i> Done</button>
                <!-- Show edit/delete buttons only for technicians -->
                <?php if ($userRole === 'technician'): ?>
                    <button onclick="editSchedule()"><i class="fas fa-edit"></i> Edit</button>
                    <button onclick="deleteSchedule()"><i class="fas fa-trash"></i> Archive</button>
                <?php endif; ?>
                <!-- Participants button always visible as it provides access to registrations -->
                <button onclick="switchTab('participants')"><i class="fas fa-users"></i> Participants</button>
            </div>
        </div>
    </div>

    <script>
        let currentScheduleId = null;

        function changeRoom() {
            const roomSelect = document.getElementById('roomSelect');
            const selectedRoom = roomSelect.value;
            window.location.href = '?room=' + selectedRoom;
        }

        function openAddScheduleModal() {
            const modal = document.getElementById('addScheduleModal');
            modal.classList.add('active');
        }

        function closeAddScheduleModal() {
            const modal = document.getElementById('addScheduleModal');
            modal.classList.remove('active');
        }

        function viewScheduleDetails(scheduleId) {
            currentScheduleId = scheduleId;
            
            fetch(`get-schedule-details.php?id=${scheduleId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const schedule = data.schedule;
                        const startTime = new Date(`2000-01-01 ${schedule.start_time}`);
                        const endTime = new Date(`2000-01-01 ${schedule.end_time}`);
                        const durationMs = endTime - startTime;
                        const durationHours = Math.floor(durationMs / 3600000);
                        const durationMins = (durationMs % 3600000) / 60000;

                        document.getElementById('detailLab').textContent = `Lab ${schedule.room}`;
                        document.getElementById('detailCourse').textContent = schedule.course_code;
                        document.getElementById('detailInstructor').textContent = schedule.instructor;
                        document.getElementById('detailDay').textContent = schedule.day;
                        document.getElementById('detailTime').textContent = `${schedule.start_time} to ${schedule.end_time}`;
                        document.getElementById('detailDuration').textContent = `${durationHours} hour${durationHours !== 1 ? 's' : ''} ${durationMins > 0 ? durationMins + ' minutes' : ''}`;

                        const modal = document.getElementById('scheduleDetailsModal');
                        modal.classList.add('active');
                    }
                })
                .catch(error => console.error('Error fetching schedule details:', error));
        }

        function closeScheduleDetails() {
            const modal = document.getElementById('scheduleDetailsModal');
            modal.classList.remove('active');
        }

        function switchTab(tabName) {
            const detailsContent = document.getElementById('detailsContent');
            const notificationsContent = document.getElementById('notificationsContent');
            const participantsContent = document.getElementById('participantsContent');
            const buttons = document.querySelectorAll('.tab-button');

            buttons.forEach(btn => btn.classList.remove('active'));

            if (tabName === 'details') {
                detailsContent.style.display = 'grid';
                notificationsContent.style.display = 'none';
                participantsContent.style.display = 'none';
                buttons[0].classList.add('active');
            } else if (tabName === 'notifications') {
                detailsContent.style.display = 'none';
                notificationsContent.style.display = 'block';
                participantsContent.style.display = 'none';
                buttons[1].classList.add('active');
            } else if (tabName === 'participants') {
                detailsContent.style.display = 'none';
                notificationsContent.style.display = 'none';
                participantsContent.style.display = 'block';
                buttons[2].classList.add('active');
                loadParticipants(currentScheduleId);
            }
        }

        function loadParticipants(scheduleId) {
            fetch(`get-schedule-participants.php?id=${scheduleId}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('participantsTableBody');
                    if (data.success && data.participants.length > 0) {
                        tbody.innerHTML = data.participants.map(participant => `
                            <tr>
                                <td>${participant.id_number}</td>
                                <td>${participant.full_name}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-small btn-edit">Edit</button>
                                        <button class="btn-small btn-delete">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="3" class="no-participants">No participants registered.</td></tr>';
                    }
                })
                .catch(error => console.error('Error loading participants:', error));
        }

        function editSchedule() {
            if (currentScheduleId) {
                window.location.href = `edit-schedule.php?id=${currentScheduleId}`;
            }
        }

        function deleteSchedule() {
            if (confirm('Are you sure you want to archive this schedule?')) {
                window.location.href = `delete-schedule.php?id=${currentScheduleId}`;
            }
        }

        document.addEventListener('click', function(e) {
            const detailsModal = document.getElementById('scheduleDetailsModal');
            const addModal = document.getElementById('addScheduleModal');
            if (e.target === detailsModal) {
                detailsModal.classList.remove('active');
            }
            if (e.target === addModal) {
                addModal.classList.remove('active');
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
