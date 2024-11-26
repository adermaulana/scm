<?php
error_reporting(0);
require("../includes/config.php");
session_start();

if (isset($_SESSION['manufacturer_login'])) {
    // Validasi ID
    $id = isset($_GET['id']) ? mysqli_real_escape_string($con, $_GET['id']) : null;

    if (!$id) {
        echo "<script> alert(\"Invalid Order ID\"); </script>";
        header("refresh:0;url=view_orders.php");
        exit();
    }

    // Inisialisasi Variabel
    $availProId = $availQuantity = $orderProId = $orderQuantity = [];
    $queryAvailQuantity = "
        SELECT 
            products.pro_id AS pro_id,
            products.quantity AS quantity 
        FROM 
            order_items 
        JOIN 
            products 
        ON 
            products.pro_id = order_items.pro_id 
        WHERE 
            order_items.order_id = '$id' 
            AND products.quantity IS NOT NULL";
    $resultAvailQuantity = mysqli_query($con, $queryAvailQuantity);

    $queryOrderQuantity = "
        SELECT 
            quantity AS q,
            pro_id AS p 
        FROM 
            order_items 
        WHERE 
            order_id = '$id'";
    $resultOrderQuantity = mysqli_query($con, $queryOrderQuantity);

    // Periksa hasil query
    if (!$resultAvailQuantity || !$resultOrderQuantity) {
        die("Error in query execution: " . mysqli_error($con));
    }

    // Isi data dari query
    while ($rowAvailQuantity = mysqli_fetch_assoc($resultAvailQuantity)) {
        $availProId[] = $rowAvailQuantity['pro_id'];
        $availQuantity[] = $rowAvailQuantity['quantity'];
    }
    while ($rowOrderQuantity = mysqli_fetch_assoc($resultOrderQuantity)) {
        $orderProId[] = $rowOrderQuantity['p'];
        $orderQuantity[] = $rowOrderQuantity['q'];
    }

    // Pastikan data tersedia untuk kombinasi
    if (empty($availProId) || empty($orderProId)) {
        echo "<script> alert(\"No products available for this order\"); </script>";
        header("refresh:0;url=view_orders.php");
        exit();
    }

    // Proses Update Stok
    $updateSuccess = true; // Indikator sukses
    foreach (array_combine($orderProId, $orderQuantity) as $p => $q) {
        foreach (array_combine($availProId, $availQuantity) as $proId => $quantity) {
            if ($p == $proId) {
                $total = $quantity - $q;
                if ($total >= 0) {
                    $queryUpdateQuantity = "UPDATE products SET quantity='$total' WHERE pro_id='$proId'";
                    if (!mysqli_query($con, $queryUpdateQuantity)) {
                        $updateSuccess = false;
                    }
                } else {
                    $updateSuccess = false;
                }
            }
        }
    }

    // Periksa hasil update
    if (!$updateSuccess) {
        echo "<script> alert(\"You don't have enough stock to approve this order\"); </script>";
        header("refresh:0;url=view_orders.php");
        exit();
    }

    // Konfirmasi Order
    $queryConfirm = "UPDATE orders SET approved=1 WHERE order_id='$id'";
    if (mysqli_query($con, $queryConfirm)) {
        echo "<script> alert(\"Order has been confirmed\"); </script>";
        header("refresh:0;url=view_orders.php");
        exit();
    } else {
        echo "<script> alert(\"There was some issue in approving the order.\"); </script>";
        header("refresh:0;url=view_orders.php");
        exit();
    }
} else {
    // Jika pengguna tidak login
    header('Location:../index.php');
    exit();
}
?>