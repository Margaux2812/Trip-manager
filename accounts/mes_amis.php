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

if(isset($_POST['ordre'])){
	$_SESSION['ordre'] = $_POST['ordre'];
}else{
	$_SESSION['ordre'] = 'nom';
}
if(isset($_POST['format'])){
	$_SESSION['format'] = $_POST['format'];
}else{
	$_SESSION['format'] = 'List';
}

/*Changer les amis*/

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
		<title>My Trip Manager</title>
		<link rel="stylesheet" type="text/css" href="styles/styleamis.css"/>
		<link rel="icon" href="../images/icon.png" sizes="16x16" type="image/png" />
		<link rel="icon" href="../images/iconbigger.png" sizes="32x32" type="image/png" />
		<script src="http://code.jquery.com/jquery.js"></script>
</head>
<body>

	<?php
	include('header.php');
?>
	<div id='content'>
		<table id='bandeau'>
			<tr>
				<td>
					<input type='submit' name='format' id='Square' class='format_button'>
					<input type='submit' name='format' id='List' class='format_button'>
				</td>
				<td>
					<p>Trier par :</p>
					<select name="format" class="ordre_dropdown">
						<option value="nom">Ordre Alphabétique</option>
						<option value="nom DESC">Ordre Alphabétique Inversé</option>
					</select>
				</td>
				<td>
					<form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get'>
						<input type="search" name="search" placeholder="Rechercher" title="Rechercher (ALT + SHIFT + s)" accesskey="s" id="searchInput" autocomplete="off" size="20">
						<input type="submit" name="rechercher" value="rechercher" id="searchButton">
					</form>
				</td>
			</tr>
		</table>
		<div id='listOfFriends'>
		<?php
		/*On fait une recherche*/
		if(!empty($_GET['search'])){
			try{
				$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
			}
			catch (Exception $e){
					die('Erreur : ' . $e->getMessage());
			}
			
			$reponse = $bdd->query("SELECT *, CONCAT(nom, prenom, mail) AS infos FROM personne;");
			
			if(!is_bool($reponse)){
				echo '<table id="tableHole">';
				while ($donnees = $reponse->fetch()){
				/*Enlever la casse + les espaces*/
					if(strpos(strtoupper($donnees['infos']), str_replace(' ', '', strtoupper($_GET['search']))) !== false){
					echo '<tr>
							<td>'.getList($donnees).'</td>
						</tr>';
					}
				}
				echo '</table>';
			
				$reponse->closeCursor(); // Termine le traitement de la requête
				
			}else{
				echo '<p>Nous n\'avons trouvé personne correspondant à vos critères de recherche</p>';
			}
			
		}elseif(!empty($_SESSION['amis'])){
		/*On a des amis*/
			
			echo getListFriend(json_encode($_SESSION['amis']), $_SESSION['ordre'], $_SESSION['format']);
			
		}else{
		?>
			<h2>Vous n'avez pas encore d'amis <img alt='smiley' src='images/smiley.png'></h2>
		<?php
		}
			?>
		</div>
	</div>

</body>
</html>