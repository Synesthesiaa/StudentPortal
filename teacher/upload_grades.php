<?php
require('../signup_login/auth_session.php');
require('../databaseConnector/connector.php');

// Fetch teacher's assigned classes and subjects
$email = $_SESSION['email'];
$query = "SELECT class_assigned, subjects FROM teacher_profiles WHERE email = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// Parse assigned classes and subjects with null checks
$assigned_classes = ($profile && isset($profile['class_assigned']) && $profile['class_assigned']) ? explode(',', $profile['class_assigned']) : [];
$assigned_subjects = ($profile && isset($profile['subjects']) && $profile['subjects']) ? explode(',', $profile['subjects']) : [];

// Handle AJAX request to fetch students
if (isset($_GET['action']) && $_GET['action'] === 'fetch_students' && isset($_GET['section'])) {
    try {
        $class_value = $_GET['section'];
        
        // Parse the class value to extract program, year level, and section
        if (preg_match('/^([A-Z]+)-(\d+)([A-Z])$/', $class_value, $matches)) {
            $program = $matches[1];
            $year_level = $matches[2];
            $section = $matches[3];
        } else {
            throw new Exception("Invalid class format: " . $class_value);
        }
        
        // Fetch students for the specified class
        $query = "SELECT sp.student_id, sl.fname, sl.lname 
                 FROM student_profiles sp 
                 JOIN signup_login sl ON sp.email = sl.email
                 WHERE sp.program = ? AND sp.year_level = ? AND sp.section = ?
                 ORDER BY sl.lname, sl.fname";
        
        $stmt = $con->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $con->error);
        }
        
        if (!$stmt->bind_param("sss", $program, $year_level, $section)) {
            throw new Exception("Binding parameters failed: " . $stmt->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Getting result failed: " . $stmt->error);
        }
        
        $students = array();
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($students);
        exit();
        
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Database error occurred: ' . $e->getMessage()]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Upload Grades - Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-gradient-to-br from-green-50 to-white font-sans min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="teacher_dashboard.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Upload Grades</h2>
            
            <!-- Grade Upload Form -->
            <form action="process_grades.php" method="POST" class="space-y-6">
                <!-- Class Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Class</label>
                    <select name="class" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select a class</option>
                        <?php foreach ($assigned_classes as $class): ?>
                            <option value="<?php echo htmlspecialchars(trim($class)); ?>">
                                <?php echo htmlspecialchars(trim($class)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Subject Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Subject</label>
                    <select name="subject" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select a subject</option>
                        <?php foreach ($assigned_subjects as $subject): ?>
                            <option value="<?php echo htmlspecialchars(trim($subject)); ?>">
                                <?php echo htmlspecialchars(trim($subject)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="existingGradesWarning" class="mt-2 text-red-600 hidden"></div>
                </div>

                <!-- Semester Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Semester</label>
                    <select name="semester" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select a semester</option>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                    </select>
                </div>

                <!-- Update Grades Button (hidden by default) -->
                <div id="updateGradesSection" class="hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-blue-800">Existing Grades Found</h3>
                                <p class="text-blue-600 text-sm">Grades have already been uploaded for this subject and semester.</p>
                            </div>
                            <button type="button" id="loadExistingGradesBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-edit mr-2"></i>
                                Update Grades
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Student Grades Table -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Student Grades</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quizzes (15%)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance (10%)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activities (10%)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participation (10%)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Project (15%)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Midterm Exam (20%)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Exam (20%)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Computation</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Grade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Incomplete</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="studentGradesTable">
                                <!-- Student rows will be dynamically added here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end mt-6">
                    <button type="submit" id="submitGradesBtn" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Upload Grades
                    </button>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <?php 
                        if (isset($_GET['message'])) {
                            echo htmlspecialchars(urldecode($_GET['message']));
                        } else if ($_GET['error'] === 'session') {
                            echo 'Session expired. Please log in again.';
                        } else {
                            echo 'Error uploading grades. Please try again.';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        Grades uploaded successfully!
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
    // Grade Upload functionality
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.querySelector('select[name="class"]');
        const updateGradesSection = document.getElementById('updateGradesSection');
        const loadExistingGradesBtn = document.getElementById('loadExistingGradesBtn');
        const submitGradesBtn = document.getElementById('submitGradesBtn');
        
        if (classSelect) {
            classSelect.addEventListener('change', function() {
                const selectedClass = this.value;
                
                if (selectedClass) {
                    const tableBody = document.getElementById('studentGradesTable');
                    if (tableBody) {
                        tableBody.innerHTML = '<tr><td colspan="13" class="px-6 py-4 text-center text-gray-500">Loading students...</td></tr>';
                        
                        // Fetch students from the database
                        fetch(`upload_grades.php?action=fetch_students&section=${encodeURIComponent(selectedClass)}`)
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(err => {
                                        throw new Error(err.error || `HTTP error! status: ${response.status}`);
                                    });
                                }
                                return response.json();
                            })
                            .then(students => {
                                if (students.error) {
                                    throw new Error(students.error);
                                }
                                
                                if (students.length === 0) {
                                    tableBody.innerHTML = '<tr><td colspan="13" class="px-6 py-4 text-center text-gray-500">No students found in this class.</td></tr>';
                                    return;
                                }
                                
                                tableBody.innerHTML = students.map(student => `
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${student.student_id}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${student.lname}, ${student.fname}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="scores[${student.student_id}][quizzes]" min="0" max="100"
                                                   class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                                   onchange="calculateFinalGrade('${student.student_id}')">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="scores[${student.student_id}][attendance]" min="0" max="100"
                                                   class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                                   onchange="calculateFinalGrade('${student.student_id}')">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="scores[${student.student_id}][activities]" min="0" max="100"
                                                   class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                                   onchange="calculateFinalGrade('${student.student_id}')">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="scores[${student.student_id}][participation]" min="0" max="100"
                                                   class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                                   onchange="calculateFinalGrade('${student.student_id}')">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="scores[${student.student_id}][final_project]" min="0" max="100"
                                                   class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                                   onchange="calculateFinalGrade('${student.student_id}')">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="scores[${student.student_id}][midterm_exam]" min="0" max="100"
                                                   class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                                   onchange="calculateFinalGrade('${student.student_id}')">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="scores[${student.student_id}][final_exam]" min="0" max="100"
                                                   class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                                   onchange="calculateFinalGrade('${student.student_id}')">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="final-computation-${student.student_id}">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="final-grade-${student.student_id}">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button type="button" onclick="toggleIncomplete('${student.student_id}')" 
                                                    class="incomplete-btn-${student.student_id} px-3 py-1 text-xs font-medium rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
                                                Mark Incomplete
                                            </button>
                                            <input type="hidden" name="scores[${student.student_id}][incomplete]" value="0" class="incomplete-input-${student.student_id}">
                                        </td>
                                    </tr>
                                `).join('');
                            })
                            .catch(error => {
                                console.error('Error fetching students:', error);
                                tableBody.innerHTML = `<tr><td colspan="13" class="px-6 py-4 text-center text-red-500">
                                    Error loading students: ${error.message}. Please try again.
                                </td></tr>`;
                            });
                    }
                }
            });
        }

        // Load existing grades button functionality
        if (loadExistingGradesBtn) {
            loadExistingGradesBtn.addEventListener('click', function() {
                const subject = document.querySelector('select[name="subject"]').value;
                const semester = document.querySelector('select[name="semester"]').value;
                const classSection = document.querySelector('select[name="class"]').value;
                
                if (!subject || !semester || !classSection) {
                    alert('Please select class, subject, and semester first.');
                    return;
                }
                
                // Show confirmation dialog
                if (confirm('Are you sure you want to load existing grades for editing? This will replace any current entries.')) {
                    loadExistingGrades(subject, semester, classSection);
                }
            });
        }

        // Add form validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const classSelect = document.querySelector('select[name="class"]');
                const subjectSelect = document.querySelector('select[name="subject"]');
                const studentTable = document.getElementById('studentGradesTable');
                
                if (!classSelect.value || !subjectSelect.value) {
                    e.preventDefault();
                    alert('Please select both class and subject');
                    return;
                }
                
                if (studentTable.children.length === 0) {
                    e.preventDefault();
                    alert('Please select a class to load students');
                    return;
                }
                
                // Check if all required fields are filled (excluding incomplete and dropped students)
                const rows = studentTable.querySelectorAll('tr');
                let allValid = true;
                let incompleteStudents = 0;
                
                rows.forEach(row => {
                    const incompleteInput = row.querySelector('input[name*="[incomplete]"]');
                    if (incompleteInput && incompleteInput.value === '1') {
                        incompleteStudents++;
                        return; // Skip validation for incomplete students
                    }
                    
                    const inputs = row.querySelectorAll('input[type="number"]');
                    inputs.forEach(input => {
                        if (!input.value && input.name.includes('[') && input.name.includes(']')) {
                            allValid = false;
                        }
                    });
                });
                
                if (!allValid) {
                    e.preventDefault();
                    let message = 'Please fill in all grade fields for each student.';
                    if (incompleteStudents > 0) {
                        message += ` (${incompleteStudents} incomplete)`;
                    }
                    alert(message);
                    return;
                }
            });
        }
    });

    // Function to load existing grades
    function loadExistingGrades(subject, semester, classSection) {
        const tableBody = document.getElementById('studentGradesTable');
        tableBody.innerHTML = '<tr><td colspan="13" class="px-6 py-4 text-center text-gray-500">Loading existing grades...</td></tr>';
        
        fetch(`fetch_existing_grades.php?subject=${encodeURIComponent(subject)}&semester=${encodeURIComponent(semester)}&class=${encodeURIComponent(classSection)}`)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.error || `HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(grades => {
                if (grades.error) {
                    throw new Error(grades.error);
                }
                
                if (grades.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="13" class="px-6 py-4 text-center text-gray-500">No existing grades found for this subject and semester.</td></tr>';
                    return;
                }
                
                tableBody.innerHTML = grades.map(grade => {
                    const isIncomplete = grade.final_grade === 'INC';
                    
                    return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${grade.student_id}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${grade.lname}, ${grade.fname}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" name="scores[${grade.student_id}][quizzes]" min="0" max="100"
                                       value="${grade.quizzes || ''}"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                       onchange="calculateFinalGrade('${grade.student_id}')"
                                       ${isIncomplete ? 'disabled' : ''}>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" name="scores[${grade.student_id}][attendance]" min="0" max="100"
                                       value="${grade.attendance || ''}"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                       onchange="calculateFinalGrade('${grade.student_id}')"
                                       ${isIncomplete ? 'disabled' : ''}>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" name="scores[${grade.student_id}][activities]" min="0" max="100"
                                       value="${grade.activities || ''}"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                       onchange="calculateFinalGrade('${grade.student_id}')"
                                       ${isIncomplete ? 'disabled' : ''}>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" name="scores[${grade.student_id}][participation]" min="0" max="100"
                                       value="${grade.participation || ''}"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                       onchange="calculateFinalGrade('${grade.student_id}')"
                                       ${isIncomplete ? 'disabled' : ''}>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" name="scores[${grade.student_id}][final_project]" min="0" max="100"
                                       value="${grade.final_project || ''}"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                       onchange="calculateFinalGrade('${grade.student_id}')"
                                       ${isIncomplete ? 'disabled' : ''}>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" name="scores[${grade.student_id}][midterm_exam]" min="0" max="100"
                                       value="${grade.midterm_exam || ''}"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                       onchange="calculateFinalGrade('${grade.student_id}')"
                                       ${isIncomplete ? 'disabled' : ''}>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" name="scores[${grade.student_id}][final_exam]" min="0" max="100"
                                       value="${grade.final_exam || ''}"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
                                       onchange="calculateFinalGrade('${grade.student_id}')"
                                       ${isIncomplete ? 'disabled' : ''}>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="final-computation-${grade.student_id}">${grade.final_computation || '-'}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="final-grade-${grade.student_id} ${isIncomplete ? 'text-red-600 font-bold' : ''}">${grade.final_grade}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button type="button" onclick="toggleIncomplete('${grade.student_id}')" 
                                        class="incomplete-btn-${grade.student_id} px-3 py-1 text-xs font-medium rounded-full ${isIncomplete ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-700'} hover:bg-gray-300 transition-colors">
                                    ${isIncomplete ? 'Mark Complete' : 'Mark Incomplete'}
                                </button>
                                <input type="hidden" name="scores[${grade.student_id}][incomplete]" value="${isIncomplete ? '1' : '0'}" class="incomplete-input-${grade.student_id}">
                            </td>
                        </tr>
                    `;
                }).join('');
                
                // Update submit button text
                const submitBtn = document.getElementById('submitGradesBtn');
                if (submitBtn) {
                    submitBtn.textContent = 'Update Grades';
                    submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                }
            })
            .catch(error => {
                console.error('Error loading existing grades:', error);
                tableBody.innerHTML = `<tr><td colspan="13" class="px-6 py-4 text-center text-red-500">
                    Error loading existing grades: ${error.message}. Please try again.
                </td></tr>`;
            });
    }

    // Function to calculate final grade
    function calculateFinalGrade(studentId) {
        // Check if student is marked as incomplete
        const incompleteInput = document.querySelector(`.incomplete-input-${studentId}`);
        if (incompleteInput && incompleteInput.value === '1') {
            return; // Don't calculate if marked as incomplete
        }

        const weights = {
            quizzes: 0.15,
            attendance: 0.10,
            activities: 0.10,
            participation: 0.10,
            final_project: 0.15,
            midterm_exam: 0.20,
            final_exam: 0.20
        };

        let finalComputation = 0;
        let allScoresPresent = true;

        // Calculate weighted average
        for (const [component, weight] of Object.entries(weights)) {
            const score = parseFloat(document.querySelector(`input[name="scores[${studentId}][${component}]"]`).value) || 0;
            if (score === 0) {
                allScoresPresent = false;
            }
            finalComputation += (score * weight);
        }

        // Update final computation display
        const computationElement = document.querySelector(`.final-computation-${studentId}`);
        const gradeElement = document.querySelector(`.final-grade-${studentId}`);

        if (allScoresPresent) {
            computationElement.textContent = finalComputation.toFixed(2);
            
            // Convert to 1-5 scale
            let finalGrade;
            if (finalComputation >= 90) finalGrade = 1;
            else if (finalComputation >= 85) finalGrade = 1.5;
            else if (finalComputation >= 80) finalGrade = 2;
            else if (finalComputation >= 75) finalGrade = 2.5;
            else if (finalComputation >= 70) finalGrade = 3;
            else if (finalComputation >= 65) finalGrade = 3.5;
            else if (finalComputation >= 60) finalGrade = 4;
            else finalGrade = 5;

            gradeElement.textContent = finalGrade;
        } else {
            computationElement.textContent = '-';
            gradeElement.textContent = '-';
        }
    }

    // Function to toggle incomplete status
    function toggleIncomplete(studentId) {
        const incompleteBtn = document.querySelector(`.incomplete-btn-${studentId}`);
        const incompleteInput = document.querySelector(`.incomplete-input-${studentId}`);
        const gradeElement = document.querySelector(`.final-grade-${studentId}`);
        const computationElement = document.querySelector(`.final-computation-${studentId}`);
        // Get all grade inputs for this student
        const gradeInputs = document.querySelectorAll(`input[name="scores[${studentId}][quizzes]"], input[name="scores[${studentId}][attendance]"], input[name="scores[${studentId}][activities]"], input[name="scores[${studentId}][participation]"], input[name="scores[${studentId}][final_project]"], input[name="scores[${studentId}][midterm_exam]"], input[name="scores[${studentId}][final_exam]"]`);

        if (incompleteInput.value === '0') {
            // Mark as incomplete
            incompleteBtn.textContent = 'Mark Complete';
            incompleteBtn.classList.remove('bg-gray-200', 'text-gray-700');
            incompleteBtn.classList.add('bg-red-500', 'text-white');
            incompleteInput.value = '1';
            gradeElement.textContent = 'INC';
            gradeElement.classList.add('text-red-600', 'font-bold');
            computationElement.textContent = 'INC';
            computationElement.classList.add('text-red-600', 'font-bold');
            // Disable and clear all grade inputs
            gradeInputs.forEach(input => {
                input.disabled = true;
                input.value = '';
                input.classList.add('bg-gray-100', 'cursor-not-allowed');
            });
            // Recalculate the grade
            calculateFinalGrade(studentId);
        } else {
            // Mark as complete
            incompleteBtn.textContent = 'Mark Incomplete';
            incompleteBtn.classList.remove('bg-red-500', 'text-white');
            incompleteBtn.classList.add('bg-gray-200', 'text-gray-700');
            incompleteInput.value = '0';
            gradeElement.classList.remove('text-red-600', 'font-bold');
            computationElement.classList.remove('text-red-600', 'font-bold');
            // Enable all grade inputs
            gradeInputs.forEach(input => {
                input.disabled = false;
                input.classList.remove('bg-gray-100', 'cursor-not-allowed');
            });
            // Recalculate the grade
            calculateFinalGrade(studentId);
        }
    }

    function checkExistingGrades() {
        const subject = document.querySelector('select[name="subject"]').value;
        const semester = document.querySelector('select[name="semester"]').value;
        const classSection = document.querySelector('select[name="class"]').value;
        const warningDiv = document.getElementById('existingGradesWarning');
        const updateGradesSection = document.getElementById('updateGradesSection');
        const submitButton = document.getElementById('submitGradesBtn');

        if (subject && semester && classSection) {
            // Make AJAX call to check for existing grades
            fetch(`check_existing_grades.php?subject=${encodeURIComponent(subject)}&semester=${encodeURIComponent(semester)}&class=${encodeURIComponent(classSection)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        warningDiv.classList.add('hidden');
                        updateGradesSection.classList.remove('hidden');
                        submitButton.disabled = false;
                        submitButton.textContent = 'Upload Grades';
                        submitButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        submitButton.classList.add('bg-green-600', 'hover:bg-green-700');
                    } else {
                        warningDiv.classList.add('hidden');
                        updateGradesSection.classList.add('hidden');
                        submitButton.disabled = false;
                        submitButton.textContent = 'Upload Grades';
                        submitButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        submitButton.classList.add('bg-green-600', 'hover:bg-green-700');
                    }
                })
                .catch(error => {
                    console.error('Error checking existing grades:', error);
                    warningDiv.classList.add('hidden');
                    updateGradesSection.classList.add('hidden');
                    submitButton.disabled = false;
                });
        } else {
            warningDiv.classList.add('hidden');
            updateGradesSection.classList.add('hidden');
            submitButton.disabled = false;
        }
    }

    // Add event listeners to form elements
    document.querySelector('select[name="class"]').addEventListener('change', checkExistingGrades);
    document.querySelector('select[name="subject"]').addEventListener('change', checkExistingGrades);
    document.querySelector('select[name="semester"]').addEventListener('change', checkExistingGrades);
    </script>
</body>
</html>