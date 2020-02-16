<?php
include('functions.inc.php');
//Connexion
if(isset($_GET['sign']) && ($_GET['sign']=='up')){
	if(!empty($_POST["connect"])) {
	/* Regarder s'il y a tous les champs */
		foreach($_POST as $key=>$value) {
			if(empty($_POST[$key])) {
			$erreur = 'Tous les champs sont requis';
			break;
			}
		}
	
	if(!isset($erreur)){
		if(dontmatch($_POST['mail'], $_POST['pass'])){
			$erreur = 'L\'adresse email ou le mot de passe est incorrect';
		}else{
			/*On ouvre la session*/
			session_start();
			
			try{
				$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');

			}catch (Exception $e){
				die('Erreur : ' . $e->getMessage());
			}	
		
			$reponse = $bdd->query('SELECT * FROM personne WHERE mail=\''.$_POST['mail'].'\'');
			$donnees = $reponse->fetch();
			
			$_SESSION['nom'] = $donnees['nom'];
			$_SESSION['prenom'] = $donnees['prenom'];
			$_SESSION['mail'] = $donnees['mail'];
			$_SESSION['pass'] = $donnees['pass'];
			$_SESSION['photo'] = $donnees['photo'];
			$_SESSION['amis'] = json_decode($donnees['amis']);
			$_SESSION['notifs'] = json_decode($donnees['notifs'], TRUE);
			$_SESSION['groupe'] = json_decode($donnees['groupe']);
			$_SESSION['id'] = $donnees['id'];
			$reponse->closeCursor();	
			header('Location: accounts/index.php');
		
		}
	}	
	}
}

//Inscription

if(isset($_GET['sign']) && ($_GET['sign']=='in')){
	if(!empty($_POST["register"])) {
	/* Regarder s'il y a tous les champs */
		foreach($_POST as $key=>$value) {
			if(empty($_POST[$key])) {
			$erreur = 'Tous les champs sont requis';
			break;
			}
		}
		
		if(!isset($erreur)){
			if($_POST['pass'] != $_POST['cpass']){ 
				$erreur = 'Les mots de passe ne correspondent pas'; 
			}
		}
			/* Email Validation */
		if(!isset($erreur)){
			if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL)) {
				$erreur = 'L\'adresse email n\'a pas un format valide';
			}
		}
	
		/*Securite mot de passe*/
	
		if(!isset($erreur)){
			if(strlen($_POST['pass'])<5) {
				$erreur = 'Votre mot de passe doit contenir au moins 5 caractères';
			}
			elseif(!preg_match("#[A-Z]#", $_POST['pass']) || !preg_match("#[0-9]#", $_POST['pass'])){
				$erreur = 'Votre mot de passe doit avoir au moins une majuscule et un chiffre';
			}
		}
			
		if(!isset($erreur)){
			if(already_exists($_POST['mail'])){
				$erreur = 'Votre adresse email est déjà enregistrée dans notre base de donnée';
			}
			else{ 
			
				/*On crypte le mot de passe */
				$mdp_hash = password_hash(htmlspecialchars($_POST['pass']), PASSWORD_DEFAULT);
			
				try{
					$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');

				}catch (Exception $e){
					die('Erreur : ' . $e->getMessage());
				}
			
				$bdd->exec('INSERT INTO personne(nom, prenom, mail, pass, photo, amis, groupe, notifs) VALUES(\''.strtoupper($_POST['nom']).'\', \''.strtoupper($_POST['prenom']).'\', \''.$_POST['mail'].'\', \''.$mdp_hash.'\',\'\' ,\'null\', \'null\', \'null\')');
				
				$erreur = "";
				$compte_valide = 'Votre compte a été créé avec succès';	
				unset($_POST);
			}
		}
		
		
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
		<title>Trip Manager</title>
		<link rel="stylesheet" type="text/css" href="styles/style.css"/>
		<link rel="icon" href="images/icon.png" sizes="16x16" type="image/png" />
		<link rel="icon" href="images/iconbigger.png" sizes="32x32" type="image/png" />
</head>
<body>
	<header>
		<a href="index.php" class='title'>
			<img alt='logo' src='images/logo.png'>
		</a>
		<a href="index.php?sign=in" class='buttons'>
			<img alt="sign in" src="images/signin.png">
		</a>
		<a href="index.php?sign=up" class='buttons'>
			<img alt="sign up" src="images/signup.png">
		</a>
	</header>
	<div id='content'>
		<?php 
		if(isset($_GET['sign']) && ($_GET['sign']=='in')){
		?>
			<h1>Inscription</h1>
			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<table>
				<tr>
					<td>Prénom :</td>
					<td><input type="text" name="prenom" placeholder="Prénom"/></td>
				</tr>
				<tr>
					<td>Nom :</td>
					<td><input type="text" name="nom" placeholder="Nom"/></td>
				</tr>
				<tr>
					<td>Adresse email :</td>
					<td><input type="text" name="mail" placeholder="mail@exemple.com"/></td>
				</tr>
				<tr>
					<td>Mot de passe :</td>
					<td><input type="password" name="pass" placeholder="Mot de passe"/></td>
				</tr>
				<tr>
					<td>Confirmation du mot de passe :</td>
					<td><input type="password" name="cpass" placeholder="Confirmez le mot de passe"/></td>
				</tr>
				<tr>
					<td colspan='2' class='submit_button'><input type="submit" value="S'inscrire" name='register' /></td>
				</tr>
				<?php if(isset($erreur)){
				?>
				<tr>
					<td colspan='2'><?php echo $erreur; ?></td>
				</tr>
				<?php
				}elseif(isset($compte_valide)){
				?>
				<tr>
					<td colspan='2'><?php echo $compte_valide; ?></td>
				</tr>
				<?php
				}
				?>
			</table>
			</form>
		<?php 
		}
		if(isset($_GET['sign']) && ($_GET['sign']=='up')){
		?>
		<h1>Connexion</h1>
			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<table>
				<tr>
					<td>Adresse email :</td>
					<td><input type="text" name="mail" placeholder="mail@exemple.com"/></td>
				</tr>
				<tr>
					<td>Mot de passe :</td>
					<td><input type="password" name="pass" placeholder="Mot de passe"/></td>
				</tr>
				<tr>
					<td colspan='2' id='parent'><a href='forgotten_pass.php'>Mot de passe oublié</a></td>
				</tr>
				<tr>
					<td colspan='2' class='submit_button'><input type="submit" value="Se connecter" name='connect'/></td>
				</tr>
				<?php if(isset($erreur)){
				?>
				<tr>
					<td colspan='2'><?php echo $erreur; ?></td>
				</tr>
				<?php
				}
				?>
			</table>
			</form>
		<?php
		}
		?>
	</div>
	<footer>
	</footer>
</body>
</html>