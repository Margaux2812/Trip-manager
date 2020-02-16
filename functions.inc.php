<?php
/*Retourne si le mot de passe
et l'email correspondent*/

function dontmatch($mail, $pass){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
		
	$reponse = $bdd->query('SELECT * FROM personne WHERE mail=\''.$mail.'\'');
	$donnees = $reponse->fetch();

	if(password_verify($pass, $donnees['pass'])){
		$return = 0;
	}else{
		$return = 1;
	}
	
	$reponse->closeCursor();
	return $return;
}	

/*Vérifie que l'adresse email
n'est pas dans la base de donnée*/

function already_exists($mail){
	
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT * FROM personne WHERE mail = '".$mail."'");
	$donnees = $reponse->fetch();
	
	if(is_bool($donnees)){
		$return = 0;
	}else{
		$return = 1;
	}
	$reponse->closeCursor();
	return $return;
}

/*Vérifie si cette personne est dans
le groupe*/
function ingroup($id_personne, $id_groupe){
	
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
		
	$reponse = $bdd->query('SELECT membres FROM groupe WHERE id=\''.$id_groupe.'\'');
	$donnees = $reponse->fetch();
	
	$membres = json_decode($donnees['membres']);
	
	if(is_array($membres) && in_array($id_personne, $membres)){
		return 1;
	}else{
		return 0;
	}
	$reponse->closeCursor();
}

/*Transforme un array php en array JAVA*/

function phpToJava($array){
	if(is_array($array)){
		$result = '[';
		for($i=0; $i<(count($array)-1); $i++){
			$result .= $array[$i].',';
		}
		$result .= $array[count($array)-1].']';
	}else{
		$result = '['.$array.']';
	}
	return $result;
}

/*Retourne la liste d'amis*/

if(isset($_POST['friends']) && !empty($_POST['friends'])){
    switch($_POST['friends']){
        case 'getListFriend':
		/*On a reçu un string délimité par des , donc on le transforme en array puis en JSON*/
		
			$new_list = explode(',', $_POST['list']);

            getListFriend(json_encode($new_list), $_POST['ordre'],$_POST['format']);
            break;
        default:
            break;
    }
}

function getListFriend($list, $ordre='nom', $format='List'){
	
	$list_amis = '';
	
	/*$list est un JSON donc on le decode puis on en fait un string délimité par des , */
	$list_array = json_decode($list);
	for($i=0; $i<(count($list_array)-1); $i++){
		$list_amis .= $list_array[$i].',';
	}
	$list_amis .= $list_array[count($list_array)-1];
	

	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}
	catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query('SELECT * FROM personne WHERE id IN ('.$list_amis.') ORDER BY '.$ordre.';');

	if(!is_bool($reponse)){
		
		echo '<table class="tableHoleList">';
		$compteur = 0;
		while ($donnees = $reponse->fetch()){
			if($format == 'List'){
				echo '<tr>
						<td>'.getList($donnees).'</td>
						</tr>';
			}else{
				if(($compteur%2)==0){
					echo '<tr>
						<td>'.getSquare($donnees).'</td>';
				}else{
					echo '<td>'.getSquare($donnees).'</td>
						</tr>';
				}
				$compteur++;
			}
		}
			echo '</table>';
			
			$reponse->closeCursor(); // Termine le traitement de la requête
	}
?>	
	<script>
	function  getListFr(list,ordre, format){

        $.ajax({
                type:'POST',
                url:'../functions.inc.php',
                data:'friends=getListFriend&list='+list+'&ordre='+ordre+'&format='+format,
				success:function(html){
				$('#listOfFriends>.tableHoleList').html(html);
				}
        });
       }
	$('.ordre_dropdown').on('change',function(){
	/*On transforme $list qui était un JSON en array PHP puis en array JAVA*/
        getListFr(<?php echo phpToJava(json_decode($list)); ?>, $('.ordre_dropdown').val(), "<?php echo $format; ?>");
    });
	$('.format_button').click(function(){
	/*On transforme $list qui était un JSON en array PHP puis en array JAVA*/
        getListFr(<?php echo phpToJava(json_decode($list)); ?>, "<?php echo $ordre; ?>", $(this).attr('id'));
    });
</script>
<?php
}

/*On fusionne deux range de date*/

function fusionnerDate($debut1, $fin1, $debut2, $fin2){
	/*On a directement les dates au format YYYY-MM-DD*/
	/*Si la date de début de fin de disponibilité 
	est avant la date de début de disponibilité, 
	alors on doit regarder où se situe la fin*/
	if(strtotime($debut2)<=strtotime($debut1)){
		/*Si la date de fin de fin de disponibilité 
		est avant la date de fin de disponibilité, 
		alors il reste le segment $fin2 +1jour 
		jusqu'à $fin1*/
		if(strtotime($fin2)<strtotime($fin1)){
			/*On doit avoir MM/DD/YYYY*/
			$morceaux = explode('-', $fin2);
			$fin22 = $morceaux[1].'/'.$morceaux[2].'/'.$morceaux[0];
			
			$newfin2 = date('Y-m-d', strtotime($fin22.' +1 days'));
			$dates = $newfin2.';'.$fin1;
		}else{
			$dates = '';
		}
		
	}else{
		/*La date de début de fin de disponibilité se 
		situe après la date de début de disponibilité 
		donc on a déjà un premier segment qui va de la 
		date debut1 à debut2 - 1 jour*/
		/*On doit avoir MM/DD/YYYY*/
			$morceaux = explode('-', $debut2);
			$debut22 = $morceaux[1].'/'.$morceaux[2].'/'.$morceaux[0];
			
			$newdebut2 = date('Y-m-d', strtotime($debut22.' -1 days'));
		
		$dates = $debut1.';'.$newdebut2;
		/*Si la date de fin de fin de disponibilité 
		est avant la date de fin de disponibilité, 
		alors il reste le segment $fin2 +1jour 
		jusqu'à $fin1*/
		if(strtotime($fin2)<strtotime($fin1)){
			/*On doit avoir MM/DD/YYYY*/
			$morceaux = explode('-', $fin2);
			$fin22 = $morceaux[1].'/'.$morceaux[2].'/'.$morceaux[0];
			
			$newfin2 = date('Y-m-d', strtotime($fin22.' +1 days'));
			
			$segment2 = $newfin2.';'.$fin1;
			$dates = $dates.'+'.$segment2;
		}
	}

	return $dates;
}

/*Regarder si deux personnes sont dans un même groupe*/

function areTogether($id1, $id2){
	$result = array();
	
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT id FROM groupe WHERE json_contains(membres,'\"".$id1."\"') AND json_contains(membres,'\"".$id2."\"')");
	
	if(!is_bool($reponse)){
		while ($groupes = $reponse->fetch()){
			array_push( $result, $groupes['id']);
		}
	}
	$reponse->closeCursor();
	
	return $result;
}
/*************************************************************************************************************************La photo de profil*/




/*On vérifie que le fichier
donné soit de type jpg, jpeg,
gif ou png*/

function isnotvalid($picture){
	if ($picture['error'] == 0){
		// Testons si le fichier n'est pas trop gros
		if ($picture['size'] <= 1000000){
			if(preg_match('#image#', $picture['type'])){	
					// Testons si l'extension est autorisée
					$infosfichier = pathinfo(htmlspecialchars($picture['name']));
					$extension_upload = $infosfichier['extension'];
					$extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png');
					
				if (in_array($extension_upload, $extensions_autorisees)){
					return FALSE;
				}else{
					return TRUE;
				}
			}else{
				return TRUE;
			}
		}
		else{
			return TRUE;
		}
	}else{
		return TRUE;
	}
}

/*On retaille un photo en
respectant son ratio*/

function resize($img){
	$dimensions = getimagesize($img);

	$ratio_w = $dimensions[0] / 150; // 150 est la largeur maximale et la hauteur maximale
	$ratio_h = $dimensions[1] / 150;

	if ($ratio_w > $ratio_h)
	{
		$newh = round($dimensions[1] / $ratio_w);
		$neww = 110;
	}
	else
	{
		$neww = round($dimensions[0]/ $ratio_h);
		$newh = 142;
	}
	 $new_dimensions = array(
		'width' => $neww,
		'height' =>$newh
	 );
	 
	 return $new_dimensions;
}

/*On upload une photo dans le
dossier 'ressources' puis on
update dans la base de donnée
le chemin d'accès à la photo*/

function upload($newfile, $id){
	
	/*On prend le nom de la photo pour recuperer son extension*/

	$infosfichier = pathinfo(htmlspecialchars($newfile['name']));
	$extension_upload = $infosfichier['extension'];
	
	$img_path = 'ressources/'.$id.'.'.$extension_upload;
	
	/*On crée une nouvelle image où nous allons copier notre image redimensionnée*/
	$uploadedfile  = $newfile['tmp_name'];
	if($extension_upload=="jpg" || $extension_upload=="jpeg" ){
		$src = imagecreatefromjpeg($uploadedfile);
	}else if($extension_upload=="png"){
		$src = imagecreatefrompng($uploadedfile);
	}else {
		$src = imagecreatefromgif($uploadedfile);
	}
	
	/*On prend nos deux dimensions (déprat, arrivée)*/
	list($width,$height)=getimagesize($uploadedfile);
	$new_dimensions = resize($uploadedfile);
	$tmp=imagecreatetruecolor($new_dimensions['width'],$new_dimensions['height']);
	imagecopyresampled($tmp,$src,0,0,0,0,$new_dimensions['width'],$new_dimensions['height'], $width,$height);
	imagejpeg($tmp,$img_path,100);
	
	imagedestroy($src);
	imagedestroy($tmp);

	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$bdd->exec('UPDATE personne SET photo=\''.$img_path.'\' WHERE id = "'.$id.'"');	
	
	return $img_path;
}




/*************************************************************************************************************************Getters*/




function getGroupe($id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}
	
	$reponse = $bdd->query("SELECT groupe FROM personne WHERE id = '".$id."'");
	
	/*L'inviteur reçoit une notif*/
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$groupe = json_decode($donnees['groupe'], TRUE);
		$reponse->closeCursor();
		
		return $groupe;
	}
}

function getAmis($id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}
	
	$reponse = $bdd->query("SELECT amis FROM personne WHERE id = '".$id."'");
	
	/*L'inviteur reçoit une notif*/
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$amis = json_decode($donnees['amis'], TRUE);
		$reponse->closeCursor();
		
		return $amis;
	}
}

function getNotifications($id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT notifs FROM personne WHERE id = '".$id."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$notifs = json_decode($donnees['notifs'], TRUE);
		$reponse->closeCursor();
		
		if(is_array($notifs)){
			$result = '<ul>';
			for($i=0; $i<count($notifs); $i++){
				$result .= '<li>'.html_entity_decode(html_entity_decode($notifs[$i])).'</li>';
			}
			$result .= '</ul>';
		}else{
			$result = 'Vous n\'avez aucune notification.';
		}
		
		return $result;
	}else{
		return 'Nous n\'avons pas trouvé ce profil.';
	}

}

function getNomGroupe($groupe){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT * FROM groupe WHERE id = '".$groupe."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$return = $donnees['nom'];
		
		return $return;
	}else{
		return 'Nous n\'avons pas trouvé ce groupe.';
	}
	$reponse->closeCursor();
}

function getCreator($groupe, $key){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT activites FROM groupe WHERE id = '".$groupe."'");
	$infos = $reponse->fetch();
	$activites = json_decode($infos['activites'], TRUE);
	$reponse->closeCursor();
	return getNom($activites[$key]['creator']);
}

function getNom($id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT * FROM personne WHERE id = '".$id."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$return = $donnees['nom'].' '.ucfirst(strtolower($donnees['prenom']));
		
		return $return;
	}else{
		return 'Nous n\'avons pas trouvé ce profil.';
	}
	$reponse->closeCursor();
}

function getActivites($id, $groupe){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}
	
	$reponse = $bdd->query('SELECT activites FROM groupe WHERE id="'.$groupe.'"');
	$infos = $reponse->fetch();
	
	$activites = json_decode($infos['activites'], TRUE);

	if(is_array($activites)){
		$noms_activites = array_keys($activites);
		
		for($i=0; $i<count($noms_activites); $i++){
			$stars = $activites[$noms_activites[$i]][$id];
			$result[$noms_activites[$i]] = $stars;
		}
		
		return $result;
	}else{
		return null;
	}
}

function getList($personne){
	if(empty($personne['photo'])){
		$photo='images/profile.png';
	}else{
		$photo = $personne['photo'];
	}
	
	$result = '	<table class=\'listed\'>
					<tr>
						<td><img alt=\'profile_picture\' src="'.$photo.'"></td>
						<td><a href=\'personne.php?id='.$personne['id'].'\' title="Voir le profil"><strong>'.$personne['nom'].'</strong> '.ucfirst(strtolower($personne['prenom'])).'</a></td>
					</tr>
				</table>
				';
	return $result;
}

function getSquare($personne){
	if($personne['photo'] != null){
		$photo = $personne['photo'];
	}else{
		$photo = 'images/profile.png';
	}
	$result = '	<table class=\'listed\'>
					<tr>
						<td><img alt=\'profile_picture\' src="'.$photo.'"</td>
					</tr>
					<tr>
						<td><strong>'.$personne['nom'].'</strong> '.ucfirst(strtolower($personne['prenom'])).'</td></tr>
					<tr>
						<td class="mail">'.$personne['mail'].'</td></tr>';
	$result .='
				</table>
				';
	return $result;
}

function getFiche($id, $amis){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT * FROM personne WHERE id = '".$id."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$return = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="fiche">
		<table class="tableHole">
			<tr><td>'.getSquare($donnees).'</td></tr>
			<tr><td>';
		if((is_array($amis) && !in_array($id, $amis)) || ($amis==null)){
			$return .= '<input type="submit" name="add" id="add">';
		}else{
			$return .= '<input type="submit" name="delete" id="delete">';
		}
		$return .= '</td></tr>
		</table>
		</form>';
		
		return $return;
	}else{
		return 'Nous n\'avons pas trouvé ce profil.';
	}
}

function getEveryoneColor($groupe, $id){
	$return = '';
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
		
	$reponse = $bdd->query('SELECT couleur FROM groupe WHERE id="'.$groupe.'"');
	$donnees = $reponse->fetch();
	$array = json_decode($donnees['couleur'], TRUE);

	if(is_array($array)){
		foreach($array as $key => $value){
			if(($key == $id) !=1){
				$id_personne = $key;
				$couleur = $value;
				$reponse2 = $bdd->query('SELECT nom, prenom FROM personne WHERE id="'.$id_personne.'"');
				$infos = $reponse2->fetch();
				
				$return .= '<tr><td class="color_menu">'.ucfirst(strtolower($infos['prenom'])).' '.$infos['nom'].'</td><td><input type="color" value="'.$couleur.'" disabled="disabled" /> </td></tr>';
			}
		}
	}
	
	return $return;
}

function getColor($groupe, $id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
		
	$reponse = $bdd->query('SELECT couleur FROM groupe WHERE id="'.$groupe.'"');
	$donnees = $reponse->fetch();
	
	$array = json_decode($donnees['couleur'], TRUE);

	if(is_array($array)){
		return $array[$id];
	}
}




/*************************************************************************************************************************Setters*/




function setChamp($nom, $groupe, $id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}
	
	$reponse = $bdd->query('SELECT activites, membres FROM groupe WHERE id="'.$groupe.'"');
	$infos = $reponse->fetch();

	$activites = json_decode($infos['activites'], TRUE);
	$membres = json_decode($infos['membres']);

	if(is_array($activites)){
		/*Si on avait déjà des activités dans ce groupe*/
		if(array_key_exists($nom, $activites)){
			return 'Cette activité existe déjà dans ce groupe';
		}else{
				$activites[$nom]['creator'] = $id;
			for($i=0; $i<count($membres); $i++){
				$activites[$nom][$membres[$i]] = 0;
			}
		}
	}else{
		$activites[$nom]['creator'] = $id;
		/*On crée l'array et pour chaque membre on initialise à 0*/
		for($i=0; $i<count($membres); $i++){
			$activites[$nom][$membres[$i]] = 0;
		}
	}
	
	
	$bdd->exec('UPDATE groupe SET activites=\''.json_encode($activites).'\' WHERE id = "'.$groupe.'"');

	return 'Cette activité a été ajouté avec succès';	
}

function setDisponibilites($id, $groupe, $start_date, $end_date){
	/*On a les dates au format YYYY-MM-DD*/
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}

	/*Unicité d'évènement par personne. On prend les évènements de cette personne pour ce groupe et on ajoute les dates*/
	$reponse = $bdd->query("SELECT * FROM events WHERE title= 'Disponibilité de ".$id."' AND groupe='".$groupe."' ");
	$donnees = $reponse->fetch();
	
	$currentDate = date('Y').'-'.date('m').'-'.date('d');
	
	
	if(is_bool($donnees) || ($donnees['date'] == '')){
		$date = $start_date.';'.$end_date;
	
		/*Créer un évènement car la personne n'avait jamais mis ses disponibilités*/
		$bdd->exec('INSERT INTO events(groupe, title, date, created, modified) VALUES(\''.$groupe.'\', \'Disponibilité de '.$id.'\', \''.$date.'\', \''.$currentDate.'\',\''.$currentDate.'\')');
	}else{
		$date = $donnees['date'].'+'.$start_date.';'.$end_date;
		/*On a déjà un évènement lié à cette personne pour ce groupe*/
		$bdd->exec('UPDATE events SET date=\''.$date.'\', modified = \''.$currentDate.'\' WHERE title= "Disponibilité de '.$id.'" AND groupe = "'.$groupe.'"');	
	
	}
		$reponse2 = $bdd->query("SELECT * FROM events WHERE title= 'Disponibilité de ".$id."' AND date = '".$date."' AND created = '".$currentDate."'");
		$donnees = $reponse2->fetch();
		$id_event = $donnees['id'];
		$reponse2->closeCursor();
		
	$reponse->closeCursor();	
	
	
	/*Ajouter l'évènement au groupe*/
	$reponse = $bdd->query("SELECT events FROM groupe WHERE id='".$groupe."';");
	$donnees = $reponse->fetch();
	
	$events_du_groupe = json_decode($donnees['events']);
	
	if(is_array($events_du_groupe) && !in_array($id_event, $events_du_groupe)){
		array_push($events_du_groupe, $id_event);
	}else{
		$events_du_groupe = array($id_event);
	}
	
	$json_events = json_encode($events_du_groupe);

	$bdd->exec('UPDATE groupe SET events=\''.$json_events.'\' WHERE id='.$groupe.'');	
	$reponse->closeCursor();
		
}

function setColor($groupe, $id, $color){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}
	
	$reponse = $bdd->query('SELECT couleur FROM groupe WHERE id="'.$groupe.'"');
	$donnees = $reponse->fetch();

	$array = json_decode($donnees['couleur'], TRUE);
	if(is_array($array)){
		$array[$id] = $color;
	}

	$couleur = json_encode($array);

	$bdd->exec('UPDATE groupe SET couleur=\''.$couleur.'\' WHERE id = "'.$groupe.'"');	
	
	$reponse->closeCursor();
}




/*************************************************************************************************************************Notifications*/




/*On cherche une notification 
indicative afin de pouvoir la
fermer*/
function findNotif($notif, $id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}
	$reponse = $bdd->query("SELECT notifs FROM personne WHERE id = '".$id."'");
	
	/*L'inviteur reçoit une notif*/
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$notifs = json_decode($donnees['notifs'], TRUE);
		$reponse->closeCursor();
		
		if(search($notif, $notifs) >=0){
			$key = search($notif, $notifs);
			if(count($notifs)>1){
				for($i=$key; $i<(count($notifs)-1); $i++){
					$notifs[$i] = $notifs[$i+1];
				}
				unset($notifs[(count($notifs)-1)]);
			}else{
				$notifs = null;
			}
			
			$bdd->exec('UPDATE personne SET notifs=\''.json_encode($notifs).'\' WHERE id = "'.$id.'"');	
		}
	}
}

/*On cherche dans l'array des
notifications avec htmlentities()
devant chaque value de l'array*/

function search($notif, $array){
	for($i=0; $i<count($array); $i++){
		if($notif == htmlentities($array[$i])){
			$key = $i;
			return $key;
		}
	}
}

/*On supprime une notification qui
était sous forme de question (demande
d'ami, de rejoindre un groupe,etc...)*/

function removeNotif($nbNotif, $id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT notifs FROM personne WHERE id = '".$id."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$notifs = json_decode($donnees['notifs'], TRUE);
		$reponse->closeCursor();
		
		if(is_array($notifs)){
			if(count($notifs) > 1){
				for($i=$nbNotif; $i<count($notifs); $i++){
					$notifs[$i] = $notifs[$i+1];
				}
				unset($notifs[count($notifs)-1]);
			}else{
				$notifs = null;
			}
			$bdd->exec('UPDATE personne SET notifs=\''.json_encode($notifs).'\' WHERE id = "'.$id.'"');	
		}
	}
}


/****************************************************************************Les amis*/




/*On demande quelqu'un en ami*/

function sendInvitation($id_receveur, $id_demandeur){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	

	$reponse = $bdd->query("SELECT notifs FROM personne WHERE id = '".$id_receveur."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$notifs = json_decode($donnees['notifs'], TRUE);
		$reponse->closeCursor();
		
		/*On convertit le code HTML en string grâce à htmlentities*/
		if(is_array($notifs)){
			$notifs[count($notifs)] = htmlentities('<a href="/calendar/accounts/personne.php?id='.$id_demandeur.'">'.getNom($id_demandeur).'</a> souhaite être votre ami<br/><a href="/calendar/accounts/mon_profil.php?addfriend=YES&id='.$id_demandeur.'&num_notif='.count($notifs).'">Accepter</a><a href=/calendar/accounts/mon_profil.php?addfriend=NO&id='.$id_demandeur.'&num_notif='.count($notifs).'">Refuser</a>');
		}else{
			$notifs[0] = htmlentities('<a href="/calendar/accounts/personne.php?id='.$id_demandeur.'">'.getNom($id_demandeur).'</a> souhaite être votre ami<br/><a href="/calendar/accounts/mon_profil.php?addfriend=YES&id='.$id_demandeur.'&num_notif=0">Accepter</a><a href="/calendar/accounts/mon_profil.php?addfriend=NO&id='.$id_demandeur.'&num_notif=0">Refuser</a>');
		}

		$bdd->exec('UPDATE personne SET notifs=\''.json_encode($notifs).'\' WHERE id = "'.$id_receveur.'"');	

		return 'Votre demande d\'ami a été envoyée';
	}else{
		return 'Nous n\'avons pas trouvé ce profil.';
	}
}

/*On refuse une demande d'ami. 
On envoie donc une notification
à l'id_demandeur comme quoi sa
demande a été rejetée*/

function rejectFriend($id_demandeur, $id_receveur){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT notifs FROM personne WHERE id = '".$id_demandeur."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$notifs = json_decode($donnees['notifs'], TRUE);
		$reponse->closeCursor();
		
		$message = '<button class="closeButton">x</button>'.getNom($id_receveur).' a décliné votre demande d&apos;ami';
		
		if(is_array($notifs)){
			array_push($notifs, htmlentities(htmlentities($message), ENT_QUOTES));
		}else{
			$notifs[0] = htmlentities(htmlentities($message), ENT_QUOTES);
		}
		$bdd->exec('UPDATE personne SET notifs=\''.json_encode($notifs).'\' WHERE id = "'.$id_demandeur.'"');	
	}
}

/*On a accepté la demande d'ami.*/

function addFriend($id_demandeur, $id_receveur){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	/*On ajoute le demandeur aux amis du receveur*/
	$reponse = $bdd->query("SELECT amis FROM personne WHERE id = '".$id_receveur."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$amis = json_decode($donnees['amis']);
		$reponse->closeCursor();
		
		/*On convertit le code HTML en string grâce à htmlentities*/
		if(is_array($amis)){
			$amis[count($amis)] = $id_demandeur;
		}else{
			$amis[0] = $id_demandeur;
		}

		$bdd->exec('UPDATE personne SET amis=\''.json_encode($amis).'\' WHERE id = "'.$id_receveur.'"');	

	}else{
		return 'L\'ajout a échoué.';

	}	
	/*On ajoute le receveur aux amis du demandeur et on envoie un notification*/
	$reponse = $bdd->query("SELECT amis, notifs FROM personne WHERE id = '".$id_demandeur."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$amis = json_decode($donnees['amis']);
		$notifs = json_decode($donnees['notifs']);
		$reponse->closeCursor();
		
		$message = '<button class="closeButton">x</button>'.getNom($id_receveur).' a accepté votre demande d$apos;ami.';
		
		/*On convertit le code HTML en string grâce à htmlentities*/
		if(is_array($amis)){
			$amis[count($amis)] = $id_receveur;
		}else{
			$amis[0] = $id_receveur;
		}
		
		if(is_array($notifs)){
			array_push($notifs, htmlentities(htmlentities($message, ENT_QUOTES), ENT_QUOTES));
		}else{
			$notifs[0] = htmlentities(htmlentities($message, ENT_QUOTES), ENT_QUOTES);
		}

		$bdd->exec('UPDATE personne SET amis=\''.json_encode($amis).'\', notifs=\''.json_encode($notifs).'\' WHERE id = "'.$id_demandeur.'"');	
	/*Envoyer la notification*/
	}else{
		return 'L\'ajout a échoué.';
	}
	
	return 'Vous êtes maintenant ami avec '.getNom($id_demandeur).' !';
}

/*On supprime un ami*/

function deleteFriend($id_ami, $mon_id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}
	
	$reponse = $bdd->query("SELECT amis FROM personne WHERE id = '".$mon_id."'");
	
	/*Je l'enleve de mes amis*/
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$amis = json_decode($donnees['amis'], TRUE);
		$reponse->closeCursor();
		
		if(in_array($id_ami, $amis) && ($amis != null)){
			
			$key = array_search($id_ami, $amis);
			
			if(count($amis)>1){
				for($i=$key; $i<count($amis); $i++){
					$amis[$i] = $amis[$i+1];
				}
				unset($amis[(count($amis)-1)]);
			}else{
				$amis = null;
			}
			
			$bdd->exec('UPDATE personne SET amis=\''.json_encode($amis).'\' WHERE id = "'.$mon_id.'"');	
		}
	}
	
	$reponse = $bdd->query("SELECT amis FROM personne WHERE id = '".$id_ami."'");
	
	/*Je m'enleve de sa liste d'amis*/
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$amis = json_decode($donnees['amis'], TRUE);
		$reponse->closeCursor();
		
		if(in_array($mon_id, $amis) && is_array($amis)){
			
			$key = array_search($mon_id, $amis);
			
			if(count($amis)>1){
				for($i=$key; $i<count($amis); $i++){
					$amis[$i] = $amis[$i+1];
				}
				unset($amis[(count($amis)-1)]);
			}else{
				$amis = null;
			}
			
			$bdd->exec('UPDATE personne SET amis=\''.json_encode($amis).'\' WHERE id = "'.$id_ami.'"');	
		}
	}
}

/****************************************************************************Les groupes*/




/*Je rejoins le groupe dans lequel
on m'a invité*/

function joinGroup($groupe, $id_parrain, $id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT notifs FROM personne WHERE id = '".$id_parrain."'");
	
	/*L'inviteur reçoit une notif*/
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$notifs = json_decode($donnees['notifs'], TRUE);
		$reponse->closeCursor();
		
		$message = '<button class="closeButton">x</button><a href="personne.php?id='.$id.'">'.getNom($id).'</a> a accepté votre invitation concernant le groupe '.getNomGroupe($groupe).'.';
			
		if(is_array($notifs)){
			array_push($notifs, htmlentities($message));
		}else{
			$notifs[0] = htmlentities($message);
		}

		$bdd->exec('UPDATE personne SET notifs=\''.json_encode($notifs).'\' WHERE id = "'.$id_parrain.'"');	
	}
	
	$reponse = $bdd->query("SELECT membres FROM groupe WHERE id = '".$groupe."'");
	
	/*on ajoute au groupe le membre*/
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$membres = json_decode($donnees['membres'], TRUE);
		$reponse->closeCursor();
	
		if(is_array($membres)){
			array_push($membres, $id);
		}
		
		$bdd->exec('UPDATE groupe SET membres =\''.json_encode($membres).'\' WHERE id = "'.$groupe.'"');	
	}
	
	$reponse = $bdd->query("SELECT couleur FROM groupe WHERE id = '".$groupe."'");
	
	/*on ajoute au groupe une couleur aléatoire pour le membre*/
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$couleurs = json_decode($donnees['couleur'], TRUE);
		$reponse->closeCursor();
	
		if(is_array($couleurs)){
			$color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
			array_push($couleurs, $color);
		}
		
		$bdd->exec('UPDATE groupe SET couleur =\''.json_encode($couleurs).'\' WHERE id = "'.$groupe.'"');	
	}
	
	$reponse = $bdd->query("SELECT activites FROM groupe WHERE id = '".$groupe."'");
	
	/*on met toutes les activités à 0 étoiles*/
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$activites = json_decode($donnees['activites'], TRUE);
		print_r($activites);
		$reponse->closeCursor();
	
		if(is_array($activites)){
			echo '<br>coucou';
			$noms_activites = array_keys($activites);
		
			for($i=0; $i<count($noms_activites); $i++){
				$activites[$noms_activites[$i]][$id] = 0;
			}
		}
		echo '<br>';
		print_r($activites);
		$bdd->exec('UPDATE groupe SET activites =\''.json_encode($activites).'\' WHERE id = "'.$groupe.'"');	
	}
	
	/*On ajout le groupe aux groupes de l'id*/
	$reponse = $bdd->query("SELECT groupe FROM personne WHERE id = '".$id."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$groupes = json_decode($donnees['groupe'], TRUE);
		$reponse->closeCursor();
	
		if(is_array($groupes)){
			array_push($groupes, $groupe);
		}else{
			$groupes[0] = $groupe;
		}
		
		$bdd->exec('UPDATE personne SET groupe =\''.json_encode($groupes).'\' WHERE id = "'.$id.'"');	
	}
}

/*Je ne rejoins pas le groupe
dans lequel on m'a invité*/

function rejectGroup($groupe, $id_parrain, $id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT notifs FROM personne WHERE id = '".$id_parrain."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$notifs = json_decode($donnees['notifs'], TRUE);
		$reponse->closeCursor();
		
		$message = '<button class="closeButton">x</button><a href="personne.php?id='.$id.'">'.getNom($id).'</a> a refusé votre invitation concernant le groupe '.getNomGroupe($groupe).'.';
			
		if(is_array($notifs)){
			array_push($notifs, htmlentities($message));
		}else{
			$notifs[0] = htmlentities($message);
		}

		$bdd->exec('UPDATE personne SET notifs=\''.json_encode($notifs).'\' WHERE id = "'.$id_parrain.'"');	
	}
}

/*L'id_demandeur invite id_receveur 
dans un groupe auquel il appartient*/

function sendInvitationGroup($id, $groupe, $id_demandeur){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT notifs FROM personne WHERE id = '".$id."'");
	
	if(!is_bool($reponse)){
		$donnees = $reponse->fetch();
		$notifs = json_decode($donnees['notifs'], TRUE);
		$reponse->closeCursor();
		
		if(is_array($notifs)){
			$message = '<a href="personne.php?id='.$id_demandeur.'">'.getNom($id_demandeur).'</a> vous a invité à rejoindre son groupe "'.getNomGroupe($groupe).'"<br><a href="mon_profil.php?joingroup=YES&gr='.$groupe.'&parrain='.$id_demandeur.'&num_notif='.count($notifs).'">Accepter</a><a href="mon_profil.php?joingroup=NO&gr='.$groupe.'&parrain='.$id_demandeur.'&num_notif='.count($notifs).'">Refuser</a>';
		
			array_push($notifs, htmlentities($message));
		}else{
			$message = '<a href="personne.php?id='.$id_demandeur.'">'.getNom($id_demandeur).'</a> vous a invité à rejoindre son groupe "'.getNomGroupe($groupe).'"<br><a href="mon_profil.php?joingroup=YES&gr='.$groupe.'&parrain='.$id_demandeur.'&num_notif=0">Accepter</a><a href="mon_profil.php?joingroup=NO&gr='.$groupe.'&parrain='.$id_demandeur.'&num_notif=0">Refuser</a>';
		
			$notifs[0] = htmlentities($message);
		}

		$bdd->exec('UPDATE personne SET notifs=\''.json_encode($notifs).'\' WHERE id = "'.$id.'"');	
	}
}

/*Envoie d'une notification au créateur
du groupe quand quelqu'un souhaite le
rejoindre*/

function sendNotificationAdmin($groupe, $demandeur_id){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
	
	$reponse = $bdd->query("SELECT creator FROM groupe WHERE id = '".$groupe."'");
	$donnees = $reponse->fetch();
	$admin = $donnees['creator'];
	$reponse->closeCursor();
	
	$reponse = $bdd->query("SELECT * FROM personne WHERE id = '".$admin."'");
	$donnees = $reponse->fetch();
	
	$notifs = json_decode($donnees['notifs'], TRUE);
	$reponse->closeCursor();
	if(is_array($notifs)){
		$notifs[count($notifs)] = getNom($demandeur_id).' souhaite joindre votre groupe '.getNomGroupe($groupe).'<br><a href="'.$_SERVER['PHP_SELF'].'?joingroup='.$groupe.'&member='.$demandeur_id.'>Accepter</a><a href="'.$_SERVER['PHP_SELF'].'?rejectgroup='.$groupe.'&member='.$demandeur_id.'>Refuser</a>';
	}else{
		$notifs[0] = getNom($demandeur_id).' souhaite joindre votre groupe '.getNomGroupe($groupe).'<br><a href="'.$_SERVER['PHP_SELF'].'?joingroup='.$groupe.'&member='.$demandeur_id.'>Accepter</a><a href="'.$_SERVER['PHP_SELF'].'?rejectgroup='.$groupe.'&member='.$demandeur_id.'>Refuser</a>';
	}
	
	$bdd->exec('UPDATE personne SET notifs=\''.json_encode($notifs).'\' WHERE id = "'.$admin.'"');	

}




/*************************************************************************************************************************Les UPDATE*/




/*On update les étoiles dans un champ*/

function updateStars($value, $id, $key, $groupe){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}
	
	$reponse = $bdd->query('SELECT activites FROM groupe WHERE id="'.$groupe.'"');
	$infos = $reponse->fetch();

	$activites = json_decode($infos['activites'], TRUE);
	$activites[$key][$id] = $value;

	$bdd->exec('UPDATE groupe SET activites=\''.json_encode($activites).'\' WHERE id = "'.$groupe.'"');	
	$reponse->closeCursor();
	
}

/*On supprime des disponibilités*/

function supprimerDisponibilites($id, $groupe, $start, $end){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}
	$date_finale = '';
	
	/*Unicité d'évènement par personne. On prend les évènements de cette personne pour ce groupe et on supprime les dates*/
	$reponse = $bdd->query("SELECT * FROM events WHERE title= 'Disponibilité de ".$id."' AND groupe='".$groupe."' ");
	$donnees = $reponse->fetch();
	
	if(!is_bool($donnees)){

		$ranges = explode('+', $donnees['date']);
		for($i=0; $i<count($ranges); $i++){
			/*On recupère chaque range avec les dates au format YY-MM-DD*/
			$dates = explode(';', $ranges[$i]);
			$date_debut = $dates[0];
			$date_fin = $dates[1];
			
			$datefinale = fusionnerDate($date_debut, $date_fin, $start, $end);
			if(($datefinale != '') && ($date_finale !='')){
				$date_finale .= '+'.$datefinale;
			}elseif($datefinale != ''){
				$date_finale .= $datefinale;
			}
		}
		$currentDate = date('d').'-'.date('m').'-'.date('Y');
		
		if($date_finale == ''){
			$date_finale = null;
		}
		/*On a déjà un évènement lié à cette personne pour ce groupe*/
		$bdd->exec('UPDATE events SET date=\''.$date_finale.'\', modified = \''.$currentDate.'\' WHERE title= "Disponibilité de '.$id.'" AND groupe = "'.$groupe.'"');	
	
	}
	
}




/*************************************************************************************************************************HTML CONTENT*/




/*On retourne les étoiles correspondant
à la personne, au groupe et à l'activité*/

if(isset($_POST['value']) && !empty($_POST['value'])){
	updateStars($_POST['value'], $_POST['id'], $_POST['key'], $_POST['groupe']);
    getStars($_POST['value'], $_POST['key'], $_POST['id'], $_POST['groupe']);
}

function getStars($value, $key, $id, $groupe){

?>	
				  <div class='rating-stars text-center'>
					<ul class='stars'>
					  <li class='star <?php if($value>0){ echo 'selected'; }?>' title='Nul' data-value='1'>
						<i class='fa fa-star fa-fw'></i>
					  </li>
					  <li class='star <?php if($value>1){ echo 'selected'; }?>' title='Assez Bien' data-value='2'>
						<i class='fa fa-star fa-fw'></i>
					  </li>
					  <li class='star <?php if($value>2){ echo 'selected'; }?>' title='Bien' data-value='3'>
						<i class='fa fa-star fa-fw'></i>
					  </li>
					  <li class='star <?php if($value>3){ echo 'selected'; }?>' title='Excellent' data-value='4'>
						<i class='fa fa-star fa-fw'></i>
					  </li>
					  <li class='star <?php if($value>4){ echo 'selected'; }?>' title='WOW!!!' data-value='5'>
						<i class='fa fa-star fa-fw'></i>
					  </li>
					</ul>
				  </div>

<script>
	function setStars(ratingValue, key, id, groupe){
		$.ajax({
			type:'POST',
			url:'../functions.inc.php',
			data:'value='+ratingValue+'&key='+key+'&id='+id+'&groupe='+groupe,
			success:function(html){
				$('#'+key).html(html);
			}
		});
	}
  
  /* 1. Visualizing things on Hover - See next part for action on click */
  $('#<?php echo $key; ?> .stars li').on('mouseover', function(){
    var onStar = parseInt($(this).data('value'), 10); // The star currently mouse on
   
    // Now highlight all the stars that's not after the current hovered star
    $(this).parent().children('#<?php echo $key; ?> li.star').each(function(e){
      if (e < onStar) {
        $(this).addClass('hover');
      }
      else {
        $(this).removeClass('hover');
      }
    });
    
  }).on('mouseout', function(){
    $(this).parent().children('#<?php echo $key; ?> li.star').each(function(e){
      $(this).removeClass('hover');
    });
  });
  
  /* 2. Action to perform on click */
	$('#<?php echo $key; ?> .stars li').on('click', function(){
    var onStar = parseInt($(this).data('value'), 10); // The star currently selected
    var stars = $(this).parent().children('#<?php echo $key; ?> li.star');
    
    for (i = 0; i < stars.length; i++) {
      $(stars[i]).removeClass('selected');
    }
    
    for (i = 0; i < onStar; i++) {
      $(stars[i]).addClass('selected');
    }
    
    var ratingValue = parseInt($('#<?php echo $key; ?> .stars li.selected').last().data('value'), 10);
	var key = '<?php echo $key; ?>';
	var id = '<?php echo $id; ?>';
	var groupe = '<?php echo $groupe; ?>';
	setStars(ratingValue, key, id, groupe);   
   });

</script>
<?php		
}
?>
