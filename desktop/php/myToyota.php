<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('myToyota');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<!-- Page d'accueil du plugin -->
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<div class="row">
			<div class="col-sm-10">
				<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
				<!-- Boutons de gestion du plugin -->
				<div class="eqLogicThumbnailContainer">
					<div class="cursor eqLogicAction logoPrimary" data-action="add">
						<i class="fas fa-plus-circle"></i>
						<br>
						<span>{{Ajouter}}</span>
					</div>
					<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
						<i class="fas fa-wrench"></i>
						<br>
						<span>{{Configuration}}</span>
					</div>
				</div>
			</div>
			<?php
			// à conserver
			// sera afficher uniquement si l'utilisateur est en version 4.4 ou supérieur
			$jeedomVersion  = jeedom::version() ?? '0';
			$displayInfoValue = version_compare($jeedomVersion, '4.4.0', '>=');
			if ($displayInfoValue) {
			?>
				<div class="col-sm-2">
					<legend><i class=" fas fa-comments"></i> {{Community}}</legend>
					<div class="eqLogicThumbnailContainer">
						<div class="cursor eqLogicAction logoSecondary" data-action="createCommunityPost">
							<i class="fas fa-ambulance"></i>
							<br>
							<span style="color:var(--txt-color)">{{Créer un post Community}}</span>
						</div>
					</div>
				</div>
			<?php
			}
			?>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes équipements}}</legend>
		<?php
		if (count($eqLogics) == 0) {
			echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement myToyota trouvé, cliquer sur "Ajouter" pour commencer}}</div>';
		} else {
			// Champ de recherche
			echo '<div class="input-group" style="margin:5px;">';
			echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
			echo '<div class="input-group-btn">';
			echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
			echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
			echo '</div>';
			echo '</div>';
			// Liste des équipements du plugin
			echo '<div class="eqLogicThumbnailContainer">';
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $eqLogic->getImage() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '<span class="hiddenAsCard displayTableRight hidden">';
				echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
				echo '</span>';
				echo '</div>';
			}
			echo '</div>';
		}
		?>
	</div> <!-- /.eqLogicThumbnailDisplay -->

	<!-- Page de présentation de l'équipement -->
	<div class="col-xs-12 eqLogic" style="display: none;">
		<!-- barre de gestion de l'équipement -->
		<div class="input-group pull-right" style="display:inline-flex;">
			<span class="input-group-btn">
				<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
				</a>
			</span>
		</div>
		<!-- Onglets -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<!-- Onglet de configuration de l'équipement -->
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<!-- Partie gauche de l'onglet "Equipements" -->
				<!-- Paramètres généraux et spécifiques de l'équipement -->
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nom de l'équipement}}</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;">
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Objet parent}}</label>
								<div class="col-sm-6">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
										$options = '';
										foreach ((jeeObject::buildTree(null, false)) as $object) {
											$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
										}
										echo $options;
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Catégorie}}</label>
								<div class="col-sm-6">
									<?php
									foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" >' . $value['name'];
										echo '</label>';
									}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Options}}</label>
								<div class="col-sm-6">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
								</div>
							</div>

							<legend><i class="fas fa-cogs"></i> {{Paramètres du compte et du véhicule}}</legend>

							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nom d'utilisateur compte myToyota}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Renseignez votre nom d'utilisateur (mail)}}"></i></sup>
								</label>
								<div class="col-sm-4">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="username">
								</div>
							</div>

							<div id="div_pwd" class="form-group">		
									<label class="col-sm-4 control-label">{{Mot de passe}}</label>
									<div class="col-sm-4 pass_show">
										<input type="password" id="pwd" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="password" placeholder="Mot de passe utilisé pour vous connecter à votre compte My BMW" style="margin-bottom:0px !important">
										<span class="eye fa fa-fw fa-eye toggle-pwd"></span>
									</div>
							</div>

							<div id="div_vin" class="form-group">		
								<label class="col-sm-4 control-label">{{VIN}}</label>
								<div class="col-sm-4">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_vin" placeholder="Numéro d'identification de votre véhicule disponible sur la carte grise (E)">
								</div>
							</div>

							<div class="form-group">		
								<label class="col-sm-4 control-label">{{Modèle}}</label>
								<div id="div_model" class="col-sm-4">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_model" placeholder="Modèle du véhicule" readonly>
								</div>
							</div>
							
							<div class="form-group">		
								<label class="col-sm-4 control-label">{{Date fabrication}}</label>
								<div id="div_year" class="col-sm-4">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_year" placeholder="Date de fabrication du véhicule" readonly>
								</div>
							</div>
							
							<div class="form-group">		
								<label class="col-sm-4 control-label">{{Type}}</label>
								<div id="div_type" class="col-sm-4">
									<input type="text" class="eqLogicAttr form-control" style="margin: 1px 0px;" data-l1key="configuration" data-l2key="vehicle_type" placeholder="Type de véhicule" readonly>
								</div>
							</div>

							</br>

							<div id="div_actions" class="form-group">						
								<label class="col-sm-4 control-label">{{Actions}}</label>	
								<div class="col-sm-4">
									<a class="btn btn-danger btn-sm cmdAction" id="bt_Synchronization"><i class="fas fa-sync"></i> {{Synchronisation}}</a>
									<a class="btn btn-primary btn-sm cmdAction" id="bt_Data"><i class="far fa-file-alt"></i> {{Données brutes}}</a>
								</div>	
							</div>

							</br>

							</br>
								
								<legend><i class="fas fa-location-arrow"></i> {{Paramètres de localisation}}</legend>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Domicile (présence)}}</label>
									<div class="col-sm-4">
										<select id="sel_option_localisation" class="eqLogicAttr form-control" style="margin-bottom: 1px;" data-l1key="configuration" data-l2key="option_localisation">
											<?php
											if ( (config::byKey('info::latitude','core','0') != '0') && (config::byKey('info::longitude','core','0') != '0') ) {
												echo '<option value="" disabled selected hidden>{{Choisir dans la liste}}</option>';
												echo '<option value="jeedom">{{Configuration Jeedom}}</option>';
												echo '<option value="vehicle">{{Configuration position actuelle du véhicule}}</option>';
												echo '<option value="manual">{{Configuration manuelle}}</option>';
											} 
											else {
												echo '<option value="" disabled selected hidden>{{Choisir dans la liste}}</option>';
												echo '<option value="vehicle">{{Configuration position actuelle du véhicule}}</option>';
												echo '<option value="manual">{{Configuration manuelle}}</option>';
												//echo '<option value="jeedom">{{Configuration Jeedom indisponible}}</option>';
											}
											?>
										</select>
									</div>
								</div>
								
								<div class="form-group" id="gps_coordinates">		
									<label class="col-sm-4 control-label help" data-help="{{Coordonnées GPS au format xx.xxxxxx  et pas xx°xx'xx.x''N}}">{{Coordonnées GPS}}</label>
									<div class="col-sm-2" id="div_home_lat">
										<input id="input_home_lat" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="home_lat" placeholder="Lat. domicile">
									</div>
									<div class="col-sm-2" id="div_home_long">
										<input id="input_home_long" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="home_long" placeholder="Long. domicile">
									</div>
									<div class="col-sm-2">
										<a class="btn btn-primary btn-sm cmdAction" id="bt_gps" style="height:32px; width:32px; padding-top:8px" title="{{Récupérer la position actuelle du véhicule}}"><i class="fas fa-location-arrow"></i></a>
									</div>	
								</div>
																							
								<div class="form-group">	
									<label class="col-sm-4 control-label">{{Distance max (en m)}}</label>
									<div class="col-sm-4">
										<input id="home_distance"type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="home_distance" placeholder="Distance max avec votre domicile (en m)">
									</div>
								</div>
								
								</br></br>
						</div>

						<!-- Partie droite de l'onglet "Équipement" -->
						<!-- Affiche un champ de commentaire par défaut mais vous pouvez y mettre ce que vous voulez -->
						<div class="col-lg-6">
							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Description}}</label>
								<div class="col-sm-6">
									<textarea class="form-control eqLogicAttr autogrow" data-l1key="comment"></textarea>
								</div>
							</div>
						</div>


						<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>	
                        		
								<div class="form-group">
									<div id="div_img" class="col-sm-6" style="padding-top: 20px;">
										<img id="car_img" src=""/>
									</div>
								</div>
								
							</fieldset>
						</form>  
                    </div>







					</fieldset>
				</form>
			</div><!-- /.tabpanel #eqlogictab-->

			<!-- Onglet des commandes de l'équipement -->
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<!--<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/>-->
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{ID}}</th><th>{{Nom}}</th><th>{{Type}}</th><th>{{Logical ID}}</th><th>{{Options}}</th><th>{{Valeur}}</th><th>{{Action}}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>

		</div><!-- /.tab-content -->



	</div><!-- /.eqLogic -->

	<script>
			setDisplayGPS();
			setDisplayPanel();
			
			$('#sel_option_localisation').on("change",function (){
				setDisplayGPS();
			});

			$('#sel_panel_icon').on("change",function (){
				setDisplayPanel();
			});
			
			function setDisplayGPS() {
				if ( $('.eqLogicAttr[data-l2key=option_localisation]').value() == "jeedom" || $('.eqLogicAttr[data-l2key=option_localisation]').value() == null) {
					$('#gps_coordinates').hide();
					$('#home_distance').css('margin', '0px 0px');
				}
				if ( $('.eqLogicAttr[data-l2key=option_localisation]').value() == "manual" ) {
					$('#gps_coordinates').show();
					$('#bt_gps').hide();
					$('#input_home_lat').attr('readonly', false);
					$('#input_home_long').attr('readonly', false);
					$('#home_distance').css('margin', '1px 0px');
				}
				if ( $('.eqLogicAttr[data-l2key=option_localisation]').value() == "vehicle" ) {
					$('#gps_coordinates').show();
					$('#bt_gps').show();
					$('#input_home_lat').attr('readonly', true);
					$('#input_home_long').attr('readonly', true);
					$('#home_distance').css('margin', '1px 0px');
				}
			}

			function setDisplayPanel() {
				if ( $('.eqLogicAttr[data-l2key=panel_doors_windows_display]').value() == "text") {
					$('#sel_panel_color option[value=""]').prop('selected', true);
					$('#sel_panel_color').attr('disabled', true);
				}
				if ( $('.eqLogicAttr[data-l2key=panel_doors_windows_display]').value() == "icon") {
					$('#sel_panel_color').attr('disabled', false);
				}
			}


		$('body').off('click', '.toggle-pwd').on('click', '.toggle-pwd', function () {
			$(this).toggleClass("fa-eye fa-eye-slash");
			var input = $("#pwd");
			if (input.attr("type") === "password") {
			input.attr("type", "text");
			} else {
			input.attr("type", "password");
			}
		});

	</script>
	
	<style>
		
		.pass_show {
			position: relative
		}

		.pass_show .eye {
			position: absolute;
			top: 60% !important;
			right: 20px;
			z-index: 1;
			margin-top: -10px;
			cursor: pointer;
			transition: .3s ease all;
		}

	</style>			

</div><!-- /.row row-overflow -->

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'myToyota', 'js', 'myToyota'); ?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js'); ?>