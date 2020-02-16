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

/*Créer un groupe*/

if(isset($_POST['create'])){
	if(empty($_POST['name'])){
		$erreur='Veuillez renseigner un nom de groupe';
	}
	
	if(empty($erreur)){
		try{
			$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
		}catch(Exception $e){
			die('Erreur : '.$e->getMessage());
		}
		
		if(!empty($_POST['amis'])){
			array_push($_POST['amis'], $_SESSION['id']);
			$membres = json_encode($_POST['amis']);
		}else{
			$membres=json_encode(array($_SESSION['id']));
		}

		if(!empty($_POST['infos'])){
			$infos=$_POST['infos'];
		}else{
			$infos='';
		}
		
		$arraycouleur = json_decode($membres);
		$arrayfinal=array();
		for($i=0; $i<count($arraycouleur); $i++){
			$arrayfinal[$arraycouleur[$i]] = '#'.dechex(rand(0x000000, 0xFFFFFF));
		}
		
		$array_couleur = json_encode($arrayfinal);

		$bdd->exec('INSERT INTO groupe(nom, membres, infos, events, couleur, activites, creator) VALUES(\''.htmlspecialchars($_POST['name']).'\', \''.$membres.'\', \''.$infos.'\', "null", \''.$array_couleur.'\', "null", '.$_SESSION['id'].')');
		$reponse = $bdd->query("SELECT id FROM groupe WHERE nom='".htmlspecialchars($_POST['name'])."';");
		$donnees = $reponse->fetch();
		
		if(is_array($_SESSION['groupe'])){
			array_push($_SESSION['groupe'], $donnees['id']);
		}else{
			$_SESSION['groupe'] = array($donnees['id']);
		}
		$bdd->exec('UPDATE personne SET groupe = \''.json_encode($_SESSION['groupe']).'\' WHERE id = \''.$_SESSION['id'].'\'');
		
		$reponse->closeCursor();
		
		$erreur='Votre groupe a été créé avec succès';
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
		<title>My Trip Manager</title>
		<link rel="stylesheet" type="text/css" href="styles/styleamis.css"/>
		<link rel="icon" href="../images/icon.png" sizes="16x16" type="image/png" />
		<link rel="icon" href="../images/iconbigger.png" sizes="32x32" type="image/png" />
</head>
<body>

<?php
	include('header.php');
?>
	<div id='content'>
		<h2>Créer un groupe</h2>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<table>
			<tr><td>
			<input type='text' name='name' placeholder='Nom du groupe' ><h4>*</h4></td></tr>
			<tr><th>Inviter des amis</th></tr>
		<?php
		try{
			$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
		}
		catch (Exception $e){
			die('Erreur : ' . $e->getMessage());
		}
				
		$string_amis = implode("','", $_SESSION['amis']);
				
		$reponse = $bdd->query("SELECT * FROM personne WHERE id IN('".$string_amis."') ;");
				
		if(!is_bool($reponse)){
			while ($donnees = $reponse->fetch()){
				echo '<tr><td><input type="checkbox" name="amis[]" value="'.$donnees['id'].'"/><label>'.$donnees['nom'].' '.$donnees['prenom'].'</label></td></tr>';
			}
		}else{
			echo'<tr><td>Aucun amis à inviter</td></tr>';
		}	

		?>
			<tr><td><textarea name="infos" rows="8" cols="45" placeholder="Informations complémentaires"></textarea></td></tr>
			<tr><td><input type="submit" name="create" value="Créer"></td></tr>
		</form>
		
		<?php
		if(isset($erreur)){
			echo '<p>'.$erreur.'</p>';
		}?>
	</div>
</body>
</html>