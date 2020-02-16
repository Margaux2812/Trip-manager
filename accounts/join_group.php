<?php
session_start();
if(empty($_SESSION['id'])){
	header('location:../index.php');
	die;
}
include('../functions.inc.php');

if(!empty($_POST["logout"])) {
	session_destroy();
	header('Location: ../index.php');
	die;
}

/*On veut rejoindre le groupe*/
if(isset($_POST['join'])){
	sendNotificationAdmin($_POST['groupe'], $_SESSION['id']);
	$message = 'Votre demande a été envoyée';
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
		<title>My Trip Manager</title>
		<link rel="stylesheet" type="text/css" href="styles/stylegroups.css"/>
		<link rel="icon" href="../images/icon.png" sizes="16x16" type="image/png" />
		<link rel="icon" href="../images/iconbigger.png" sizes="32x32" type="image/png" />
</head>
<body>
	<?php
	include('header.php');
?>
	<div id='content'>
		<form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get'>
			<input type="search" name="search" placeholder="Rechercher" title="Rechercher (ALT + SHIFT + s)" accesskey="s" id="searchInput" autocomplete="off" size="20">
			<input type="submit" name="rechercher" value="rechercher" id="searchButton">
		</form>
		
		<?php
		if(isset($_GET['search'])){	
			try{
				$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
			}
			catch (Exception $e){
				die('Erreur : ' . $e->getMessage());
			}
			
			$reponse = $bdd->query("SELECT * FROM groupe WHERE nom LIKE '%".$_GET['search']."%';");

			if(!is_bool($reponse)){
				echo '<table>
						<tr>
							<td>Nom du groupe</td>
							<td>Membres</td>
							<td></td>
						</tr>';
				while ($donnees = $reponse->fetch()){

					/*Nom du groupe*/
					echo'
						<tr>
							<td>'.$donnees['nom'].'</td>';
					
					$list_id = json_decode($donnees['membres']);
					
					/*Si on a des valeurs*/
					if(is_array($list_id)){
						
					
						$recherche = $bdd->query("SELECT * FROM personne WHERE id IN(".implode(",", $list_id).");");
						
						/*Si on trouves des membres*/
						if(!is_bool($recherche)){
							$liste = array();
							while ($list_membres = $recherche->fetch()){
								$nomcomplet = $list_membres['nom'].' '.$list_membres['prenom'];
								array_push($liste, $nomcomplet);
							}
							
							$membres = implode(", ", $liste);
							echo'
								<td>'.$membres.'</td>
								<td>';
							if(!ingroup($_SESSION['id'], $donnees['id'])){
								echo '<form method="post" action="join_group.php">
								<input type="hidden" name="groupe" value="'.$donnees['id'].'" >
								<input type="submit" name="join" value="Rejoindre">
								</form></td>
								</tr>';
							}else{
								echo '</td>';
							}
							$recherche->closeCursor();
						}else{
							echo '<td>Aucun membre</td>
							<td>Rejoindre</td>
							</tr>';
						}
					}else{
							echo '<td>Aucun membre</td>
							<td>Rejoindre</td>
							</tr>';
						}
				}
				$reponse->closeCursor(); // Termine le traitement de la requête
				
				echo'</table>';
			}else{
				echo '<p>Nous n\'avons trouvé aucun groupe correspondant à vos critères de recherche</p>';
			}
		}
		?>
	</div>
</body>
</html>