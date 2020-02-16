<header>
		<nav>
			<ul>
				<li <?php if($_SERVER['PHP_SELF'] == '/calendar/accounts/index.php') echo "id='en-cours'"; ?>><a href='index.php'><img alt='icon_home' src='images/icon_home.png'> Tableau de bord</a></li>
				<li <?php if($_SERVER['PHP_SELF'] == '/calendar/accounts/mes_amis.php') echo "id='en-cours'"; ?>><a href='mes_amis.php'><img alt='icon_amis' src='images/icon_friends.png'> Mes amis</a></li>
				<li class='dropdown_group' <?php if($_SERVER['PHP_SELF'] == '/calendar/accounts/mes_groupes.php') echo "id='en-cours'"; ?>><a href='mes_groupes.php'><img alt='icon_groups' src='images/icon_groups.png'> Mes groupes</a>
				<?php if(is_array($_SESSION['groupe'])){
					try{
						$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');

					}catch (Exception $e){
						die('Erreur : ' . $e->getMessage());
					}	
					
					$groupes = implode(",", $_SESSION['groupe']);
					$reponse = $bdd->query('SELECT * FROM groupe WHERE id IN('.$groupes.')');
						
					if(!is_bool($reponse)){
						echo '<ul class="dropdown_content">';
						while($donnees = $reponse->fetch()){
						
							echo '<li><a href="mes_groupes?groupe_id='.$donnees['id'].'">'.$donnees['nom'].'</a></li>';	
						}
						echo '</ul>';
					}
											
				}
				?>
				</li>
				<li <?php if($_SERVER['PHP_SELF'] == '/calendar/accounts/mon_profil.php') echo "id='en-cours'"; ?>><a href='mon_profil.php'><img alt='icon_me' src='images/icon_me.png'> Mon profil</a></li>
				<li><form method="post" action='<?php echo $_SERVER['PHP_SELF']; ?>'>
				<img alt='icon_exit' src='images/icon_exit.png'>
				<input type="submit" name="logout" id="logout" value="Déconnexion"></form></li>
			</ul>
		</nav>
	</header>