function setDiv(value) {

    if(value == 'annee'){
                document.getElementById('monthDispo').style='display:none;';
                document.getElementById('monthTache').style='display:none;';
				document.getElementById('yearDispo').style='';
                document.getElementById('weekTache').style='display:none;';
				$('#format option').value('annee').html(' selected');
            }
     else if(value === 'semaine'){

                document.getElementById('monthDispo').style='display:none;';
                document.getElementById('monthTache').style='display:none;';
				document.getElementById('yearDispo').style='display:none;';
                document.getElementById('weekTache').style='';
				$('option[value=semaine]').attr('selected', 'selected');
    }else if (value === 'mois'){
				document.getElementById('monthDispo').style='';
                document.getElementById('monthTache').style='';
				document.getElementById('yearDispo').style='display:none;';
				document.getElementById('weekTache').style='display:none;';
	}

}
