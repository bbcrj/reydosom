<?php
/**
 * 	Copyright 2008,2009
 *  This file is part of mod_GMap.
 *
 *  mod_GMap is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  mod_GMap is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with mod_GMap.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Created on Nov, 2008
 * @author James Hansen(Kermode Bear Software)
 *
 */
require_once(JPATH_SITE . '/modules/mod_JGMap/elements/GElement.php');
class JElementGMarker extends JElement{
	var $_name = "GMarker";
 }

 class JFormFieldGMarker extends JFormField{
	var $type = 'GMarker';
	/**
	 * Javascript map initialization
	 * Javascript variable 'marker' must be defined.
	 */
	function addJavascript(&$params){
		$document = JFactory::getDocument();
		$lat = $params->get('lat');
		$long = $params->get('longitude');
		$js =<<<EOL
			var marker;
			function updateMarker(element){
				var lat = $('jform_params_lat').value;
				var lng = $('jform_params_lng').value;
				var opts = { draggable : true, bouncy: true };

				if(element.checked == true){
						opts.title = $('jform_params_marker_title').value;
						opts.map = map;
						opts.position = new google.maps.LatLng( lat, lng);
						marker = new google.maps.Marker(opts);
						google.maps.event.addListener(marker, 'drag', function(latlng){
							var latlng = this.getPosition();
							$('jform_params_marker_lat').value = latlng.lat();
							$('jform_params_marker_lng').value = latlng.lng();
						});

						$('jform_params_marker_lat').value = lat;
						$('jform_params_marker_lng').value = lng;

			  	}else{
			  		if(marker){
			  			marker.setVisible(false);
			  			delete marker;
			  			marker = null;

			  		}
			  	}
			}
EOL;

		$document->addScriptDeclaration($js);
	}

	function getInput  ( ){
		$params =& GElement::getParameters();

		$this->addJavascript($params);
		$marker = ($params->get('marker', '')) ? 'checked' : '' ;
		$html = '<div>';
		$html .= '<input type="checkbox" id="paramsmarker" name="jform[params][marker]" ' . $marker .
				' onclick="updateMarker(this);" value="1" />';

		return $html;
	}
 }
?>