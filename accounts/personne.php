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

include('../functions.inc.php');

if(!empty($_POST['add'])){
	
	$message = sendInvitation($_GET['id'], $_SESSION['id']);

}
if(!empty($_POST['delete'])){
	$message = deleteFriend($_GET['id'], $_SESSION['id']);
	$_SESSION['amis'] = getAmis($_SESSION['id']);
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
		<title>My Trip Manager</title>
		<link rel="stylesheet" type="text/css" href="styles/stylepersonne.css"/>
		<link rel="icon" href="../images/icon.png" sizes="16x16" type="image/png" />
		<link rel="icon" href="../images/iconbigger.png" sizes="32x32" type="image/png" />
		<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css"/>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		
</head>
<body>
<?php
	include('header.php');
?>
<div id='content'>
<?php
if(empty($_GET['id'])){
?>

<h2>Uhoh, quelque chose s'est mal passé</h2>

<?php	
}else{
	if(areTogether($_GET['id'], $_SESSION['id']) != null){
		echo '<section id="left">
		<ul>';
		$list_group = areTogether($_GET['id'], $_SESSION['id']);

		for($i=0; $i<count($list_group); $i++){
			echo '<li><a href = "personne.php?id='.$_GET['id'].'&groupe='.$list_group[$i].'">'.getNomGroupe($list_group[$i]).'</a></li>';
		}
		echo '</ul></section>';
	}
	
	echo '<div id="center">'.getFiche($_GET['id'], $_SESSION['amis']);
	if(!empty($_GET['groupe']) && ingroup($_SESSION['id'], $_GET['groupe'])){
		if(getActivites($_GET['id'], $_GET['groupe']) !== null){
			echo '<table id="stars_table">';
			$activites = getActivites($_GET['id'], $_GET['groupe']);
			foreach($activites as $key => $value){
	?>
			<tr>
				<td><?php echo $key; ?></td>
				<td><div id="<?php echo $key; ?>"><?php echo getStars($value, $key, $_GET['id'], $_GET['groupe']); ?></div></td>
			</tr>
		<?php
			}
			echo '</table>';
		}else{
			echo '<table>
			<tr>
				<td>Cette personne n\'a pas encore d\'activité dans ce groupe</td>
			</tr></table>';
		}
	}
	
	if(isset($message)){
		echo'<p>'.$message.'</p>';
	}
	echo '</div>';
}
	if(empty($_GET['groupe'])){
?>

<script type="text/javascript">
	$(function(){
	
		$('#center form>table').css('border-bottom', '1px solid black').css('border-radius', '5px');
	});
</script>
<?php
	}
?>
</div>
</body>
</html>