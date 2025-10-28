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
                        <div class="time-grid-lines">
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                        </div>
                        <!-- ITCAPSTONE1 Block -->
                        <div class="schedule-block schedule-blue" style="top: 80px; height: 120px;" onclick="viewScheduleDetails('ITCAPSTONE1')">
                            <div class="course-code">ITCAPSTONE1</div>
                            <div class="instructor">Prof. Cruz</div>
                            <div class="enrollment">(20/30)</div>
                        </div>
                    </div>

                    <!-- Tuesday -->
                    <div class="day-column">
                        <div class="day-header">Tuesday</div>
                        <div class="time-grid-lines">
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                        </div>
                        <!-- ICYBERSEC1 Block -->
                        <div class="schedule-block schedule-red" style="top: 80px; height: 160px;" onclick="viewScheduleDetails('ICYBERSEC1')">
                            <div class="course-code">ICYBERSEC1</div>
                            <div class="instructor">Prof. Green</div>
                            <div class="enrollment">(30/32)</div>
                        </div>
                    </div>

                    <!-- Wednesday -->
                    <div class="day-column">
                        <div class="day-header">Wednesday</div>
                        <div class="time-grid-lines">
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                        </div>
                        <!-- ALGORITHMS1 Block -->
                        <div class="schedule-block schedule-orange" style="top: 280px; height: 120px;" onclick="viewScheduleDetails('ALGORITHMS1')">
                            <div class="course-code">ALGORITHMS1</div>
                            <div class="instructor">Prof. Lee</div>
                            <div class="enrollment">(38/45)</div>
                        </div>
                    </div>

                    <!-- Thursday -->
                    <div class="day-column">
                        <div class="day-header">Thursday</div>
                        <div class="time-grid-lines">
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                        </div>
                        <!-- QUANTITATIVE MODELS1 Block -->
                        <div class="schedule-block schedule-pink" style="top: 200px; height: 120px;" onclick="viewScheduleDetails('QUANTITATIVE')">
                            <div class="course-code">QUANTITATIVE MODELS1</div>
                            <div class="instructor">Prof. Johnson</div>
                            <div class="enrollment">(32/32)</div>
                        </div>
                    </div>

                    <!-- Friday -->
                    <div class="day-column">
                        <div class="day-header">Friday</div>
                        <div class="time-grid-lines">
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                        </div>
                        <!-- CS1 Block -->
                        <div class="schedule-block schedule-pink" style="top: 80px; height: 200px;" onclick="viewScheduleDetails('CS1')">
                            <div class="course-code">CS1</div>
                            <div class="instructor">Prof. White</div>
                            <div class="enrollment">(32/32)</div>
                        </div>
                    </div>

                    <!-- Saturday -->
                    <div class="day-column">
                        <div class="day-header">Saturday</div>
                        <div class="time-grid-lines">
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                            <div class="time-line"></div>
                        </div>
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
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
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

    <!-- Schedule Details Modal -->
    <div id="scheduleDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="detailsTitle">Schedule Details</h3>
                <p class="modal-subtitle" id="detailsSubtitle">View and manage schedule information</p>
            </div>

            <div id="scheduleDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeScheduleDetailsModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="editSchedule()">Edit Schedule</button>
            </div>
        </div>
    </div>

    <script>
        // Sample schedule data
        const scheduleData = {
            '1811': {
                'ITCAPSTONE1': {
                    courseCode: 'ITCAPSTONE1',
                    courseName: 'IT Capstone Project 1',
                    instructor: 'Prof. Cruz',
                    day: 'Monday',
                    startTime: '08:00',
                    endTime: '10:00',
                    enrolled: 20,
                    capacity: 30,
                    color: 'blue'
                },
                'ICYBERSEC1': {
                    courseCode: 'ICYBERSEC1',
                    courseName: 'Introduction to Cybersecurity',
                    instructor: 'Prof. Green',
                    day: 'Tuesday',
                    startTime: '08:00',
                    endTime: '12:00',
                    enrolled: 30,
                    capacity: 32,
                    color: 'red'
                },
                'ALGORITHMS1': {
                    courseCode: 'ALGORITHMS1',
                    courseName: 'Algorithm Analysis',
                    instructor: 'Prof. Lee',
                    day: 'Wednesday',
                    startTime: '11:00',
                    endTime: '01:00',
                    enrolled: 38,
                    capacity: 45,
                    color: 'orange'
                },
                'QUANTITATIVE': {
                    courseCode: 'QUANTITATIVE MODELS1',
                    courseName: 'Quantitative Models',
                    instructor: 'Prof. Johnson',
                    day: 'Thursday',
                    startTime: '10:00',
                    endTime: '12:00',
                    enrolled: 32,
                    capacity: 32,
                    color: 'pink'
                },
                'CS1': {
                    courseCode: 'CS1',
                    courseName: 'Computer Science 1',
                    instructor: 'Prof. White',
                    day: 'Friday',
                    startTime: '08:00',
                    endTime: '01:00',
                    enrolled: 32,
                    capacity: 32,
                    color: 'pink'
                }
            },
            '1812': {
                // Add schedules for other labs
            }
        };

        // Check if user is logged in (for demo purposes, we'll skip this check)
        // const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
        // if (!currentUser) {
        //     window.location.href = 'index.html';
        // }

        function changeRoom() {
            const roomSelect = document.getElementById('roomSelect');
            const selectedRoom = roomSelect.value;
            const scheduleTitle = document.getElementById('scheduleTitle');
            scheduleTitle.textContent = `Viewing: Lab ${selectedRoom} Schedule`;
            
            // Here you would typically reload the schedule data for the selected room
            console.log('Changed to room:', selectedRoom);
        }

        function openAddScheduleModal() {
            const modal = document.getElementById('addScheduleModal');
            modal.classList.add('active');
        }

        function closeAddScheduleModal() {
            const modal = document.getElementById('addScheduleModal');
            modal.classList.remove('active');
            document.getElementById('addScheduleForm').reset();
        }

        function viewScheduleDetails(courseCode) {
            const currentRoom = document.getElementById('roomSelect').value;
            const schedule = scheduleData[currentRoom] && scheduleData[currentRoom][courseCode];
            
            if (!schedule) {
                console.log('Schedule not found:', courseCode);
                return;
            }

            const modal = document.getElementById('scheduleDetailsModal');
            const title = document.getElementById('detailsTitle');
            const subtitle = document.getElementById('detailsSubtitle');
            const content = document.getElementById('scheduleDetailsContent');

            title.textContent = schedule.courseCode;
            subtitle.textContent = schedule.courseName;

            content.innerHTML = `
                <div style="display: grid; gap: 15px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <strong>Instructor:</strong><br>
                            <span style="color: #666;">${schedule.instructor}</span>
                        </div>
                        <div>
                            <strong>Laboratory:</strong><br>
                            <span style="color: #666;">Lab ${currentRoom}</span>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <strong>Day & Time:</strong><br>
                            <span style="color: #666;">${schedule.day}, ${schedule.startTime} - ${schedule.endTime}</span>
                        </div>
                        <div>
                            <strong>Enrollment:</strong><br>
                            <span style="color: #666;">${schedule.enrolled}/${schedule.capacity} students</span>
                        </div>
                    </div>
                    <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <strong>Enrollment Status:</strong><br>
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                            <div style="flex: 1; background: #e5e7eb; border-radius: 10px; height: 8px;">
                                <div style="width: ${(schedule.enrolled / schedule.capacity) * 100}%; background: ${schedule.enrolled >= schedule.capacity ? '#ef4444' : '#10b981'}; height: 100%; border-radius: 10px;"></div>
                            </div>
                            <span style="font-size: 12px; color: #666;">${Math.round((schedule.enrolled / schedule.capacity) * 100)}%</span>
                        </div>
                    </div>
                </div>
            `;

            modal.classList.add('active');
        }

        function closeScheduleDetailsModal() {
            const modal = document.getElementById('scheduleDetailsModal');
            modal.classList.remove('active');
        }

        function editSchedule() {
            alert('Edit schedule functionality would be implemented here');
            closeScheduleDetailsModal();
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                localStorage.removeItem('currentUser');
                window.location.href = 'index.html';
            }
        }

        // Handle add schedule form submission
        document.getElementById('addScheduleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                courseCode: document.getElementById('courseCode').value,
                courseName: document.getElementById('courseName').value,
                instructor: document.getElementById('instructor').value,
                dayOfWeek: document.getElementById('dayOfWeek').value,
                startTime: document.getElementById('startTime').value,
                endTime: document.getElementById('endTime').value,
                maxCapacity: parseInt(document.getElementById('maxCapacity').value)
            };

            console.log('Adding new schedule:', formData);
            
            // Here you would typically send the data to your backend
            alert('Schedule added successfully!');
            closeAddScheduleModal();
        });

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Schedule Management page loaded');
        });
    </script>
</body>
</html>
