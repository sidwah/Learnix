<?php
require_once '../config.php';
session_start();


$user_id = $_SESSION['user_id'];

try {
    // Get department info
    $dept_query = "SELECT d.department_id, d.name as department_name 
                   FROM departments d 
                   INNER JOIN department_staff ds ON d.department_id = ds.department_id 
                   WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL";
    
    $dept_stmt = $conn->prepare($dept_query);
    $dept_stmt->bind_param("i", $user_id);
    $dept_stmt->execute();
    $dept_result = $dept_stmt->get_result();
    
    if ($dept_result->num_rows === 0) {
        die('Access denied');
    }
    
    $department = $dept_result->fetch_assoc();
    $department_id = $department['department_id'];
    
    // Get enrollments data
    $enrollments_query = "SELECT 
                            e.enrollment_id,
                            CONCAT(u.first_name, ' ', u.last_name) as student_name,
                            u.email as student_email,
                            c.title as course_title,
                            cat.name as category_name,
                            e.enrolled_at,
                            e.status,
                            e.completion_percentage,
                            e.last_accessed,
                            CASE 
                                WHEN p.enrollment_id IS NOT NULL THEN 'Paid'
                                WHEN c.price > 0 THEN 'Pending'
                                ELSE 'Free'
                            END as payment_status,
                            COALESCE(p.amount, c.price, 0) as amount,
                            -- Calculate actual progress
                            COALESCE(
                                (SELECT 
                                    (COUNT(CASE WHEN prog.completion_status = 'Completed' THEN 1 END) * 100.0) / 
                                    NULLIF(COUNT(st.topic_id), 0)
                                FROM course_sections cs
                                LEFT JOIN section_topics st ON cs.section_id = st.section_id  
                                LEFT JOIN progress prog ON st.topic_id = prog.topic_id AND prog.enrollment_id = e.enrollment_id AND prog.deleted_at IS NULL
                                WHERE cs.course_id = c.course_id AND cs.deleted_at IS NULL
                                ), 0
                            ) as actual_progress
                        FROM enrollments e
                        INNER JOIN courses c ON e.course_id = c.course_id
                        INNER JOIN users u ON e.user_id = u.user_id
                        LEFT JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
                        LEFT JOIN categories cat ON sub.category_id = cat.category_id
                        LEFT JOIN course_payments p ON e.enrollment_id = p.enrollment_id AND p.status = 'Completed'
                        WHERE c.department_id = ?    
                        ORDER BY e.enrolled_at DESC";
    
    $enrollments_stmt = $conn->prepare($enrollments_query);
    $enrollments_stmt->bind_param("i", $department_id);
    $enrollments_stmt->execute();
    $enrollments_result = $enrollments_stmt->get_result();
    
    // Set headers for CSV download
    $filename = 'enrollments_' . $department['department_name'] . '_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create file pointer
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'Enrollment ID',
        'Student Name',
        'Student Email',
        'Course Title',
        'Category',
        'Enrollment Date',
        'Status',
        'Progress (%)',
        'Last Accessed',
        'Payment Status',
        'Amount (GHS)'
    ]);
    
    // Add data rows
    while ($row = $enrollments_result->fetch_assoc()) {
        fputcsv($output, [
            $row['enrollment_id'],
            $row['student_name'],
            $row['student_email'],
            $row['course_title'],
            $row['category_name'] ?: 'Uncategorized',
            date('Y-m-d H:i:s', strtotime($row['enrolled_at'])),
            $row['status'],
            number_format($row['actual_progress'], 1),
            $row['last_accessed'] ? date('Y-m-d H:i:s', strtotime($row['last_accessed'])) : 'Never',
            $row['payment_status'],
            number_format($row['amount'], 2)
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    error_log("Error exporting enrollments: " . $e->getMessage());
    die('Export failed');
}
?>