<?php
// Make session expire when browser closes
ini_set('session.cookie_lifetime', 0);

session_start();

// Check if this is a new session (browser was closed and reopened)
if (!isset($_SESSION['initialized'])) {
    // Clear all session data and start fresh
    session_unset();
    $_SESSION['initialized'] = true;
    $_SESSION['user_role'] = 'faculty';
    $_SESSION['user_name'] = 'Faculty User';
}

// Set default role to faculty if no session exists
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'faculty';
    $_SESSION['user_name'] = 'Faculty User';
}

require_once '../includes/db_connect.php';

$userRole = $_SESSION['user_role'] ?? 'faculty';
$userName = $_SESSION['user_name'] ?? 'Faculty User';

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
    '07:00' => 0, '07:30' => 0.5, '08:00' => 1, '09:00' => 2, '10:00' => 3, '11:00' => 4,
    '12:00' => 5, '13:00' => 6, '14:00' => 7, '15:00' => 8, '16:00' => 9,
    '17:00' => 10, '18:00' => 11, '19:00' => 12, '20:00' => 13, '21:00' => 14
);

$colors = array('schedule-blue', 'schedule-orange', 'schedule-red', 'schedule-purple', 'schedule-green');

function getSchedulePosition($startTime, $endTime, $timeSlots) {
    // Parse times - times are stored as 'HH:MM' format (e.g., '10:00', '13:30')
    // Normalize to ensure consistent parsing
    $startTime = trim($startTime);
    $endTime = trim($endTime);
    
    // Parse time strings into hours and minutes
    list($startHour, $startMin) = explode(':', $startTime);
    list($endHour, $endMin) = explode(':', $endTime);
    
    $startHour = (int)$startHour;
    $startMin = (int)$startMin;
    $endHour = (int)$endHour;
    $endMin = (int)$endMin;
    
    // Calculate total minutes from 7:00 AM (base time)
    $baseHour = 7;
    $baseMin = 0;
    
    $startTotalMinutes = ($startHour - $baseHour) * 60 + ($startMin - $baseMin);
    $endTotalMinutes = ($endHour - $baseHour) * 60 + ($endMin - $baseMin);
    
    // Ensure non-negative
    if ($startTotalMinutes < 0) $startTotalMinutes = 0;
    if ($endTotalMinutes < 0) $endTotalMinutes = 0;
    
    // Each 30-minute slot = 46.5px (matches .time-slot height exactly)
    $pxPerHalfHour = 46.5;
    $minutesPerSlot = 30;
    
    // Convert minutes to slots (each slot is 30 minutes)
    $startSlots = $startTotalMinutes / $minutesPerSlot;
    $endSlots = $endTotalMinutes / $minutesPerSlot;
    
    // Convert slots to pixels - this gives exact pixel position
    $top = $startSlots * $pxPerHalfHour;
    $height = ($endSlots - $startSlots) * $pxPerHalfHour;
    
    // Ensure minimum height of one slot
    if ($height < $pxPerHalfHour) {
        $height = $pxPerHalfHour;
    }
    
    return array('top' => round($top, 2), 'height' => round($height, 2));
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
            <div class="header-container">
                <div class="header-left">
                   <h1 class="page-title"><i class="fas fa-clipboard-list"></i> Schedule Management</h1>
                </div>
                    <div class="header-right">
                        <div class="user-widget">
                            <div class="user-pill">Hello, <?= htmlspecialchars($userName) ?></div>
                            <div class="user-subtext">
                            </div>
                        </div>
                    </div>
            </div>

            <div class="page-header">
                <div class="header-title">
                    <select id="roomSelector" class="room-dropdown" onchange="changeRoom(this.value)">
                        <option value="1811" <?= $selected_room === '1811' ? 'selected' : '' ?>>Lab 1811 Schedule</option>
                        <option value="1812" <?= $selected_room === '1812' ? 'selected' : '' ?>>Lab 1812 Schedule</option>
                    </select>
                </div>
                <!-- Show add schedule button for both technician and faculty -->
                <div style="display: flex; gap: 8px;">
                    <button class="btn btn-primary" onclick="openAddScheduleModal()">
                        <i class="fas fa-plus"></i> Add Schedule
                    </button>
                    <button class="btn btn-danger-role" onclick="confirmClearSchedules()">
                        <i class="fas fa-trash-alt"></i> Clear Schedule
                    </button>
                </div>
            </div>
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
                            <?php
                            $start = strtotime('07:00');
                            $end = strtotime('21:00');
                            
                            for ($time = $start; $time <= $end; $time += 30 * 60): // +30 mins
                                $time_12 = date('g:i A', $time);
                            ?>
                                <div class="time-slot"><?= $time_12 ?></div>
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

    <!-- Notification Container -->
    <div id="notificationContainer" class="notification-container"></div>

    <!-- Add Schedule Modal -->
    <div id="addScheduleModal" class="modal">
        <div class="modal-content add-schedule-modal">
            <div class="modal-header">
                <h3 class="modal-title">Add New Schedule</h3>
                <p class="modal-subtitle">Create a new class schedule for the selected laboratory</p>
            </div>

            <!-- Modal notification area -->
            <div id="modalNotification" class="modal-notification" style="display: none;"></div>

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
                        <label class="form-label">Allowed Absences</label>
                        <input type="number" class="form-input" name="allowedAbsences" placeholder="e.g., 3" min="0" step="1" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Grace Period (minutes)</label>
                        <input type="number" class="form-input" name="gracePeriod" placeholder="e.g., 15" min="0" step="1" required>
                    </div>
                </div>

                <div id="scheduleSlotsContainer">
                    <div class="schedule-slot" data-slot="0">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Day</label>
                                <select class="form-input" name="dayOfWeek[]" required>
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
                                <select class="form-input" name="room[]" required>
                                    <option value="1811" <?= $selected_room === '1811' ? 'selected' : '' ?>>1811</option>
                                    <option value="1812" <?= $selected_room === '1812' ? 'selected' : '' ?>>1812</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Start Time</label>
                                <input type="time" class="form-input" name="startTime[]" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">End Time</label>
                                <input type="time" class="form-input" name="endTime[]" required>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-add-slot" onclick="addScheduleSlot()">
                    <i class="fas fa-plus"></i> Add another day, room, start & end time
                </button>

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
                    <span id="detailGrace">-</span>
                </div>
                <div class="details-row">
                    <strong>Absences Allowed:</strong>
                    <span id="detailAbsences">-</span>
                </div>
            </div>

            <div id="notificationsContent" style="display: none;">
                <p>No active notifications for this schedule.</p>
            </div>

            <div id="participantsContent" style="display: none;">
                <!-- Link to participant registration page -->
                <div style="margin-bottom: 15px; text-align: right;">
                    <a href="participant-registration.php" class="btn-register-participants" style="display: inline-flex; align-items: center; gap: 8px; background: #c00; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; transition: background 0.3s;">
                        <i class="fas fa-user-plus"></i> Register Participants
                    </a>
                </div>
                <!-- Expanded participants section with scrollable table -->
                <div class="participants-body">
                    <div id="participantsTableContainer">
                        <table class="participants-table">
                            <thead>
                                <tr>
                                    <th>Student Number</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="participantsTableBody">
                                <tr><td colspan="4" class="no-participants">Loading participants...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button onclick="closeScheduleDetails()"><i class="fas fa-check"></i> Done</button>
                <!-- Edit/delete buttons available for both faculty and technicians -->
                <button onclick="editSchedule()"><i class="fas fa-edit"></i> Edit</button>
                <button onclick="deleteSchedule()"><i class="fas fa-trash"></i> Delete</button>
                <!-- Participants button always visible as it provides access to registrations -->
                <button onclick="switchTab('participants')"><i class="fas fa-users"></i> Participants</button>
            </div>
        </div>
    </div>

    <script>
        let currentScheduleId = null;
        let slotCounter = 1;

        // Show notification function
        function showNotification(message, type = 'success') {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;
            container.appendChild(notification);
            
            // Animate in
            setTimeout(() => notification.classList.add('show'), 10);
            
            // Remove after 4 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }

        // Check for session messages on page load
        window.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['success'])): ?>
                showNotification('<?= addslashes($_SESSION['success']) ?>', 'success');
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                showNotification('<?= addslashes($_SESSION['error']) ?>', 'error');
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        });

        // ESC key handler for closing modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddScheduleModal();
                closeScheduleDetails();
            }
        });

        function confirmClearSchedules() {
            const room = new URLSearchParams(window.location.search).get('room') || '1811';
            if (confirm(`Are you sure you want to clear ALL schedules for Lab ${room}? This action cannot be undone.`)) {
                window.location.href = `clear-schedules.php?room=${room}`;
            }
        }

        function addScheduleSlot() {
            const container = document.getElementById('scheduleSlotsContainer');
            const newSlot = document.createElement('div');
            newSlot.className = 'schedule-slot';
            newSlot.setAttribute('data-slot', slotCounter);
            newSlot.innerHTML = `
                <div class="slot-header">
                    <span>Schedule Slot ${slotCounter + 1}</span>
                    <button type="button" class="btn-remove-slot" onclick="removeScheduleSlot(${slotCounter})">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Day</label>
                        <select class="form-input" name="dayOfWeek[]" required>
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
                        <select class="form-input" name="room[]" required>
                            <option value="1811">1811</option>
                            <option value="1812">1812</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-input" name="startTime[]" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-input" name="endTime[]" required>
                    </div>
                </div>
            `;
            container.appendChild(newSlot);
            slotCounter++;
        }

        function removeScheduleSlot(slotId) {
            const slot = document.querySelector(`[data-slot="${slotId}"]`);
            if (slot) {
                slot.remove();
            }
        }

        function changeRoom() {
            const roomSelect = document.getElementById('roomSelect');
            const selectedRoom = roomSelect.value;
            window.location.href = '?room=' + selectedRoom;
        }

        function changeRoom(room) {
            window.location.href = '?room=' + room;
        }

        function openAddScheduleModal() {
            const modal = document.getElementById('addScheduleModal');
            modal.classList.add('active');
            // Clear previous notifications
            hideModalNotification();
        }

        function closeAddScheduleModal() {
            const modal = document.getElementById('addScheduleModal');
            modal.classList.remove('active');
            // Clear form and notifications
            document.getElementById('addScheduleForm').reset();
            hideModalNotification();
        }

        function showModalNotification(message, type = 'error') {
            const notification = document.getElementById('modalNotification');
            notification.className = `modal-notification modal-notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            notification.style.display = 'block';
        }

        function hideModalNotification() {
            const notification = document.getElementById('modalNotification');
            notification.style.display = 'none';
        }

        // Handle form submission with AJAX
        document.getElementById('addScheduleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            hideModalNotification();

            const formData = new FormData(this);

            fetch('add-schedule.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddScheduleModal();
                    showNotification(data.message, 'success');
                    // Reload page to show new schedules
                    setTimeout(() => {
                        window.location.href = '?room=' + (data.room || '1811');
                    }, 1000);
                } else {
                    // Show error in modal
                    showModalNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showModalNotification('An error occurred. Please try again.', 'error');
            });
        });

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
                        document.getElementById('detailGrace').textContent = `${schedule.grace_period || 0} minutes`;
                        document.getElementById('detailAbsences').textContent = `${schedule.allowed_absences || 0}`;

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
                                <td>${participant.email || '-'}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-small btn-edit" onclick="window.location.href='participant-registration.php'">Edit</button>
                                        <button class="btn-small btn-delete" onclick="deleteParticipant(${participant.id})">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" class="no-participants">No participants registered. <a href="participant-registration.php" style="color: #c00; text-decoration: underline;">Register participants here</a></td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error loading participants:', error);
                    const tbody = document.getElementById('participantsTableBody');
                    tbody.innerHTML = '<tr><td colspan="4" class="no-participants">Error loading participants. Please try again.</td></tr>';
                });
        }

        function deleteParticipant(participantId) {
            if (confirm('Are you sure you want to delete this participant?')) {
                // Redirect to participant registration page with delete action
                window.location.href = `participant-registration.php?delete=${participantId}`;
            }
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
