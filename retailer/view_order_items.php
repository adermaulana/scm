<?php
require("../includes/config.php");
session_start();
if(isset($_SESSION['manufacturer_login']) || isset($_SESSION['admin_login']) || isset($_SESSION['retailer_login'])) {
    $order_id = $_GET['id'];
    
    // Handle return submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_submit'])) {
        $order_item_id = $_POST['order_item_id'];
        $return_quantity = $_POST['return_quantity'];
        $return_reason = mysqli_real_escape_string($con, $_POST['return_reason']);
        
        $insert_return_query = "INSERT INTO returns 
            (order_id, order_item_id, return_quantity, return_reason) 
            VALUES 
            ('$order_id', '$order_item_id', '$return_quantity', '$return_reason')";
        
		if(mysqli_query($con, $insert_return_query)) {
            // Set success message in session
            echo "<script> alert(\"Berhasil Ajukan Retur\"); </script>";
			header('Refresh:0;url=view_my_orders.php');
        }
    }
    
    $query_selectOrderItems = "SELECT *,order_items.quantity AS quantity FROM orders,order_items,products WHERE order_items.order_id='$order_id' AND order_items.pro_id=products.pro_id AND order_items.order_id=orders.order_id";
    $result_selectOrderItems = mysqli_query($con,$query_selectOrderItems);
    $query_selectOrder = "SELECT date,status FROM orders WHERE order_id='$order_id'";
    $result_selectOrder = mysqli_query($con,$query_selectOrder);
    $row_selectOrder = mysqli_fetch_array($result_selectOrder);
}
else {
    header('Location:../index.php');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link rel="stylesheet" href="../includes/main_style.css">


<script>
function openReturnModal(orderItemId, productName, maxQuantity) {
    const modal = document.getElementById('returnModal');
    const productNameEl = document.getElementById('return_product_name');
    const orderItemIdEl = document.getElementById('return_order_item_id');
    const maxQuantityEl = document.getElementById('return_quantity');
    
    productNameEl.textContent = productName;
    orderItemIdEl.value = orderItemId;
    maxQuantityEl.max = maxQuantity;
    maxQuantityEl.value = 1; // Default to 1
    
    modal.style.display = 'flex';
}

function closeReturnModal() {
    document.getElementById('returnModal').style.display = 'none';
}

// Optional: Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('returnModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<style>
    /* Modern, clean modal styling */
    .return-modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
    }

    .return-modal-content {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        width: 90%;
        max-width: 500px;
        padding: 25px;
        position: relative;
    }

    .return-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .return-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #888;
    }

    .return-form-group {
        margin-bottom: 15px;
    }

    .return-form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .return-form-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .return-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .return-btn {
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .return-btn-submit {
        background-color: #4CAF50;
        color: white;
    }

    .return-btn-cancel {
        background-color: #f44336;
        color: white;
    }

    .return-btn:hover {
        opacity: 0.9;
    }

    .return-action-btn {
        background-color: #2196F3;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .return-action-btn:hover {
        background-color: #1976D2;
    }
</style>
</head>
<body>
    <?php
    include("../includes/header.inc.php");
    include("../includes/nav_retailer.inc.php");
    include("../includes/aside_retailer.inc.php");
    ?>
    <section>
        <h1>Detail Order</h1>
        <!-- Existing order details code -->
        
		<table class="table_invoiceFormat">
    <tr>
        <th>Produk</th>
        <th>Harga Unit</th>
        <th>Jumlah</th>
        <th>Total Harga</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php 
    mysqli_data_seek($result_selectOrderItems, 0);
    while($row_selectOrderItems = mysqli_fetch_array($result_selectOrderItems)) { 
    ?>
	<?php if ($row_selectOrderItems['status'] != 0): ?>
		<tr>
			<td><?php echo $row_selectOrderItems['pro_name']; ?></td>
			<td><?php echo $row_selectOrderItems['pro_price']; ?></td>
			<td><?php echo $row_selectOrderItems['quantity']; ?></td>
			<td><?php echo $row_selectOrderItems['quantity']*$row_selectOrderItems['pro_price']; ?></td>
			<?php if ($row_selectOrderItems['status'] != 0): ?>
				<td>Selesai</td>
			<?php else: ?>
				<td>Pending</td>
			<?php endif; ?>
			<td>
				<button class="return-action-btn" 
						onclick="openReturnModal(
							<?php echo $row_selectOrderItems['order_items_id']; ?>, 
							'<?php echo htmlspecialchars($row_selectOrderItems['pro_name']); ?>', 
							<?php echo $row_selectOrderItems['quantity']; ?>
						)">
					Ajukan Retur
				</button>
			</td>
		</tr>
	<?php else: ?>
		<tr>
			<td><?php echo $row_selectOrderItems['pro_name']; ?></td>
			<td><?php echo $row_selectOrderItems['pro_price']; ?></td>
			<td><?php echo $row_selectOrderItems['quantity']; ?></td>
			<td><?php echo $row_selectOrderItems['quantity']*$row_selectOrderItems['pro_price']; ?></td>
			<?php if ($row_selectOrderItems['status'] != 0): ?>
				<td>Selesai</td>
			<?php else: ?>
				<td>Pending</td>
			<?php endif; ?>
			<td>
				<button disabled class="return-action-btn" style="background-color:red !important;">
					Belum Bisa Return
				</button>
			</td>
		</tr>
	<?php endif; ?>
    <?php } ?>
</table>
    </section>

    <!-- Return Modal -->
	<div id="returnModal" class="return-modal">
    <div class="return-modal-content">
        <div class="return-modal-header">
            <h2>Formulir Retur Produk</h2>
            <button class="return-modal-close" onclick="closeReturnModal()">&times;</button>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" id="return_order_item_id" name="order_item_id">
            
            <div class="return-form-group">
                <label>Produk:</label>
                <p id="return_product_name" class="return-form-input" style="background-color:#f4f4f4;"></p>
            </div>
            
            <div class="return-form-group">
                <label>Jumlah Retur:</label>
                <input type="number" 
                       id="return_quantity" 
                       name="return_quantity" 
                       class="return-form-input"
                       min="1" 
                       required>
            </div>
            
            <div class="return-form-group">
                <label>Alasan Retur:</label>
                <textarea 
                    name="return_reason" 
                    class="return-form-input" 
                    rows="4" 
                    required></textarea>
            </div>
            
            <div class="return-form-actions">
                <button type="button" 
                        class="return-btn return-btn-cancel" 
                        onclick="closeReturnModal()">
                    Batal
                </button>
                <button type="submit" 
                        name="return_submit" 
                        class="return-btn return-btn-submit">
                    Ajukan Retur
                </button>
            </div>
        </form>
    </div>
</div>

    <?php include("../includes/footer.inc.php"); ?>
</body>
</html>