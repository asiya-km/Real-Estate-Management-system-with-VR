<?php
session_start();
require("config.php");
require('fpdf/fpdf.php'); // You'll need to install FPDF library

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login1.php");
    exit();
}

// Initialize variables
$user_id = $_SESSION['uid'];

// Validate booking ID
if (isset($_REQUEST['booking_id']) && is_numeric($_REQUEST['booking_id'])) {
    $booking_id = intval($_REQUEST['booking_id']);
    
    // Get booking details with property information
    $query = "SELECT b.*, p.title, p.price, p.location, p.city, p.pimage, p.type, p.stype, u.uname as agent_name
              FROM bookings b 
              JOIN property p ON b.property_id = p.pid 
              JOIN user u ON p.uid = u.uid
              WHERE b.id = ? AND b.user_id = ? AND b.payment_status = 'completed'";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$booking = mysqli_fetch_assoc($result)) {
        die("Booking not found, unauthorized access, or payment not completed");
    }
} else {
    die("Invalid booking ID");
}

// Create PDF receipt
class PDF extends FPDF {
    function Header() {
        // Logo
        $this->Image('images/logo.png', 10, 10, 50);
        // Line break
        $this->Ln(20);
    }
    
    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Initialize PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Title
$pdf->Cell(0, 10, 'PAYMENT RECEIPT', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Receipt #: ' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT), 0, 1, 'C');
$pdf->Ln(10);

// Customer Information
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Customer Information', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 8, 'Name:', 0);
$pdf->Cell(0, 8, $booking['name'], 0, 1);
$pdf->Cell(40, 8, 'Email:', 0);
$pdf->Cell(0, 8, $booking['email'], 0, 1);
$pdf->Cell(40, 8, 'Phone:', 0);
$pdf->Cell(0, 8, $booking['phone'], 0, 1);
$pdf->Ln(5);

// Property Information
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Property Information', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 8, 'Property:', 0);
$pdf->Cell(0, 8, $booking['title'], 0, 1);
$pdf->Cell(40, 8, 'Location:', 0);
$pdf->Cell(0, 8, $booking['location'] . ', ' . $booking['city'], 0, 1);
$pdf->Cell(40, 8, 'Type:', 0);
$pdf->Cell(0, 8, $booking['type'] . ' for ' . $booking['stype'], 0, 1);
$pdf->Cell(40, 8, 'Agent:', 0);
$pdf->Cell(0, 8, $booking['agent_name'], 0, 1);
$pdf->Ln(5);

// Payment Information
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Payment Information', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 8, 'Payment Date:', 0);
$pdf->Cell(0, 8, date('F j, Y', strtotime($booking['payment_date'])), 0, 1);
$pdf->Cell(60, 8, 'Payment Method:', 0);
$pdf->Cell(0, 8, ucfirst($booking['payment_method']), 0, 1);
$pdf->Cell(60, 8, 'Transaction Reference:', 0);
$pdf->Cell(0, 8, $booking['payment_reference'], 0, 1);
$pdf->Ln(5);

// Amount
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, 'Amount Paid:', 0);
$pdf->Cell(0, 10, 'ETB ' . number_format($booking['price'], 2), 0, 1);
$pdf->Ln(10);

// Terms
$pdf->SetFont('Arial', 'I', 10);
$pdf->MultiCell(0, 6, 'This receipt confirms your payment for the property booking. Please keep this receipt for your records. For any inquiries, please contact our customer service at support@remsko.com or call +251 11 123 4567.');

// Output PDF
$pdf->Output('Receipt_' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT) . '.pdf', 'D');
?>
