<?php
session_start();
if(empty($_SESSION['id'])){
	header('location:../index.php');
	die;
}
include('../functions.inc.php');

if(isset($_POST["logout"])) {
	session_destroy();
	header('Location: ../index.php');
	die;
}

/*J'ai accepté une demande d'ami*/
if(isset($_GET['addfriend'])){
	if($_GET['addfriend'] == 'YES'){
		addFriend($_GET['id'], $_SESSION['id']);
		$_SESSION['amis'] = getAmis($_SESSION['id']);
	}elseif($_GET['addfriend'] == 'NO'){
		rejectFriend($_GET['id'], $_SESSION['id']);
	}
	removeNotif($_GET['num_notif'], $_SESSION['id']);
}

/*Changer la photo de profil*/
if(isset($_POST['change'])){
	if(!isnotvalid($_FILES['pic'])){
		$_SESSION['photo'] = upload($_FILES['pic'], $_SESSION['id']);	
	}else{
		$erreur ='Veuillez selectionner un fichier valide';
	}
}

/*Je reponds a une demande pour rejoindre un groupe que j'ai créé*/
//sendInvitationAdmin a regarder

/*J'ai une invitation pour un groupe*/
if(isset($_GET['joingroup']) && isset($_GET['gr']) && isset($_GET['parrain'])){
	if($_GET['joingroup'] =='YES'){
		joinGroup($_GET['gr'], $_GET['parrain'], $_SESSION['id']);
		$_SESSION['groupe'] = getGroupe($_SESSION['id']);
	}else{
		rejectGroup($_GET['gr'], $_GET['parrain'], $_SESSION['id']);
	}
	removeNotif($_GET['num_notif'], $_SESSION['id']);
}

/*Je ferme une notif*/
if(isset($_GET['notif_html']) && !empty($_GET['notif_html'])){
	findNotif(htmlentities(htmlentities($_GET['notif_html'])), $_SESSION['id']);
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
		<title>My Trip Manager</title>
		<link rel="stylesheet" type="text/css" href="styles/styleme.css"/>
		<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css"/>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<link rel="icon" href="../images/icon.png" sizes="16x16" type="image/png" />
		<link rel="icon" href="../images/iconbigger.png" sizes="32x32" type="image/png" />
</head>
<body>
	<?php
	include('header.php');
?>
	<div id='content'>
		<div id='change_pic'>
			<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>' enctype="multipart/form-data">
				<img alt='profile_picture' src='images/icon_wait.png' id='preview' >
				<input type='file' name='pic' id='pic'>				
				<input type='submit' value='Choisir cette photo' name='change' id='change'>
			</form>					
		</div>
		<div id='left'>
			<table>
				<tr>
					<th>Mes groupes</th>
				</tr>
				<?php
				if(is_array($_SESSION['groupe'])){
					try{
						$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');

					}catch (Exception $e){
						die('Erreur : ' . $e->getMessage());
					}	
					
					$groupes = implode(",", $_SESSION['groupe']);
					$reponse = $bdd->query('SELECT * FROM groupe WHERE id IN('.$groupes.')');
						
					if(!is_bool($reponse)){
						
						while($donnees = $reponse->fetch()){
						
							echo '<tr>
								<td><a href="'.$_SERVER['PHP_SELF'].'?groupe_id='.$donnees['id'].'">'.$donnees['nom'].'</a></td>
								</tr>';	
						}
					}
											
				}else{
					echo '<tr>
						<td>Vous n\'êtes dans encore aucun groupe</td>
						</tr>';
				}
				?>
			</table>
		</div>
		<div id='center'>
		<table>
			<tr>
				<td colspan='2' id='profile_picture'>
				
					<img alt='profile' src="<?php
					if(empty($_SESSION['photo'])){
						$photo='images/profile.png';
					}else{
						$photo = $_SESSION['photo'];
					}
					echo $photo;
					?>">
					<input type='submit' id='sub' value='Changer cette photo'>
				<?php if(isset($erreur)){ echo $erreur; } ?></td>
			</tr>
			<tr>
				<td colspan='2'><?php echo '<strong>'.ucfirst($_SESSION['nom']).'</strong> '.ucfirst($_SESSION['prenom']); ?></td>
			</tr>
			<tr>
				<td colspan='2'><?php echo '@ : '.$_SESSION['mail']; ?></td>
			</tr>
			
		<?php
		if(!empty($_GET['groupe_id'])){
			if(getActivites($_SESSION['id'], $_GET['groupe_id']) !== null){
				$activites = getActivites($_SESSION['id'], $_GET['groupe_id']);
				foreach($activites as $key => $value){
		?>
			<tr>
				<td><?php echo $key; ?></td>
				<td><div id="<?php echo $key; ?>"><?php echo getStars($value, $key, $_SESSION['id'], $_GET['groupe_id']); ?></div></td>
			</tr>
		<?php
				}
			}else{
				echo '<tr>
					<td>Vous n\'avez pas encore d\'activité dans ce groupe</td>
					</tr>';
			}
		}
		?>
				
		</table>
		</div>
		<div id='right'>
			<h2>Notifications</h2>
			<?php
			echo getNotifications($_SESSION['id']);
			?>
		</div>
	</div>
		<script src="http://code.jquery.com/jquery.js"></script>
		<script>
		$(function() {	
			$('#change_pic').hide();
			
			$('#sub').click(function(){
				$('#change_pic').show();
			});
			
			function readURL(input) {

			  if (input.files && input.files[0]) {
				var reader = new FileReader();

				reader.onload = function(e) {
				  $('#preview').attr('src', e.target.result);
				}

				reader.readAsDataURL(input.files[0]);
			  }
			}

			$("#pic").change(function() {
			  readURL(this);
			});
			
			$('.closeButton').click(function(){
				//this).parent().attr('style', 'display=none;');
				var notif = $(this).parent().html();
				window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?notif_html=" + notif; 
			
			});
		});
		</script>
</body>
</html>