<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADE - Schedule Management</title>
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/schedule-modal.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <?php include '../includes/sidebar.php'; ?>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Schedule Management</h1>
                <div class="header">
                    <div class="user-profile">
                        <select id="roomSelect" class="add-btn room-select" onchange="changeRoom()">
                            <option value="1811">Lab 1811</option>
                            <option value="1812">Lab 1812</option>
                            <option value="1815">Lab 1815</option>
                            <option value="1816">Lab 1816</option>
                            <option value="1817">Lab 1817</option>
                        </select>
                        <button class="add-btn" onclick="openAddScheduleModal()">+ Add Schedule</button>
                    </div>
                </div>
            </div>

            <!-- Schedule Container -->
            <div class="schedule-container">
                <div class="schedule-header">
                    <h2 class="schedule-title" id="scheduleTitle">Viewing: Lab 1811 Schedule</h2>
                </div>

                <div class="calendar-grid">
                    <!-- Time Column -->
                    <div class="time-column">
                        <div class="day-header" style="background: white; border: none;"></div>
                        <div class="time-slot">7:00 AM</div>
                        <div class="time-slot">7:30 AM</div>
                        <div class="time-slot">8:00 AM</div>
                        <div class="time-slot">8:30 AM</div>
                        <div class="time-slot">9:00 AM</div>
                        <div class="time-slot">9:30 AM</div>
                        <div class="time-slot">10:00 AM</div>
                        <div class="time-slot">10:30 AM</div>
                        <div class="time-slot">11:00 AM</div>
                        <div class="time-slot">11:30 PM</div>
                        <div class="time-slot">12:00 PM</div>
                        <div class="time-slot">12:30 PM</div>
                        <div class="time-slot">1:00 PM</div>
                        <div class="time-slot">2:00 PM</div>
                    </div>

                    <!-- Monday -->
                    <div class="day-column">
                        <div class="day-header">Monday</div>
                        <div class="schedule-block schedule-blue" style="top: 80px; height: 120px;" onclick="viewScheduleDetails('ITCAPSTONE1')">
                            <div class="course-code">ITCAPSTONE1</div>
                            <div class="instructor">Prof. Cruz</div>
                            <div class="enrollment">(20/30)</div>
                        </div>
                    </div>

                    <!-- Tuesday -->
                    <div class="day-column">
                        <div class="day-header">Tuesday</div>
                        <div class="schedule-block schedule-red" style="top: 80px; height: 160px;" onclick="viewScheduleDetails('ICYBERSEC1')">
                            <div class="course-code">ICYBERSEC1</div>
                            <div class="instructor">Prof. Green</div>
                            <div class="enrollment">(30/32)</div>
                        </div>
                    </div>

                    <!-- Wednesday -->
                    <div class="day-column">
                        <div class="day-header">Wednesday</div>
                        <div class="schedule-block schedule-orange" style="top: 280px; height: 120px;" onclick="viewScheduleDetails('ALGORITHMS1')">
                            <div class="course-code">ALGORITHMS1</div>
                            <div class="instructor">Prof. Lee</div>
                            <div class="enrollment">(38/45)</div>
                        </div>
                    </div>

                    <!-- Thursday -->
                    <div class="day-column">
                        <div class="day-header">Thursday</div>
                        <div class="schedule-block schedule-pink" style="top: 200px; height: 120px;" onclick="viewScheduleDetails('QUANTITATIVE')">
                            <div class="course-code">QUANTITATIVE MODELS1</div>
                            <div class="instructor">Prof. Johnson</div>
                            <div class="enrollment">(32/32)</div>
                        </div>
                    </div>

                    <!-- Friday -->
                    <div class="day-column">
                        <div class="day-header">Friday</div>
                        <div class="schedule-block schedule-pink" style="top: 80px; height: 200px;" onclick="viewScheduleDetails('CS1')">
                            <div class="course-code">CS1</div>
                            <div class="instructor">Prof. White</div>
                            <div class="enrollment">(32/32)</div>
                        </div>
                    </div>

                    <!-- Saturday -->
                    <div class="day-column">
                        <div class="day-header">Saturday</div>
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
            <form id="addScheduleForm">
                <div class="form-group">
                    <label class="form-label">Course Code</label>
                    <input type="text" class="form-input" id="courseCode" placeholder="e.g., CS101" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Course Name</label>
                    <input type="text" class="form-input" id="courseName" placeholder="e.g., Data Structures" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Instructor</label>
                    <input type="text" class="form-input" id="instructor" placeholder="e.g., Prof. Smith" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Day</label>
                        <select class="form-input" id="dayOfWeek" required>
                            <option value="">Select Day</option>
                            <option>Monday</option><option>Tuesday</option>
                            <option>Wednesday</option><option>Thursday</option>
                            <option>Friday</option><option>Saturday</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Capacity</label>
                        <input type="number" class="form-input" id="maxCapacity" placeholder="30" min="1" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-input" id="startTime" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-input" id="endTime" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddScheduleModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>

   <!-- 📘 Pretty Schedule Details Modal -->
    <div id="scheduleDetailsModal" class="modal">
      <div class="modal-content">
        <div class="tab-bar">
          <button id="detailsTab" class="tab-button active">Schedule Details</button>
          <button id="notificationsTab" class="tab-button">Notifications</button>
        </div>

        <div id="scheduleDetailsContent" class="details-body">
          <div class="details-row"><strong>Lab:</strong> <span id="labDetails">Lab 1811</span></div>
          <div class="details-row"><strong>Course:</strong> <span id="courseDetails">IT2628</span></div>
          <div class="details-row"><strong>Course Instructor:</strong> <span id="instructorDetails">Arthur Ollanda</span></div>
          <div class="details-row"><strong>Day:</strong> <span id="dayDetails">Tuesday</span></div>
          <div class="details-row"><strong>Time:</strong> <span id="timeDetails">7:00 AM to 10:00 AM</span></div>
          <div class="details-row"><strong>Duration:</strong> <span id="durationDetails">2 hours</span></div>
          <div class="details-row"><strong>Grace Period:</strong> <span id="graceDetails">15 minutes</span></div>
          <div class="details-row"><strong>Absences Allowed:</strong> <span id="absenceDetails">6 hours</span></div>
        </div>

        <div id="notificationsContent" style="display: none;">
          <p style="text-align:center; color:#888;">No notifications available for this schedule.</p>
        </div>

        <div class="modal-footer">
          <button onclick="closeScheduleDetailsModal()">Done</button>
          <button onclick="openDeleteModal()"><i class="fas fa-trash"></i></button>
          <button onclick="openEditScheduleModal()"><i class="fas fa-edit"></i></button>
          <button><i class="fas fa-users"></i></button>
        </div>
      </div>
    </div>

    <!-- 🗑️ Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal">
      <div class="modal-content delete-modal">
        <h3>Delete Schedule</h3>
        <p>Are you sure you want to delete this schedule?</p>
        <div class="delete-buttons">
          <button class="btn-yes" onclick="confirmDelete()">Yes</button>
          <button class="btn-no" onclick="closeDeleteModal()">No</button>
        </div>
      </div>
    </div>

    <!-- Edit Schedule Modal -->
<div id="editScheduleModal" class="modal">
  <div class="modal-content">
    <h2>Edit Schedule</h2>

    <form id="editScheduleForm">
      <div class="form-group">
        <label for="editCourse">Course</label>
        <input type="text" id="editCourse" name="course" required>
      </div>

      <div class="form-group">
        <label for="editInstructor">Instructor</label>
        <input type="text" id="editInstructor" name="instructor" required>
      </div>

      <div class="form-group">
        <label>Laboratory Room</label>
        <div class="radio-group">
          <label><input type="radio" name="lab" value="Lab 1"> 1811</label>
          <label><input type="radio" name="lab" value="Lab 2"> 1812</label>
        </div>
      </div>

      <div class="form-group time-group">
        <div>
          <label for="editStartTime">Start Time</label>
          <input type="time" id="editStartTime" name="start_time" required>
        </div>
        <div>
          <label for="editEndTime">End Time</label>
          <input type="time" id="editEndTime" name="end_time" required>
        </div>
      </div>

      <div class="form-group">
        <label for="editDay">Day</label>
        <select id="editDay" name="day" required>
          <option value="">Select Day</option>
          <option>Monday</option>
          <option>Tuesday</option>
          <option>Wednesday</option>
          <option>Thursday</option>
          <option>Friday</option>
        </select>
      </div>

      <div class="form-group">
        <label for="editDuration">Duration (minutes)</label>
        <input type="number" id="editDuration" name="duration" min="0" required>
      </div>

      <div class="form-group">
        <label for="editAbsences">Allowed Absences</label>
        <input type="number" id="editAbsences" name="absences" min="0" required>
      </div>

      <div class="form-group">
        <label for="editGrace">Grace Period (minutes)</label>
        <input type="number" id="editGrace" name="grace" min="0" required>
      </div>

      <div class="modal-buttons">
        <button type="button" class="btn cancel" onclick="closeEditScheduleModal()">Cancel</button>
        <button type="submit" class="btn save">Save Schedule</button>
      </div>
    </form>
  </div>
</div>

    <script>
      // Tab switching logic
      const detailsTab = document.getElementById('detailsTab');
      const notificationsTab = document.getElementById('notificationsTab');
      const detailsContent = document.getElementById('scheduleDetailsContent');
      const notificationsContent = document.getElementById('notificationsContent');

      detailsTab.addEventListener('click', () => {
        detailsTab.classList.add('active');
        notificationsTab.classList.remove('active');
        detailsContent.style.display = 'block';
        notificationsContent.style.display = 'none';
      });

      notificationsTab.addEventListener('click', () => {
        notificationsTab.classList.add('active');
        detailsTab.classList.remove('active');
        detailsContent.style.display = 'none';
        notificationsContent.style.display = 'block';
      });

      // Schedule data
      const scheduleData = {
        '1811': {
          'ITCAPSTONE1': { courseCode:'ITCAPSTONE1', instructor:'Prof. Cruz', startTime:'08:00', endTime:'10:00' },
          'ICYBERSEC1': { courseCode:'ICYBERSEC1', instructor:'Prof. Green', startTime:'08:00', endTime:'12:00' },
        }
      };

      // Show schedule details modal
      function viewScheduleDetails(courseCode) {
        const currentRoom = document.getElementById('roomSelect').value;
        const schedule = scheduleData[currentRoom]?.[courseCode];
        if (!schedule) return;

        document.getElementById('labDetails').textContent = 'Lab ' + currentRoom;
        document.getElementById('courseDetails').textContent = schedule.courseCode;
        document.getElementById('instructorDetails').textContent = schedule.instructor;
        document.getElementById('timeDetails').textContent = formatTime(schedule.startTime) + ' to ' + formatTime(schedule.endTime);
        document.getElementById('durationDetails').textContent = calculateDuration(schedule.startTime, schedule.endTime);

        document.getElementById('scheduleDetailsModal').classList.add('active');
      }

      function closeScheduleDetailsModal() {
        document.getElementById('scheduleDetailsModal').classList.remove('active');
      }

      // Add Schedule Modal Control
      function openAddScheduleModal() {
        document.getElementById('addScheduleModal').classList.add('active');
      }

      function closeAddScheduleModal() {
        document.getElementById('addScheduleModal').classList.remove('active');
      }

      // Delete Confirmation Modal Control
      function openDeleteModal() {
        document.getElementById('deleteConfirmModal').classList.add('active');
      }

      function closeDeleteModal() {
        document.getElementById('deleteConfirmModal').classList.remove('active');
      }

      function confirmDelete() {
        alert("Schedule deleted successfully!");
        closeDeleteModal();
        closeScheduleDetailsModal();
      }

      // Edit Schedule Modal
    function openEditScheduleModal() {
    document.getElementById('editScheduleModal').classList.add('active');
    document.getElementById('scheduleDetailsModal').classList.remove('active');
    }

    function closeEditScheduleModal() {
    document.getElementById('editScheduleModal').classList.remove('active');
    }


      // Helper functions
      function formatTime(time) {
        let [h, m] = time.split(':');
        const suffix = h >= 12 ? 'PM' : 'AM';
        h = (h % 12) || 12;
        return `${h}:${m} ${suffix}`;
      }

      function calculateDuration(start, end) {
        const [sh, sm] = start.split(':').map(Number);
        const [eh, em] = end.split(':').map(Number);
        const total = (eh * 60 + em) - (sh * 60 + sm);
        const hrs = Math.floor(total / 60);
        const mins = total % 60;
        return `${hrs} hour${hrs !== 1 ? 's' : ''}${mins ? ' ' + mins + ' minutes' : ''}`;
      }
    </script>
</body>
</html>
