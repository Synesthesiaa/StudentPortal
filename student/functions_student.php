<?php
// Utility functions for student pages

function getYearLevelFromSubjectCode($subject_code) {
    if (preg_match('/^[A-Z]+(\d+)-(\d+)/', $subject_code, $matches)) {
        $year_number = intval(substr($matches[2], 0, 1));
        switch ($year_number) {
            case 1:
                return '1st Year';
            case 2:
                return '2nd Year';
            case 3:
                return '3rd Year';
            case 4:
                return '4th Year';
            default:
                return 'Unknown Year';
        }
    }
    return 'Unknown Year';
}

function getSubjectName($subject_code) {
    $subject_names = [
        // BSCS Subjects
        'CS1-101' => 'Introduction to Computer Science',
        'CS1-102' => 'Programming Fundamentals',
        'CS1-103' => 'Discrete Mathematics',
        'CS2-104' => 'Computer Architecture',
        'CS2-105' => 'Data Structures',
        'CS2-106' => 'Object-Oriented Programming',
        'CS1-201' => 'Algorithms and Complexity',
        'CS1-202' => 'Operating Systems',
        'CS1-203' => 'Computer Networks',
        'CS2-204' => 'Software Engineering',
        'CS2-205' => 'Database Systems',
        'CS2-206' => 'Web Development',
        'CS1-301' => 'Artificial Intelligence',
        'CS1-302' => 'Machine Learning',
        'CS1-303' => 'Computer Graphics',
        'CS2-304' => 'Compiler Design',
        'CS2-305' => 'Computer Security',
        'CS2-306' => 'Distributed Systems',
        'CS1-401' => 'Advanced AI',
        'CS1-402' => 'Distributed Systems',
        'CS1-403' => 'Computer Vision',
        'CS2-404' => 'CS Capstone Project',
        'CS2-405' => 'CS Internship',
        'CS2-406' => 'Advanced Topics in CS',
        // BSIT Subjects
        'IT1-101' => 'Introduction to Computing',
        'IT1-102' => 'Computer Programming 1',
        'IT1-103' => 'Computer Programming 2',
        'IT2-104' => 'Data Structures and Algorithms',
        'IT2-105' => 'Web Development',
        'IT2-106' => 'Database Management Systems',
        'IT1-201' => 'Database Management Systems',
        'IT1-202' => 'Object-Oriented Programming',
        'IT1-203' => 'Networking 1',
        'IT2-204' => 'Systems Analysis and Design',
        'IT2-205' => 'Mobile Application Development',
        'IT2-206' => 'Web Technologies',
        'IT1-301' => 'Advanced Database Systems',
        'IT1-302' => 'Web Application Development',
        'IT1-303' => 'Networking 2',
        'IT2-304' => 'Software Engineering',
        'IT2-305' => 'Information Security',
        'IT2-306' => 'Cloud Computing',
        'IT1-401' => 'IT Project Management',
        'IT1-402' => 'Cloud Computing',
        'IT1-403' => 'Artificial Intelligence',
        'IT2-404' => 'IT Capstone Project',
        'IT2-405' => 'IT Internship',
        'IT2-406' => 'Emerging Technologies',
        // BSCE Subjects
        'CE1-101' => 'Introduction to Computer Engineering',
        'CE1-102' => 'Digital Logic Design',
        'CE1-103' => 'Computer Organization',
        'CE2-104' => 'Programming for Engineers',
        'CE2-105' => 'Circuit Analysis',
        'CE2-106' => 'Microprocessors',
        'CE1-201' => 'Microprocessors',
        'CE1-202' => 'Computer Architecture',
        'CE1-203' => 'Embedded Systems',
        'CE2-204' => 'Digital Systems',
        'CE2-205' => 'Computer Networks',
        'CE2-206' => 'VLSI Design',
        'CE1-301' => 'VLSI Design',
        'CE1-302' => 'Computer Security',
        'CE1-303' => 'Real-time Systems',
        'CE2-304' => 'Robotics',
        'CE2-305' => 'Computer Vision',
        'CE2-306' => 'IoT Systems',
        'CE1-401' => 'Advanced Computer Architecture',
        'CE1-402' => 'IoT Systems',
        'CE1-403' => 'Hardware Security',
        'CE2-404' => 'CE Capstone Project',
        'CE2-405' => 'CE Internship',
        'CE2-406' => 'Advanced Digital Systems',
        // BSEE Subjects
        'EE1-101' => 'Introduction to Electrical Engineering',
        'EE1-102' => 'Circuit Analysis 1',
        'EE1-103' => 'Electronics 1',
        'EE2-104' => 'Digital Electronics',
        'EE2-105' => 'Engineering Mathematics',
        'EE2-106' => 'Electromagnetics',
        'EE1-201' => 'Circuit Analysis 2',
        'EE1-202' => 'Electronics 2',
        'EE1-203' => 'Electromagnetics',
        'EE2-204' => 'Power Systems',
        'EE2-205' => 'Control Systems',
        'EE2-206' => 'Power Electronics',
        'EE1-301' => 'Power Electronics',
        'EE1-302' => 'Electric Machines',
        'EE1-303' => 'Communication Systems',
        'EE2-304' => 'Digital Signal Processing',
        'EE2-305' => 'Power Distribution',
        'EE2-306' => 'Renewable Energy',
        'EE1-401' => 'Renewable Energy Systems',
        'EE1-402' => 'Smart Grid Technology',
        'EE1-403' => 'Power System Protection',
        'EE2-404' => 'EE Capstone Project',
        'EE2-405' => 'EE Internship',
        'EE2-406' => 'Advanced Power Systems',
        // BSChem Subjects
        'CHEM1-101' => 'General Chemistry',
        'CHEM1-102' => 'Organic Chemistry 1',
        'CHEM1-103' => 'Physical Chemistry 1',
        'CHEM2-104' => 'Analytical Chemistry',
        'CHEM2-105' => 'Chemical Engineering Principles',
        'CHEM2-106' => 'Chemical Thermodynamics',
        'CHEM1-201' => 'Organic Chemistry 2',
        'CHEM1-202' => 'Physical Chemistry 2',
        'CHEM1-203' => 'Chemical Thermodynamics',
        'CHEM2-204' => 'Chemical Kinetics',
        'CHEM2-205' => 'Unit Operations',
        'CHEM2-206' => 'Process Control',
        'CHEM1-301' => 'Chemical Process Design',
        'CHEM1-302' => 'Transport Phenomena',
        'CHEM1-303' => 'Chemical Reaction Engineering',
        'CHEM2-304' => 'Process Control',
        'CHEM2-305' => 'Plant Design',
        'CHEM2-306' => 'Environmental Engineering',
        'CHEM1-401' => 'Process Safety',
        'CHEM1-402' => 'Environmental Engineering',
        'CHEM1-403' => 'Plant Economics',
        'CHEM2-404' => 'ChemE Capstone Project',
        'CHEM2-405' => 'ChemE Internship',
        'CHEM2-406' => 'Advanced Process Design',
        // BSME Subjects
        'ME1-101' => 'Introduction to Mechanical Engineering',
        'ME1-102' => 'Engineering Mechanics',
        'ME1-103' => 'Engineering Materials',
        'ME2-104' => 'Engineering Drawing',
        'ME2-105' => 'Thermodynamics 1',
        'ME2-106' => 'Fluid Mechanics',
        'ME1-201' => 'Fluid Mechanics',
        'ME1-202' => 'Heat Transfer',
        'ME1-203' => 'Machine Design',
        'ME2-204' => 'Manufacturing Processes',
        'ME2-205' => 'Thermodynamics 2',
        'ME2-206' => 'Mechanical Vibrations',
        'ME1-301' => 'Mechanical Vibrations',
        'ME1-302' => 'Control Systems',
        'ME1-303' => 'Power Plants',
        'ME2-304' => 'Robotics',
        'ME2-305' => 'Automotive Engineering',
        'ME2-306' => 'Energy Systems',
        'ME1-401' => 'Energy Systems',
        'ME1-402' => 'HVAC Systems',
        'ME1-403' => 'Renewable Energy',
        'ME2-404' => 'ME Capstone Project',
        'ME2-405' => 'ME Internship',
        'ME2-406' => 'Advanced Manufacturing'
    ];
    return $subject_names[$subject_code] ?? 'Subject Name Not Found';
} 