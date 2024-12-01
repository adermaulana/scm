<?php
	include("../includes/config.php");
	include("../includes/validate_data.php");
	session_start();
	if(isset($_SESSION['manufacturer_login'])) {
		if($_SESSION['manufacturer_login'] == true) {
			$query_selectCategory = "SELECT cat_id,cat_name FROM categories";
			$query_selectUnit = "SELECT id,unit_name FROM unit";
			$result_selectCategory = mysqli_query($con,$query_selectCategory);
			$result_selectUnit = mysqli_query($con,$query_selectUnit);
			$name = $price = $unit = $category = $rdbStock = $description = "";
			$nameErr = $priceErr = $requireErr = $confirmMessage = "";
			$nameHolder = $priceHolder = $descriptionHolder = "";
			if($_SERVER['REQUEST_METHOD'] == "POST") {
				if(!empty($_POST['txtProductName'])) {
					$nameHolder = $_POST['txtProductName'];
					$name = $_POST['txtProductName'];
				}
				if(!empty($_POST['txtProductPrice'])) {
					$priceHolder = $_POST['txtProductPrice'];
					$resultValidate_price = validate_price($_POST['txtProductPrice']);
					if($resultValidate_price == 1) {
						$price = $_POST['txtProductPrice'];
					}
					else {
						$priceErr = $resultValidate_price;
					}
				}
				if(isset($_POST['cmbProductUnit'])) {
					$unit = $_POST['cmbProductUnit'];
				}
				if(isset($_POST['cmbProductCategory'])) {
					$category = $_POST['cmbProductCategory'];
				}
				if(empty($_POST['rdbStock'])) {
					$rdbStock = "";
				}
				else {
					if($_POST['rdbStock'] == 1) {
						$rdbStock = 1;
					}
					else if($_POST['rdbStock'] == 2) {
						$rdbStock = 2;
					}
				}
				if(!empty($_POST['txtProductDescription'])) {
					$description = $_POST['txtProductDescription'];
					$descriptionHolder = $_POST['txtProductDescription'];
				}
				if($name != null && $price != null && $unit != null && $category != null && $rdbStock == 1) {
					$rdbStock = 0;
					$query_addProduct = "INSERT INTO products(pro_name,pro_desc,pro_price,unit,pro_cat,quantity) VALUES('$name','$description','$price','$unit','$category','$rdbStock')";
					if(mysqli_query($con,$query_addProduct)) {
						echo "<script> alert(\"Product Added Successfully\"); </script>";
						header('Refresh:0');
					}
					else {
						$requireErr = "Adding Product Failed";
					}
			}
				else if($name != null && $price != null && $unit != null && $category != null && $rdbStock == 2) {
						$query_addProduct = "INSERT INTO products(pro_name,pro_desc,pro_price,unit,pro_cat,quantity) VALUES('$name','$description','$price','$unit','$category',NULL)";
					if(mysqli_query($con,$query_addProduct)) {
						echo "<script> alert(\"Product Added Successfully\"); </script>";
						header('Refresh:0');
					}
					else {
						$requireErr = "Adding Product Failed";
					}
				}
				else {
					$requireErr = "* All Fields are Compulsory with valid values except Description";
				}
			}
		}
		else {
			header('Location:../index.php');
		}
	}
	else {
		header('Location:../index.php');
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title> Tambah Produk </title>
	<link rel="stylesheet" href="../includes/main_style.css" >
</head>
<body>
	<?php
		include("../includes/header.inc.php");
		include("../includes/nav_manufacturer.inc.php");
		include("../includes/aside_manufacturer.inc.php");
	?>
	<section>
		<h1>Tambah Produk</h1>
		<form action="" method="POST" class="form">
		<ul class="form-list">
		<li>
			<div class="label-block"> <label for="product:name">Nama Produk</label> </div>
			<div class="input-box"> <input type="text" id="product:name" name="txtProductName" placeholder="Nama Produk" value="<?php echo $nameHolder; ?>" required /> </div> <span class="error_message"><?php echo $nameErr; ?></span>
		</li>
		<li>
			<div class="label-block"> <label for="product:price">Harga</label> </div>
			<div class="input-box"> <input type="text" id="product:price" name="txtProductPrice" placeholder="Harga" value="<?php echo $priceHolder; ?>" required /> </div> <span class="error_message"><?php echo $priceErr; ?></span>
		</li>
		<li>
		<div class="label-block"> <label for="product:unit">Tipe Unit</label> </div>
		<div class="input-box">
		<select name="cmbProductUnit" id="product:unit">
			<option value="" disabled selected>--- Pilih Unit ---</option>
			<?php while($row_selectUnit = mysqli_fetch_array($result_selectUnit)) { ?>
			<option value="<?php echo $row_selectUnit["id"]; ?>"> <?php echo $row_selectUnit["unit_name"]; ?> </option>
			<?php } ?>
		</select>
		</div>
		</li>
		<li>
		<div class="label-block"> <label for="product:category">Kategori</label> </div>
		<div class="input-box">
		<select name="cmbProductCategory" id="product:category">
			<option value="" disabled selected>--- Pilih Kategori ---</option>
			<?php while($row_selectCategory = mysqli_fetch_array($result_selectCategory)) { ?>
			<option value="<?php echo $row_selectCategory["cat_id"]; ?>"> <?php echo $row_selectCategory["cat_name"]; ?> </option>
			<?php } ?>
		</select>
		</div>
		</li>
		<li>
			<div class="label-block"> <label for="product:stock">Manajemen Stok</label> </div>
			<input type="radio" name="rdbStock" value="1">Aktif
			<input type="radio" name="rdbStock" value="2">Tidak Aktif
		</li>
		<li>
			<div class="label-block"> <label for="product:description">Deskripsi</label> </div>
			<div class="input-box"> <textarea type="text" id="product:description" name="txtProductDescription" placeholder="Deskripsi"><?php echo $descriptionHolder; ?></textarea> </div>
		</li>
		<li>
			<input type="submit" value="Tambah Produk" class="submit_button" /> <span class="error_message"> <?php echo $requireErr; ?> </span><span class="confirm_message"> <?php echo $confirmMessage; ?> </span>
		</li>
		</ul>
		</form>
	</section>
	<?php
		include("../includes/footer.inc.php");
	?>
</body>
</html>