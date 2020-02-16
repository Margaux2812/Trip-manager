<?php
session_start();
if(empty($_SESSION['id'])){
	header('location:../index.php');
	die;
}


if(!empty($_POST["logout"])) {
	session_destroy();
	header('Location: ../index.php');
	die;
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
		<title>My Trip Manager</title>
		<link rel="stylesheet" type="text/css" href="styles/styleindex.css"/>
		<link rel="icon" href="../images/icon.png" sizes="16x16" type="image/png" />
		<link rel="icon" href="../images/iconbigger.png" sizes="32x32" type="image/png" />
</head>
<body>
<?php
	include('header.php');
?>
	<div id='content'>
	
	</div>
</body>
</html>