<?php
date_default_timezone_set('Etc/UTC');
require 'PHPMailer/PHPMailerAutoload.php';

function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = '';
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass .= $alphabet[$n];
    }
    return $pass;
}
function sendMail($adress, $nom, $prenom){
	//Create a new PHPMailer instance
	$mail = new PHPMailer;
	//Tell PHPMailer to use SMTP
	$mail->isSMTP();
	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
	$mail->SMTPDebug = 0;
	//Ask for HTML-friendly debug output
	$mail->Debugoutput = 'html';
	//Set the hostname of the mail server
	$mail->Host = 'smtp.gmail.com';
	// use
	// $mail->Host = gethostbyname('smtp.gmail.com');
	// if your network does not support SMTP over IPv6
	//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
	$mail->Port = 587;
	//Set the encryption system to use - ssl (deprecated) or tls
	$mail->SMTPSecure = 'tls';
	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;
	//Username to use for SMTP authentication - use full email address for gmail
	$mail->Username = "tripmanager.mail@gmail.com";
	//Password to use for SMTP authentication
	$mail->Password = "Pin114162";
	//Set who the message is to be sent from
	$mail->setFrom('tripmanager.mail@gmail.com', 'Trip Manager');
	//Set who the message is to be sent to
	$mail->addAddress($adress, $nom.' '.$prenom);
	//Set the subject line
	$mail->Subject = "Récupération de votre mot de passe 'Trip Manager'";
	//Replace the plain text body with one created manually
	$newpass = randomPassword();
	$mail->Body = "Voici votre nouveau mot de passe : ".$newpass;
	//send the message, check for errors
	if (!$mail->send()) {
		$return = array(
		'message' => "Mailer Error: " . $mail->ErrorInfo,
		'mdp' => $newpass
		);
		return $return;
	} else {
		$return = array(
		'message' => 'Un mail vient de vous être envoyé',
		'mdp' => $newpass
		);
		return $return;
	}
}

function exists_email($email){
	try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}	
		
	$reponse = $bdd->query('SELECT * FROM personne WHERE mail=\''.$email.'\'');
	$donnees = $reponse->fetch();
	
	if(is_bool($donnees)){
		$return = array(
		'is' => 0);
	}else{
		$return = array_merge_recursive(array('is' => 1), $donnees);
	}
	$reponse->closeCursor();
	return $return;
}

if(!empty($_POST['lost'])){
	if(filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL) && (exists_email($_POST['mail'])['is'] == 1)){
		$email = $_POST['mail'];
		$infos = exists_email($email);

		$message = sendMail($email, $infos['nom'], $infos['prenom']);
		if($message['message']=='Un mail vient de vous être envoyé'){
			$erreur='Un mail vient de vous être envoyé';
			$newpass=$message['mdp'];
			try{
				$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
			}catch (Exception $e){
				die('Erreur : ' . $e->getMessage());
			}
			
			$bdd->exec('UPDATE personne SET pass=\''.password_hash(htmlspecialchars($newpass), PASSWORD_DEFAULT).'\' WHERE id='.$infos['id'].'');	
			
		}else{
			$erreur = $message['message'];
		}
	}else{
		$erreur = 'Entrez une adresse e-mail valide';
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
		<h1>Récupération du mot de passe</h1>
			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<table>
				<tr>
					<td>Adresse email :</td>
					<td><input type="text" name="mail" placeholder="mail@exemple.com"/></td>
				</tr>
				<tr>
					<td colspan='2' class='submit_button'><input type="submit" value="Envoyer" name='lost'/></td>
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
	</div>
</body>
</html>