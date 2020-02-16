<?php
session_start();
include('../functions.inc.php');
include('functions.php');

if(empty($_SESSION['id'])){
	header('location:../index.php');
	die;
}

if(!empty($_POST["logout"])) {
	session_destroy();
	header('location:../index.php');
	die;
}

if(!empty($_GET['groupe_id'])){
	if(ingroup($_SESSION['id'], $_GET['groupe_id'])){
		$_SESSION['group_viewing'] = $_GET['groupe_id'];
		$_SESSION['color'] = getColor($_SESSION['group_viewing'], $_SESSION['id']);
	}
}

/*Ajouter activité*/

if(isset($_POST['add_champ'])){
	if(!empty($_POST['name'])){
		$nom = strtoupper(str_replace(' ', '_', $_POST['name']));
		
		$error_champ = setChamp($nom, $_SESSION['group_viewing'], $_SESSION['id']);
		
	}else{
		$error_champ = 'Veuillez renseigner le champ requis';
	}
}

/*Envoyer des invitations*/
if(isset($_POST['send'])){
	if(is_array($_POST['friends'])){
		for($i=0; $i<count($_POST['friends']); $i++){
			sendInvitationGroup($_POST['friends'][$i], $_GET['groupe_id'], $_SESSION['id']);
		}
		$error_send = 'Vos demandes ont été envoyées';
	}else{
		$error_send = 'Vous n\'avez sélectionné aucun ami.';
	}
}

/*Modifier la couleur*/

if(!empty($_POST['color'])){
	setColor($_SESSION['group_viewing'], $_SESSION['id'], $_POST['color']);
	$_SESSION['color'] = $_POST['color'];
}

/*Ajouter disponibilité*/
if(isset($_GET['add_dispo'])){
	/*Si l'utilisateur a écrit directement dans la barre de recherche*/
	if(!isset($_GET['start_date']) || !isset($_GET['end_date'])){
		$error = 'Veuillez remplir tous les champs';
	}else{
		/*Pour comparer les dates, on doit les mettre au format YY-MM-DD*/
		$start = $_GET['start_date'];
		$end = $_GET['end_date'];
		if(strtotime($start) <= strtotime($end)){
			setDisponibilites($_SESSION['id'], $_SESSION['group_viewing'], $_GET['start_date'], $_GET['end_date']);
		}else{
			$error = 'Il semble y avoir une erreur dans les dates renseignées';
		}
	}
}

/*Supprimer des disponibilités*/

if(isset($_GET['supp_dispo'])){
	/*Si l'utilisateur a écrit directement dans la barre de recherche*/
	if(!isset($_GET['start_date']) || !isset($_GET['end_date'])){
		$error = 'Veuillez remplir tous les champs';
	}else{
		/*Pour comparer les dates, on doit les mettre au format YY-MM-DD*/
		$start = substr($_GET['start_date'], 2);
		$end = substr($_GET['end_date'], 2);
		if(strtotime($start) <= strtotime($end)){
			supprimerDisponibilites($_SESSION['id'], $_SESSION['group_viewing'], $_GET['start_date'], $_GET['end_date']);
		}else{
			$error = 'Il semble y avoir une erreur dans les dates renseignées';
		}
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
		<title>My Trip Manager</title>
		<link rel="stylesheet" type="text/css" href="styles/stylegroups.css"/>
		<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css"/>
		<link rel="icon" href="../images/icon.png" sizes="16x16" type="image/png" />
		<link rel="icon" href="../images/iconbigger.png" sizes="32x32" type="image/png" />
		<script src="http://code.jquery.com/jquery.js"></script>
</head>
<body>
<?php
	include('header.php');
?>
	<div id='content'>
	<?php
		if(!isset($_GET['groupe_id'])){
	?>
	<table id='table_alone'>
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
				<tr>
					<th><a href='join_group.php'>Rejoindre un groupe</a></th>
				</tr>
				<tr>
					<th><a href='create_group.php'>Créer un groupe</a></th>
				</tr>
			</table>
	<?php
		}elseif(ingroup($_SESSION['id'], $_GET['groupe_id'])){
	?>
		<div id='left'>
			<h2>Les tâches du groupe</h2>
				<form method='post' action='<?php echo $_SERVER['REQUEST_URI']; ?>'>
					<input type='text' name='name' placeholder='Nom de la tâche' />
					<input type='submit' value = 'Ajouter' name= 'add_champ'/>
				</form>
			<table>
				<?php
				if(!empty($error_champ)){
					echo '<p>'.$error_champ.'</p>';
				}
				
				/*On liste les activités existantes*/
				if(getActivites($_SESSION['id'], $_SESSION['group_viewing']) !== null){
				$activites = getActivites($_SESSION['id'], $_SESSION['group_viewing']);
				foreach($activites as $key => $value){
		?>
			<tr>
				<td><?php echo ucfirst(strtolower($key)).'<p class="small">créé par '.getCreator($_SESSION['group_viewing'], $key).'</p>'; ?></td>
				<td><div id="<?php echo $key; ?>"><?php echo getStars($value, $key, $_SESSION['id'], $_SESSION['group_viewing']); ?></div></td>
			</tr>
		<?php
				}
			}else{
				echo '<tr>
					<td>Vous n\'avez pas encore d\'activité dans ce groupe</td>
					</tr>';
			}
		?>
			</table>
			<h2>Inviter des amis </h2>
			<div id='invite_friend'>
				<form method='post' action='<?php echo $_SERVER['REQUEST_URI']; ?>'>
				<?php
			if(is_array($_SESSION['amis'])){
				for($i=0; $i<count($_SESSION['amis']); $i++){
					if(!ingroup($_SESSION['amis'][$i], $_GET['groupe_id'])){
						echo '<label><input type="checkbox" name="friends[]" value="'.$_SESSION['amis'][$i].'"/><span class="checkmark"></span> <a href="personne.php?id='.$_SESSION['amis'][$i].'">'.getNom($_SESSION['amis'][$i]).'</a></label>';
					}
				}
				echo '<input type="submit" value="Envoyer" name="send"/>';
			}
				?>
				</form>
			<?php
			if(isset($error_send)){
				echo '<p>'.$error_send.'</p>';
			}
			?>
			</div>
		</div>
		<div id='center'>
		<div id="calendar_div">
		<?php
				
			echo getCalender($_SESSION['group_viewing']);
		
		?>
		</div>
		<div id='menu'>
			<ul id='items'>
			  <li>Ajouter une disponibilité</li>
			</ul>
		</div>
		<div id='resultat'>
		</div>
		</div>
		<div id='right'>
		<section>
			<h2>Le code couleur</h2>
			<table>
				<tr>
				<td class="color_menu">Moi</td><td><input type="color" id='my_color' value="<?php echo $_SESSION['color']; ?>" name='color'/></td>
				</tr>
		<?php
			echo getEveryoneColor($_SESSION['group_viewing'], $_SESSION['id']);
		?>
			</table>
		</section>
		<section>
			<h2>Ajouter une disponibilité</h2>
				<table>
					<tr>
						<td>Début</td>
						<td><input type='date' placeholder='Date de début' id='start_date'></td>
					</tr>
					<tr>
						<td>Fin</td>
						<td><input type='date' placeholder='Date de fin' id='end_date'></td>
					</tr>
					<tr>
						<td class="error"><?php if(isset($error)){ echo $error; } ?></td>
					</tr>
					<tr>
						<td colspan = '2'><input type='submit' value='Ajouter' id='add_dispo'></td>
					</tr>
				</table>
		</section>
		<section>
			<h2>Supprimer une disponibilité</h2>
				<table>
					<tr>
						<td>Début</td>
						<td><input type='date' placeholder='Date de début' id='start_date_supp'></td>
					</tr>
					<tr>
						<td>Fin</td>
						<td><input type='date' placeholder='Date de fin' id='end_date_supp'></td>
					</tr>
					<tr>
						<td class="error"><?php if(isset($error_supp)){ echo $error_supp; } ?></td>
					</tr>
					<tr>
						<td colspan = '2'><input type='submit' value='Supprimer' id='supp_dispo'></td>
					</tr>
				</table>
		</section>
		</div>
	</div>
	<script type="text/javascript">

$(function() {		
		
		var d = new Date();
		var currentDate = d.getFullYear() + "-" + (d.getMonth()+1) + "-" + d.getDate();
		
		//Ajouter une disponibilité
		$('#add_dispo').on('click', function(){
		  var start = new Date($('#start_date').val());
		  var day_start = start.getDate();
		  var month_start = start.getMonth() + 1;
		  var year_start = start.getFullYear();
		  var time_start = year_start+'-'+month_start+'-'+day_start;
		  
		  var end = new Date($('#end_date').val());
		  var day_end = end.getDate();
		  var month_end = end.getMonth() + 1;
		  var year_end = end.getFullYear();
		  var time_end = year_end+'-'+month_end+'-'+day_end;
		  
			if(compareTime(time_start, currentDate)){
				if(compareTime(time_end, time_start)){
					window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?groupe_id=<?php echo $_SESSION['group_viewing']; ?>&add_dispo&start_date=" + time_start + "&end_date=" + time_end; 
				}else{
					alert('Veuillez sélectionner une date de fin de disponibilité ultérieure à la date de début');
				}
			}else{
				alert('Veuillez sélectionner une date de début de disponibilité ultérieure à la date d\'aujourd\'hui');
			}
		});
	
		//Supprimer une disponibilité
		$('#supp_dispo').on('click', function(){
		  var start = new Date($('#start_date_supp').val());
		  var day_start = start.getDate();
		  var month_start = start.getMonth() + 1;
		  var year_start = start.getFullYear();
		  var time_start = year_start+'-'+month_start+'-'+day_start;
		  
		  var end = new Date($('#end_date_supp').val());
		  var day_end = end.getDate();
		  var month_end = end.getMonth() + 1;
		  var year_end = end.getFullYear();
		  var time_end = year_end+'-'+month_end+'-'+day_end;
		  
			if(compareTime(time_start, currentDate)){
				if(compareTime(time_end, time_start)){
					window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?groupe_id=<?php echo $_SESSION['group_viewing']; ?>&supp_dispo&start_date=" + time_start + "&end_date=" + time_end; 
				}else{
					alert('Veuillez sélectionner une date de fin ultérieure à la date de début');
				}
			}else{
				alert('Veuillez sélectionner une date de début ultérieure à la date d\'aujourd\'hui');
			}
		});
	
		//Comparer deux dates
		function compareTime(time1, time2) { //format : YYYY-MM-DD
			return new Date(time1) >= new Date(time2); // true if time1 is later
		}
		
		//Changer ma couleur
		$('#my_color').change(function(){
			var color = $(this).val();
			$.ajax({
                type:'POST',
                url:'<?php echo $_SERVER['REQUEST_URI']; ?>',
                data:'color='+color,
			});
		});
		
		//Selectionner avec click droit
		$('.selected').bind("contextmenu",function(e){
		  e.preventDefault();
		  console.log(e.pageX + "," + e.pageY);
		  $("#menu").css("left",e.pageX);
		  $("#menu").css("top",e.pageY);
		 // $("#menu").hide(100);        
		  $("#menu").fadeIn(200,startFocusOut());      
		});

		function startFocusOut(){
		  $('.selected').on("click",function(){
		  $("#menu").hide();        
		  $('.selected').off("click");
		  });
		}

		$("#items > li").click(function(){
		$("#resultat").text("You have selected "+$(this).text());
		});
});
	</script>
 <script src="http://code.jquery.com/jquery.js"></script>
 <?php
	}else{
		echo '<p class="error">Oups... Il semblerait que vous ne soyez pas membre de ce groupe</p>';
	}
	?>
</body>
</html>