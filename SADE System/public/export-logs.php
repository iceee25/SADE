<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$filterLab = isset($_GET['lab']) ? $_GET['lab'] : '';
$filterType = isset($_GET['type']) ? $_GET['type'] : '';
$filterDateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filterDateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$format = isset($_GET['format']) ? $_GET['format'] : 'excel'; // Default to Excel

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

// Fetch all filtered logs (no pagination for export)
$result = $conn->query("
    SELECT 
        id,
        lab_id as laboratory,
        action as entry_type,
        DATE_FORMAT(CONVERT_TZ(timestamp, '+00:00', '+08:00'), '%m-%d-%y') AS date,
        DATE_FORMAT(CONVERT_TZ(timestamp, '+00:00', '+08:00'), '%h:%i %p') AS time,
        user_name,
        method,
        CONVERT_TZ(timestamp, '+00:00', '+08:00') AS full_timestamp
    FROM access_logs
    $whereClause
    ORDER BY timestamp DESC
");

$logs = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

if ($format === 'pdf') {
    exportToPDF($logs);
} else {
    exportToExcel($logs);
}

function exportToPDF($logs) {
    // Check if TCPDF library exists, otherwise use alternative
    $filename = 'logs_' . date('Y-m-d_H-i-s') . '.pdf';
    
    // Create PDF manually using basic structure
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Simple HTML to PDF conversion using built-in PHP
    $html = generatePDFHTML($logs);
    
    // For production, use a library like TCPDF or mPDF
    // For now, we'll generate a basic text-based PDF
    $pdf_content = generateBasicPDF($logs);
    echo $pdf_content;
}

function generatePDFHTML($logs) {
    $html = '<html><body>';
    $html .= '<h1>SADE - System Logs Report</h1>';
    $html .= '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
    $html .= '<table border="1" cellpadding="5">';
    $html .= '<tr>';
    $html .= '<th>Laboratory</th>';
    $html .= '<th>Entry Type</th>';
    $html .= '<th>User</th>';
    $html .= '<th>Method</th>';
    $html .= '<th>Date</th>';
    $html .= '<th>Time</th>';
    $html .= '</tr>';
    
    foreach ($logs as $log) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($log['laboratory']) . '</td>';
        $html .= '<td>' . htmlspecialchars($log['entry_type']) . '</td>';
        $html .= '<td>' . htmlspecialchars($log['user_name'] ?? '-') . '</td>';
        $html .= '<td>' . htmlspecialchars($log['method'] ?? '-') . '</td>';
        $html .= '<td>' . htmlspecialchars($log['date']) . '</td>';
        $html .= '<td>' . htmlspecialchars($log['time']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    $html .= '</body></html>';
    return $html;
}

function generateBasicPDF($logs) {
    // Simple text-based PDF generation
    $content = "%PDF-1.4\n";
    $content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
    $content .= "4 0 obj\n<< /Length 2500 >>\nstream\nBT\n/F1 12 Tf\n50 750 Td\n(SADE - System Logs Report) Tj\n0 -20 Td\n(Generated: " . date('Y-m-d H:i:s') . ") Tj\n0 -40 Td\n";
    
    $y = 680;
    foreach ($logs as $log) {
        $text = htmlspecialchars($log['laboratory']) . " | " . 
                htmlspecialchars($log['entry_type']) . " | " .
                htmlspecialchars($log['user_name'] ?? '-') . " | " .
                htmlspecialchars($log['date']) . " " . htmlspecialchars($log['time']);
        
        $content .= "($text) Tj\n0 -15 Td\n";
        $y -= 15;
        
        if ($y < 50) break; // Prevent overflow
    }
    
    $content .= "ET\nendstream\nendobj\n";
    $content .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    $content .= "xref\n0 6\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\n0000000223 00000 n\n0000002773 00000 n\n";
    $content .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n2872\n%%EOF";
    
    return $content;
}


function exportToExcel($logs) {
    $filename = 'logs_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write header row
    fputcsv($output, array('Laboratory', 'Entry Type', 'User', 'Method', 'Date', 'Time'));
    
    // Write data rows
    foreach ($logs as $log) {
        fputcsv($output, array(
            $log['laboratory'],
            $log['entry_type'],
            $log['user_name'] ?? '-',
            $log['method'] ?? '-',
            $log['date'],
            $log['time']
        ));
    }
    
    fclose($output);
    exit();
}
?>
