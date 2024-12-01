<?php
require("../includes/config.php");
session_start();

// Ensure only manufacturer can process returns
if(!isset($_SESSION['manufacturer_login'])) {
    header('Location:../index.php');
    exit();
}

if(isset($_GET['action']) && isset($_GET['return_id'])) {
    // Start a database transaction for data integrity
    mysqli_begin_transaction($con);

    try {
        $return_id = mysqli_real_escape_string($con, $_GET['return_id']);
        $action = mysqli_real_escape_string($con, $_GET['action']);

        // First, retrieve return details to get product and quantity
        $query_return_details = "
            SELECT 
                r.return_id, 
                r.return_quantity, 
                oi.pro_id 
            FROM 
                `returns` r
            JOIN 
                order_items oi ON r.order_item_id = oi.order_items_id
            WHERE 
                r.return_id = '$return_id'
        ";
        $result = mysqli_query($con, $query_return_details);
        $return_details = mysqli_fetch_assoc($result);

        if($action == 'approve') {
            // Update return status to Approved
            $update_return_query = "
                UPDATE `returns` 
                SET return_status = 'Approved' 
                WHERE return_id = '$return_id'
            ";
            mysqli_query($con, $update_return_query);

            // Restore stock to products table
            $update_stock_query = "
                UPDATE products 
                SET quantity = quantity + {$return_details['return_quantity']} 
                WHERE pro_id = '{$return_details['pro_id']}'
            ";
            mysqli_query($con, $update_stock_query);
        } else {
            // Reject return
            $update_return_query = "
                UPDATE `returns` 
                SET return_status = 'Rejected' 
                WHERE return_id = '$return_id'
            ";
            mysqli_query($con, $update_return_query);
        }

        // Commit the transaction
        mysqli_commit($con);

        // Set success message
        $_SESSION['message'] = "Return " . 
            (($action == 'approve') ? 'Approved and Stock Restored' : 'Rejected');
        
        header('Location: view_returns.php');
        exit();

    } catch (Exception $e) {
        // Rollback the transaction in case of error
        mysqli_rollback($con);

        // Set error message
        $_SESSION['error'] = "Failed to process return: " . $e->getMessage();
        header('Location: view_returns.php');
        exit();
    }
}
?>