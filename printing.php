<?php
session_start();
$host = 'localhost';
$db   = 'secure_library';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Get case data if ID is provided
$case_data = null;
if (isset($_GET['case_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM case_logs WHERE id = ? AND deleted = 0");
    $stmt->execute([$_GET['case_id']]);
    $case_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission for custom printing
$print_data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $print_data = $_POST;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print PRC Legal Receipt | LAWS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="img/prc-logo.png" sizes="1200x1200"/>
    <link href="css/dashboard.css" rel="stylesheet">
    <link rel="stylesheet" href="css/sidebar.css">
    <script src="js/sidebar.js" defer></script>

    <style>
        /* Base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar styles */
        .sidebar {
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 280px !important;
            height: 100vh !important;
            z-index: 1000;
            transition: none !important;
            transform: none !important;
        }

        .container {
            display: flex;
            min-height: 100vh;
            width: 100%;
            padding-left: 280px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .controls-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-group input, .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Print styles */
        .receipt-container {
            background: white;
            max-width: 900px;
            margin: 0 auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .receipt {
            padding: 30px;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.6;
            color: #000;
            font-size: 12px;
            position: relative;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        /* Fixed logo styles */
        .prc-logo {
            width: 80px !important;
            height: auto !important;
            max-height: 80px !important;
            margin: 0 auto 10px auto !important;
            display: block !important;
            object-fit: contain !important;
        }

        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .receipt-subtitle {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            text-decoration: underline;
        }

        .department {
            font-size: 12px;
            line-height: 1.4;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 2px solid #000;
        }

        .main-table td, .main-table th {
            border: 1px solid #000;
            padding: 8px 6px;
            vertical-align: top;
            font-size: 11px;
        }

        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            padding: 6px;
        }

        .field-label {
            background-color: #f8f8f8;
            font-weight: bold;
            width: 180px;
            padding: 6px;
        }

        .field-value {
            padding: 6px;
            min-height: 25px;
            background-color: white;
        }

        .warning-line {
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
            padding: 8px;
            border: 2px solid #000;
            background-color: #f0f0f0;
            font-size: 12px;
        }

        .return-slip {
            margin-top: 20px;
        }

        .return-slip-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
            text-decoration: underline;
        }

        .return-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }

        .return-table td, .return-table th {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            vertical-align: top;
            font-size: 10px;
        }

        .return-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .return-table .wide-cell {
            text-align: left;
            padding: 6px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 2px solid #000;
        }

        .signature-table td {
            border: 1px solid #000;
            padding: 15px 6px;
            font-size: 11px;
            font-weight: bold;
        }

        .signature-table .label-col {
            background-color: #f8f8f8;
            width: 150px;
        }

        /* Footer styles */
        .receipt-footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            font-weight: bold;
            line-height: 1.3;
            color: #000;
        }

        /* Print media query for 8.5x13 inch paper */
        @media print {
            @page {
                size: 8.5in 13in;
                margin: 0.5in;
            }
            
            body {
                margin: 0;
                padding: 0;
                background: white !important;
            }
            
            .sidebar,
            .controls-section,
            .btn-group,
            .page-title {
                display: none !important;
            }
            
            .container {
                padding-left: 0 !important;
                display: block !important;
            }
            
            .main-content {
                padding: 0 !important;
                background: white !important;
                min-height: auto !important;
            }
            
            .receipt-container {
                position: static !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: none !important;
                margin: 0 !important;
            }
            
            .receipt {
                padding: 15px !important;
                page-break-inside: avoid;
            }

            /* Print logo sizing */
            .prc-logo {
                width: 60px !important;
                height: auto !important;
                max-height: 60px !important;
            }

            .main-table,
            .return-table,
            .signature-table {
                page-break-inside: avoid;
            }

            .receipt-footer {
                font-size: 9px;
                margin-top: 20px;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding-left: 0;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                padding: 15px;
            }
            
            .receipt {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <h1 class="page-title">Print PRC Legal Receipt</h1>
            
            <!-- Controls Section -->
            <div class="controls-section">
                <form method="post" id="receiptForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="borrower_name">1.1 Name of Borrower</label>
                            <input type="text" id="borrower_name" name="borrower_name" 
                                   value="<?php echo $case_data['log_in_user'] ?? ($print_data['borrower_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="office_division">1.2 Office/Division</label>
                            <input type="text" id="office_division" name="office_division" 
                                   value="<?php echo $print_data['office_division'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="date_requested">1.3 Date Requested</label>
                            <input type="date" id="date_requested" name="date_requested" 
                                   value="<?php echo $print_data['date_requested'] ?? date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="details_purpose">2. Details/Purpose</label>
                            <textarea id="details_purpose" name="details_purpose"><?php echo $print_data['details_purpose'] ?? ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="case_title">3. Title of Case/Caption</label>
                            <input type="text" id="case_title" name="case_title" 
                                   value="<?php echo $case_data['case_title'] ?? ($print_data['case_title'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="board_profession">4. Board/Profession</label>
                            <input type="text" id="board_profession" name="board_profession" 
                                   value="<?php echo $print_data['board_profession'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="top_last_page">5. Top/Last Page</label>
                            <input type="text" id="top_last_page" name="top_last_page" 
                                   value="<?php echo $print_data['top_last_page'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="name_signature">6. Name and Signature</label>
                            <input type="text" id="name_signature" name="name_signature" 
                                   value="<?php echo $print_data['name_signature'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="receiving_staff">7. Receiving Staff</label>
                            <input type="text" id="receiving_staff" name="receiving_staff" 
                                   value="<?php echo $print_data['receiving_staff'] ?? ''; ?>">
                        </div>
                        <!--<div class="form-group">
                            <label for="confirming_staff">7. Receiving Staff</label>
                            <input type="text" id="receiving_staff" name="receiving_staff" 
                                   value="<?php echo $print_data['receiving_staff'] ?? ''; ?>">
                        </div>-->

                    </div>
                    
                    <!-- Return Slip Section -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="case_number_box">8. Case Title and Number/Box Number</label>
                            <input type="text" id="case_number_box" name="case_number_box" 
                                   value="<?php echo $case_data['case_number'] ?? ($print_data['case_number_box'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="profession">9. Profession</label>
                            <input type="text" id="profession" name="profession" 
                                   value="<?php echo $print_data['profession'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="tracking_no">10. Charge Out Tracking No.</label>
                            <input type="text" id="tracking_no" name="tracking_no" 
                                   value="<?php echo $print_data['tracking_no'] ?? 'TRK-' . date('Ymd') . '-' . rand(1000, 9999); ?>">
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-rounded">refresh</span>
                            Update Preview
                        </button>
                        <button type="button" class="btn btn-success" onclick="printReceipt()">
                            <span class="material-symbols-rounded">print</span>
                            Print Receipt
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <span class="material-symbols-rounded">arrow_back</span>
                            Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>

            <!-- Receipt Preview -->
            <div class="receipt-container" id="receiptContainer">
                <div class="receipt">
                    <!-- Header -->
                    <div class="receipt-header">
                        <img src="img/prc-logo.png" alt="PRC Logo" class="prc-logo">
                        <div class="receipt-title">Professional Regulation Commission</div>
                        <div class="receipt-subtitle">CHARGE OUT RECEIPT FOR DOCKETED CASES</div>
                        <div class="department">
                            Administrative Service<br>
                            Archives and Records Division
                        </div>
                    </div>

                    <!-- Main Form Table -->
                    <table class="main-table">
                        <tr>
                            <td class="section-header" colspan="5">1. BORROWER'S DETAILS</td>
                        </tr>
                        <tr>
                            <td class="field-label">1.1 NAME OF BORROWER</td>
                            <td class="field-value" colspan="4"><?php echo htmlspecialchars($case_data['log_in_user'] ?? ($print_data['borrower_name'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <td class="field-label">1.2 OFFICE/DIVISION</td>
                            <td class="field-value" colspan="4"><?php echo htmlspecialchars($print_data['office_division'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <td class="field-label">1.3 DATE REQUESTED</td>
                            <td class="field-value" colspan="4"><?php echo htmlspecialchars($print_data['date_requested'] ?? date('Y-m-d')); ?></td>
                        </tr>
                        <tr>
                            <td class="field-label">2. DETAILS/PURPOSE</td>
                            <td class="field-value" colspan="4"><?php echo nl2br(htmlspecialchars($print_data['details_purpose'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <td class="field-label">3. TITLE OF CASE/CAPTION</td>
                            <td class="field-value" colspan="4"><?php echo htmlspecialchars($case_data['case_title'] ?? ($print_data['case_title'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <td class="field-label">4. BOARD/PROFESSION</td>
                            <td class="field-value" colspan="4"><?php echo htmlspecialchars($print_data['board_profession'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <td class="field-label">5. TOP/LAST PAGE</td>
                            <td class="field-value" colspan="4"><?php echo htmlspecialchars($print_data['top_last_page'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <td class="field-label">6. NAME AND SIGNATURE:</td>
                            <td class="field-value" colspan="4"><?php echo htmlspecialchars($print_data['name_signature'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <td class="field-label">7. RECEIVING STAFF IN BEHALF OF THE BORROWER:</td>
                            <td class="field-value" colspan="4"><?php echo htmlspecialchars($print_data['receiving_staff'] ?? ''); ?></td>
                        </tr>
                    </table>

                    <!-- Warning Line -->
                    <div class="warning-line">
                        PLEASE DO NOT WRITE BELOW THIS LINE
                    </div>

                    <!-- Return Slip -->
                    <div class="return-slip">
                        <div class="return-slip-title">BORROWER'S RETURN SLIP</div>
                        
                        <table class="return-table">
                            <tr>
                                <th style="width: 35%;">8. CASE TITLE AND NUMBER/BOX NUMBER</th>
                                <th style="width: 15%;">9. PROFESSION</th>
                                <th style="width: 20%;">10. CHARGE OUT TRACKING NO.</th>
                                <th style="width: 15%;">11. DATE DUE</th>
                                <th style="width: 15%;">12. DATE AND TIME RETURN</th>
                            </tr>
                            <tr style="height: 40px;">
                                <td class="wide-cell"><?php echo htmlspecialchars($case_data['case_number'] ?? ($print_data['case_number_box'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($print_data['profession'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($print_data['tracking_no'] ?? 'TRK-' . date('Ymd') . '-' . rand(1000, 9999)); ?></td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                        
                        <!-- Signature Table -->
                        <table class="signature-table">
                            <tr>
                                <td class="label-col">13. RETURNED BY:</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="label-col">14. RECEIVED BY:</td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Footer -->
                    <div class="receipt-footer">
                        <strong>ARD-27</strong><br>
                        <strong>Rev. 00</strong><br>
                        <strong>February 15, 2022</strong><br>
                        <strong>Page 1 of 1</strong>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function printReceipt() {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            const receiptContent = document.getElementById('receiptContainer').innerHTML;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>PRC Legal Receipt</title>
                    <style>
                        @page {
                            size: 8.5in 13in;
                            margin: 0.5in;
                        }
                        
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        
                        body {
                            font-family: 'Times New Roman', Times, serif;
                            font-size: 12px;
                            line-height: 1.4;
                            color: #000;
                            background: white;
                        }
                        
                        .receipt {
                            padding: 15px;
                            max-width: 100%;
                        }
                        
                        .receipt-header {
                            text-align: center;
                            border-bottom: 3px solid #000;
                            padding-bottom: 15px;
                            margin-bottom: 20px;
                        }
                        
                        .prc-logo {
                            width: 60px !important;
                            height: auto !important;
                            max-height: 60px !important;
                            margin: 0 auto 10px auto !important;
                            display: block !important;
                            object-fit: contain !important;
                        }
                        
                        .receipt-title {
                            font-size: 16px;
                            font-weight: bold;
                            margin-bottom: 5px;
                            text-transform: uppercase;
                        }
                        
                        .receipt-subtitle {
                            font-size: 14px;
                            font-weight: bold;
                            margin-bottom: 15px;
                            text-decoration: underline;
                        }
                        
                        .department {
                            font-size: 12px;
                            line-height: 1.4;
                        }
                        
                        .main-table, .return-table, .signature-table {
                            width: 100%;
                            border-collapse: collapse;
                            border: 2px solid #000;
                            margin-bottom: 15px;
                        }
                        
                        .main-table td, .main-table th,
                        .return-table td, .return-table th,
                        .signature-table td {
                            border: 1px solid #000;
                            padding: 6px;
                            vertical-align: top;
                        }
                        
                        .section-header {
                            background-color: #f0f0f0;
                            font-weight: bold;
                            text-align: center;
                        }
                        
                        .field-label {
                            background-color: #f8f8f8;
                            font-weight: bold;
                            width: 180px;
                        }
                        
                        .field-value {
                            min-height: 25px;
                        }
                        
                        .warning-line {
                            text-align: center;
                            font-weight: bold;
                            margin: 20px 0;
                            padding: 8px;
                            border: 2px solid #000;
                            background-color: #f0f0f0;
                        }
                        
                        .return-slip-title {
                            text-align: center;
                            font-weight: bold;
                            font-size: 14px;
                            margin-bottom: 15px;
                            text-decoration: underline;
                        }
                        
                        .return-table th {
                            background-color: #f0f0f0;
                            font-weight: bold;
                            text-align: center;
                            font-size: 10px;
                        }
                        
                        .return-table td {
                            text-align: center;
                            font-size: 10px;
                        }
                        
                        .wide-cell {
                            text-align: left !important;
                        }
                        
                        .signature-table .label-col {
                            background-color: #f8f8f8;
                            font-weight: bold;
                            width: 150px;
                        }
                        
                        .signature-table td {
                            padding: 15px 6px;
                            font-size: 11px;
                        }
                        
                        .receipt-footer {
                            margin-top: 20px;
                            text-align: right;
                            font-size: 9px;
                            font-weight: bold;
                            line-height: 1.3;
                            color: #000;
                        }
                    </style>
                </head>
                <body>
                    ${receiptContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            
            // Wait for content to load then print
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }, 500);
        }

        // Auto-generate tracking number if empty
        document.addEventListener('DOMContentLoaded', function() {
            const trackingInput = document.getElementById('tracking_no');
            if (!trackingInput.value) {
                const today = new Date();
                const dateStr = today.getFullYear() + 
                               String(today.getMonth() + 1).padStart(2, '0') + 
                               String(today.getDate()).padStart(2, '0');
                const randomNum = Math.floor(Math.random() * 9000) + 1000;
                trackingInput.value = 'TRK-' + dateStr + '-' + randomNum;
            }
        });
    </script>
</body>
</html>