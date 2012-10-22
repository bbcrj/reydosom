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
 * Created on Nov , 2008
 * @author James Hansen(Kermode Bear Software)
 *
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
$document =& JFactory::getDocument();
$width = $params->get('width', 160);
$height = $params->get('height', 120);
$lat = $params->get('lat', 49);
$long = $params->get('lng', -122);
$zoom = $params->get('zoom', 3);
$mapName = $params->get('mapName', 'map');
$mapType = $params->get('mapType', 'ROADMAP');
$js = "http://maps.google.com/maps/api/js?sensor=false";

$document->addScript($js);
$mapOptions = '';
$markerOptions = '';

if($params->get('marker')){
	$title = $params->get('marker_title', '');
	$marker_lat = $params->get('marker_lat');
	$marker_lng = $params->get('marker_lng');
	$markerOptions =<<<EOL

	var opts = new Object;
	opts.title = "{$title}";
	opts.position = new google.maps.LatLng({$marker_lat}, {$marker_lng});
	opts.map = $mapName;
	marker = new google.maps.Marker(opts);
EOL;
}
$navControls = true;
if($params->get('static') || $params->get('navControls', false) == 0){
	$mapOptions .= ',disableDefaultUI: false'. PHP_EOL;
	$mapOptions .= ',streetViewControl: false' . PHP_EOL;
	$navControls = false;
}
if($params->get('smallmap')){
	$mapOptions .=  ', navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL} ' . PHP_EOL;
	$navControls = true;
}

if(!$navControls)
	$mapOptions .= ',navigationControl: false' . PHP_EOL;


if($params->get('static')){
	$mapOptions .= 	', draggable: false' .PHP_EOL;
}
$mapTypeControl = $params->get('mapTypeControl',false) ? 'true' : 'false';

$mapOptions .= ",mapTypeControl: {$mapTypeControl}". PHP_EOL;

$script =<<<EOL
	google.maps.event.addDomListener(window, 'load', {$mapName}load);

    function {$mapName}load() {
		var options = {
			zoom : {$zoom},
			center: new google.maps.LatLng({$lat}, {$long}),
			mapTypeId: google.maps.MapTypeId.{$mapType}
			{$mapOptions}
		}

        var {$mapName} = new google.maps.Map(document.getElementById("{$mapName}"), options);
		{$markerOptions}
    }

EOL;

JHTML::_('behavior.mootools');

$document->addScriptDeclaration($script);
?>
<div class="map"><div id="<?php echo $mapName;?>" style="margin-left:auto;margin-right:auto;text-align:center;width: <?php echo $width; ?>px; height: <?php echo $height; ?>px"></div></div>