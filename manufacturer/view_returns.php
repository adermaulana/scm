<?php
require("../includes/config.php");
session_start();

// Ensure only manufacturer can access
if(!isset($_SESSION['manufacturer_login'])) {
    header('Location:../index.php');
    exit();
}

// Retrieve returns with detailed product and order information
$query_returns = "SELECT 
        r.return_id,
        r.order_id,
        r.order_item_id,
        r.return_quantity,
        r.return_reason,
        r.return_date,
        r.return_status,
        p.pro_name,
        p.pro_price,
        o.date AS order_date,
        c.username
    FROM 
        returns r
    JOIN 
        order_items oi ON r.order_item_id = oi.order_items_id
    JOIN 
        products p ON oi.pro_id = p.pro_id
    JOIN 
        orders o ON r.order_id = o.order_id
    JOIN 
        retailer c ON o.retailer_id = c.retailer_id
    ORDER BY 
        r.return_date DESC
";
$result_returns = mysqli_query($con, $query_returns);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Retur Produk</title>
    <link rel="stylesheet" href="../includes/main_style.css">
    <style>
        .returns-table {
            width: 100%;
            border-collapse: collapse;
        }
        .returns-table th, 
        .returns-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .returns-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status-pending {
            color: orange;
            font-weight: bold;
        }
        .status-approved {
            color: green;
            font-weight: bold;
        }
        .status-rejected {
            color: red;
            font-weight: bold;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <?php
    include("../includes/header.inc.php");
    include("../includes/nav_manufacturer.inc.php");
    include("../includes/aside_manufacturer.inc.php");
    ?>
    
    <section>
        <h1>Daftar Retur Produk</h1>
        
        <table class="returns-table">
            <thead>
                <tr>
                    <th>No. Retur</th>
                    <th>Nama Produk</th>
                    <th>Pengecer</th>
                    <th>No. Order</th>
                    <th>Tanggal Order</th>
                    <th>Jumlah Retur</th>
                    <th>Harga Satuan</th>
                    <th>Total Retur</th>
                    <th>Alasan Retur</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row_return = mysqli_fetch_array($result_returns)) { ?>
                <tr>
                    <td><?php echo $row_return['return_id']; ?></td>
                    <td><?php echo $row_return['pro_name']; ?></td>
                    <td><?php echo $row_return['username']; ?></td>
                    <td><?php echo $row_return['order_id']; ?></td>
                    <td><?php echo date("d-m-Y", strtotime($row_return['order_date'])); ?></td>
                    <td><?php echo $row_return['return_quantity']; ?></td>
                    <td>Rp <?php echo number_format($row_return['pro_price'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($row_return['return_quantity'] * $row_return['pro_price'], 0, ',', '.'); ?></td>
                    <td><?php echo $row_return['return_reason']; ?></td>
                    <td>
                        <span class="<?php 
                            echo ($row_return['return_status'] == 'Pending') ? 'status-pending' : 
                                 (($row_return['return_status'] == 'Approved') ? 'status-approved' : 'status-rejected'); 
                        ?>">
                            <?php echo $row_return['return_status'] ?: 'Pending'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="approveReturn(<?php echo $row_return['return_id']; ?>)">Terima</button>
                            <button onclick="rejectReturn(<?php echo $row_return['return_id']; ?>)">Tolak</button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

    <script>
    function approveReturn(returnId) {
        if(confirm('Anda yakin ingin menerima retur ini?')) {
            window.location.href = 'process_return.php?action=approve&return_id=' + returnId;
        }
    }

    function rejectReturn(returnId) {
        if(confirm('Anda yakin ingin menolak retur ini?')) {
            window.location.href = 'process_return.php?action=reject&return_id=' + returnId;
        }
    }
    </script>

    <?php include("../includes/footer.inc.php"); ?>
</body>
</html>