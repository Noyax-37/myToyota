/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
})

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} }
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
	var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
	tr += '<td class="hidden-xs" style="width:5%">';
	tr += '<span class="cmdAttr" data-l1key="id"></span>';
	tr += '</td>';
	tr += '<td style="width:20%">';
	tr += '<input class="cmdAttr form-control input-sm" style="width:80%" data-l1key="name" placeholder="{{Nom de la commande}}">';
	tr += '</td>';
	tr += '<td style="width:10%; padding:5px 0px">';
	tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
	tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
	tr += '</td>';
	tr += '<td style="width:20%">';
	tr += '<input class="cmdAttr form-control input-sm" style="width:80%" data-l1key="logicalId" readonly=true>';
	tr += '</td>';
	tr += '<td style="width:10%">';
	if (init(_cmd.type) == 'info') {
		tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label>';
		tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label>';
		if (init(_cmd.subType) == 'binary') {
			tr += '</br><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label>';
		}
		tr += '<div style="margin-top:7px;">'
		tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
		tr += '</div>'
	}
	if (init(_cmd.type) == 'action') {
		tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label>';
	}
	tr += '</td>';
	tr += '<td style="width:25%">';
	tr += '<span class="cmdAttr" data-l1key="htmlstate" placeholder="{{Valeur}}">';
	tr += '</td>';	
	tr += '<td style="width:10%">';
	if (is_numeric(_cmd.id)) {
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
	}
	tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove" style="margin-top:4px;"></i>';
	tr += '</td>';
	tr += '</tr>';
		
	$('#table_cmd tbody').append(tr);
	$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
	if (isset(_cmd.type)) {
		$('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
	}
	jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}


function printEqLogic(_eqLogic) {
 	
	document.getElementById('car_img').src = 'plugins/myToyota/data/' + $('.eqLogicAttr[data-l2key=vehicle_vin]').value() + '.png';
	document.getElementById('car_img').onload = function() { 
	}

	document.getElementById('car_img').onerror = function() { 
		document.getElementById('car_img').src = "plugins/myToyota/data/image_car_not_found.png"; 
	}
		
};


function synchronize()  {
	
	//$('#div_brand').empty();
	$('#div_model').empty();
	$('#div_year').empty();
	$('#div_type').empty();
	$('#div_capabilities').empty();
	  
	$('#div_alert').showAlert({message: '{{Synchronisation en cours}}', level: 'warning'});	
	$.ajax({													// fonction permettant de faire de l'ajax
		type: "POST", 											// methode de transmission des données au fichier php
		url: "plugins/myToyota/core/ajax/myToyota.ajax.php", 			// url du fichier php
		data: {
			action: "synchronize",
			vin: $('.eqLogicAttr[data-l2key=vehicle_vin]').value(),
			username: $('.eqLogicAttr[data-l2key=username]').value(),
			pwd: $('.eqLogicAttr[data-l2key=password]').value(),
			brand: $('.eqLogicAttr[data-l2key=vehicle_brand]').value(),
			},
		dataType: 'json',
		error: function (request, status, error) {
		handleAjaxError(request, status, error);
		},
		success: function (data) { 			

			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: '{{Erreur lors de la synchronisation}}', level: 'danger'});
				return;
			}
			else  {
				//$('#div_alert').showAlert({message: '{{test}}', level: 'danger'});
				//$('#div_brand').append('<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_brand" placeholder="Marque du véhicule" value="'+data.result['brand']+'" readonly>'); 
				if (data.result['erreur'] == 'ok'){
					$('#div_model').append('<input id="model" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_model" placeholder="{{Modèle du véhicule}}" value="'+data.result['modelName']+'" readonly>'); 
					$('#div_year').append('<input id="year" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_year" placeholder="{{Année de fabrication du véhicule}}" value="'+data.result['modelYear']+'" readonly>'); 
					$('#div_type').append('<input id="type" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_type" placeholder="{{Type de véhicule}}" value="'+data.result['driveTrain']+'" readonly>');
					$('#div_capabilities').append('<textarea id=get_capabilities rows="2" cols="50" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="capabilities" readonly>'+data.result['capabilities']+'</textarea>');
					$('#div_img').empty();
					var img ='<img id="car_img" src="plugins/myToyota/data/' + data.result['vin'] + '.png" style="height:300px" />';
					$('#div_img').append(img);
					//$('.btn[data-action=save]').click();
					window.location.reload();
					$('#div_alert').showAlert({message: '{{Synchronisation terminée avec succès}}', level: 'success'});
				} else {
					$('#div_alert').showAlert({message: '{{Synchro NOK, aucun VIN ne correspond ou erreur dans la récupération des données, vérifiez les logs}}', level: 'danger'});
				}
			}
		}
	});
	
};

function all_data()  {
	
	$('#div_alert').showAlert({message: '{{Récupération des données en cours}}', level: 'warning'});	
	$.ajax({													// fonction permettant de faire de l'ajax
		type: "POST", 											// methode de transmission des données au fichier php
		url: "plugins/myToyota/core/ajax/myToyota.ajax.php", 			// url du fichier php
		data: {
			action: "all_data",
			vin: $('.eqLogicAttr[data-l2key=vehicle_vin]').value(),
			},
		dataType: 'json',
			error: function (request, status, error) {
			handleAjaxError(request, status, error);
			},
		success: function (data) { 			

			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: '{{Erreur lors de la récupération des données}}', level: 'danger'});
				return;
			}
			$('#div_alert').showAlert({message: '{{Récupération terminée avec succès}}', level: 'success'});
		}
	});
	
};

function getCoordinates()  {

	$('#div_home_lat').empty();
	$('#div_home_long').empty();
	
	//$('#div_alert').showAlert({message: '{{Récupération des informations en cours}}', level: 'warning'});	
	$.ajax({													// fonction permettant de faire de l'ajax
		type: "POST", 											// methode de transmission des données au fichier php
		url: "plugins/myToyota/core/ajax/myToyota.ajax.php", 			// url du fichier php
		data: {
			action: "gps",
			vin: $('.eqLogicAttr[data-l2key=vehicle_vin]').value(),
			},
		dataType: 'json',
			error: function (request, status, error) {
			handleAjaxError(request, status, error);
			},
		success: function (data) { 			

			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: '{{Erreur lors de la récupération des informations}}', level: 'danger'});
				return;
			}
			else  {
				if ( data.result['latitude'] == "" || data.result['longitude'] == "" )  {
					$('#div_alert').showAlert({message: '{{Aucunes coordonnées disponibles}}', level: 'danger'});
				}
				else  {
				$('#div_home_lat').append('<input id="input_home_lat" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="home_lat" placeholder="Latitude de votre domicile" value="'+data.result['latitude']+'" readonly>');
				$('#div_home_long').append('<input id="input_home_long" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="home_long" placeholder="Longitude de votre domicile" value="'+data.result['longitude']+'" readonly>');
				}
			}
			//$('#div_alert').showAlert({message: '{{Récupération des informations terminée avec succès}}', level: 'success'});
		}
	});

}

$('#bt_gps').on('click',function() {
 
	$('.btn[data-action=save]').click();
	setTimeout(getCoordinates,2000);
	
});


$('#bt_Synchronization').on('click',function() {
 
	$('.btn[data-action=save]').click();
	setTimeout(synchronize,1000);
	
});

$('#bt_Data').on('click',function() {
	
	setTimeout(all_data,1000);
	
});


$('.eqLogicAction[data-action=createCommunityPost]').on('click', function (event) {
    jeedom.plugin.createCommunityPost({
      type: eqType,
      error: function(error) {
        domUtils.hideLoading()
        jeedomUtils.showAlert({
          message: error.message,
          level: 'danger'
        })
      },
      success: function(data) {
        let element = document.createElement('a');
        element.setAttribute('href', data.url);
        element.setAttribute('target', '_blank');
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
      }
    });
    return;
});