<?php

/*
 * Function requested by Ajax
 */
if(isset($_POST['func']) && !empty($_POST['func'])){
    switch($_POST['func']){
        case 'getCalender':
            getCalender($_POST['group'], $_POST['year'],$_POST['month']);
            break;
        case 'getEvents':
            getEvents($_POST['date']);
            break;
        default:
            break;
    }
}


/*
 * Get calendar full HTML
 */
function getCalender($group, $year = '',$month = '')
{
    $dateYear = ($year != '')?$year:date("Y");
    $dateMonth = ($month != '')?$month:date("m");
    $date = $dateYear.'-'.$dateMonth.'-01';
    $currentMonthFirstDay = date("N",strtotime($date));
    $totalDaysOfMonth = cal_days_in_month(CAL_GREGORIAN,$dateMonth,$dateYear);
    $totalDaysOfMonthDisplay = ($currentMonthFirstDay == 1)?($totalDaysOfMonth):($totalDaysOfMonth + ($currentMonthFirstDay-1));
    $boxDisplay = ($totalDaysOfMonthDisplay <= 35)?35:42;
?>
    <div id="calender_section">
        <h2>
			<a href="javascript:void(0);" onclick="getCalendar('calendar_div','<?php echo date("Y"); ?>','<?php echo date("m"); ?>', '<?php echo $group; ?>');" id='today'>Today</a>
	   
            <a href="javascript:void(0);" onclick="getCalendar('calendar_div','<?php echo date("Y",strtotime($date.' - 1 Month')); ?>','<?php echo date("m",strtotime($date.' - 1 Month')); ?>', '<?php echo $group; ?>');">&lt;&lt;</a>
            <select name="month_dropdown" class="month_dropdown dropdown"><?php echo getAllMonths($dateMonth); ?></select>
            <select name="year_dropdown" class="year_dropdown dropdown"><?php echo getYearList($dateYear); ?></select>
            <a href="javascript:void(0);" onclick="getCalendar('calendar_div','<?php echo date("Y",strtotime($date.' + 1 Month')); ?>','<?php echo date("m",strtotime($date.' + 1 Month')); ?>', '<?php echo $group; ?>');">&gt;&gt;</a>
		</h2>
        <div id="event_list" class="none"></div>
        <div id="calender_section_top">
            <ul>
                <li>Lundi</li>
                <li>Mardi</li>
                <li>Mercredi</li>
                <li>Jeudi</li>
                <li>Vendredi</li>
                <li>Samedi</li>
                <li>Dimanche</li>
            </ul>
        </div>
        <div id="calender_section_bot">
            <ul>
            <?php 
                $dayCount = 1; 
                for($cb=1;$cb<=$boxDisplay;$cb++){
                    if(($cb >= $currentMonthFirstDay || $currentMonthFirstDay == 1) && $cb <= ($totalDaysOfMonthDisplay) && $dayCount <= ($totalDaysOfMonth)){
                        //Current date
                        $currentDate = $dayCount.'-'.$dateMonth.'-'.$dateYear;
                        //Define date cell color
                        if(strtotime($currentDate) == strtotime(date("Y-m-d"))){
                            echo '<li date="'.$currentDate.'" class="grey date_cell">';
                        }else{
                            echo '<li date="'.$currentDate.'" class="date_cell">';
                        }
                        //Date cell
						echo '<span>';
                        echo $dayCount;
                        echo '</span>';
                        echo getEvents($currentDate, $group);
                        
                        echo '</li>';
                        $dayCount++;
            ?>
            <?php }else{ ?>
                <li class='empty'><span>&nbsp;</span></li>
            <?php } } ?>
            </ul>
        </div>
    </div>

    <script type="text/javascript">

	   var mouseUpped = false;
	   var mouseDowned = false;
	   
		function getCalendar(target_div,year,month, group){
            $.ajax({
                type:'POST',
                url:'functions.php',
                data:'func=getCalender&group='+group+'&year='+year+'&month='+month,
                success:function(html){
                    $('#'+target_div).html(html);
                }
            });
        }
        
        $('.month_dropdown').on('change',function(){
            getCalendar('calendar_div',$('.year_dropdown').val(),$('.month_dropdown').val());
        });
        $('.year_dropdown').on('change',function(){
			getCalendar('calendar_div',$('.year_dropdown').val(),$('.month_dropdown').val());
        });
		
        $('.date_cell').mousedown(function(){
			mouseDowned = true;
			/*Si la souris a été relevée, alors on a déjà fait une sélection. On recommence
			donc une nouvelle sélection, et on remet mouseUpped à false*/
			if(mouseUpped == true){
				$('.selected').removeClass('selected');
				mouseUpped = false;
			}
			$(this).toggleClass('selected'); 
			/*Début de la sélection*/
        });
		$('.date_cell').mouseenter(function(){
			if(($(this).prev().hasClass('selected')) && (mouseUpped == false)){
				$(this).toggleClass('selected');
			}else{
				/*Si le li d'avant n'est pas selected, on a sauté une ligne
				donc il fait tous les sélectionner*/
				if((mouseUpped == false) && mouseDowned == true){
					var index_debut = $('.selected').last().index();
					var index_fin = $(this).index();
					for(var i=(index_debut+1); i<=index_fin; i++){
						//On ajoute la class selected aux élements précédents;
					}
				}
			}
			/*Pendant de la selection*/			
        });
		$('.date_cell').mouseup(function(){
			mouseUpped=true;
			/*Fin de la selection*/			
        });
    </script>
<?php
}

/*
 * Get months options list.
 */
function getAllMonths($selected = ''){
    $options = '';
    for($i=1;$i<=12;$i++)
    {
        $value = ($i < 10)?'0'.$i:$i;
        $selectedOpt = ($value == $selected)?'selected':'';
        $options .= '<option value="'.$value.'" '.$selectedOpt.' >'.date("F", mktime(0, 0, 0, $i+1, 0, 0)).'</option>';
    }
    return $options;
}

/*
 * Get years options list.
 */
function getYearList($selected = ''){
    $options = '';
    for($i=2015;$i<=2025;$i++)
    {
        $selectedOpt = ($i == $selected)?'selected':'';
        $options .= '<option value="'.$i.'" '.$selectedOpt.' >'.$i.'</option>';
    }
    return $options;
}

/*
 * Get events by date
 */
function getEvents($date, $groupe){
	/*La date nous est donnée au format JJ-MM-AAAA*/
	$ex_date = explode('-', $date);
	$dateCherchee = new DateTime($ex_date[2].'-'.$ex_date[1].'-'.$ex_date[0]);
	
    try{
		$bdd = new PDO('mysql:host=localhost;dbname=trip_manager;charset=utf8', 'root', '');
	}catch (Exception $e){
		die('Erreur : ' . $e->getMessage());
	}
	
    $eventListHTML = '';

    $result = $bdd->query("SELECT * FROM events WHERE groupe = '".$groupe."' AND status = 1");
 
    while($donnees = $result->fetch()){
		if($donnees['date'] !== ''){
			$ranges = explode('+', $donnees['date']);
			for($i=0; $i<count($ranges); $i++){
				$dates = explode(';', $ranges[$i]);
				$date_debut = $dates[0];
				$date_fin = $dates[1];
				
				$start_date = new DateTime($date_debut);
				
				$end_date = new DateTime($date_fin);
				
				if(($start_date<=$dateCherchee) && ($end_date>= $dateCherchee)){
					$membre_id = str_replace("Disponibilité de ", "", $donnees['title']);
					
					$couleur = getColorS($groupe,$membre_id);
					
					$eventListHTML .= '<span class="color" style="background-color: '.$couleur.';"></span>';
					break 1;
				}
			}
		}
	}
    
    return $eventListHTML;
}

function addEvent($date,$title){
	//Include db configuration file
	include 'dbConfig.php';
	$currentDate = date("Y-m-d");
	//Insert the event data into database
	$insert = $db->query("INSERT INTO events (title,date,created,modified) VALUES ('".$title."','".$date."','".$currentDate."','".$currentDate."')");
	if($insert){
		echo 'ok';
	}else{
		echo 'err';
	}
}

function getColorS($groupe, $id){
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

?>