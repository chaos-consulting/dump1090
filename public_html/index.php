<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="utf-8"/>
		<link rel="stylesheet" type="text/css" href="style.css" />
		<link rel="stylesheet" href="jquery/jquery-ui-1.11.4-smoothness.css" />
		<script src="jquery/jquery-3.0.0.min.js"></script>
		<script src="jquery/jquery-ui-1.11.4.min.js"></script>

                <link rel="stylesheet" href="ol3/ol-3.17.1.css" type="text/css" />
                <script src="ol3/ol-3.17.1.js" type="text/javascript"></script>

                <link rel="stylesheet" href="ol3/ol3-layerswitcher.css" type="text/css"/>
                <script src="ol3/ol3-layerswitcher.js" type="text/javascript"></script>

		<script type="text/javascript" src="config.js"></script>
		<script type="text/javascript" src="markers.js"></script>
		<script type="text/javascript" src="dbloader.js"></script>
		<script type="text/javascript" src="registrations.js"></script>
		<script type="text/javascript" src="planeObject.js"></script>
		<script type="text/javascript" src="formatter.js"></script>
		<script type="text/javascript" src="flags.js"></script>
		<script type="text/javascript" src="layers.js"></script>
		<script type="text/javascript" src="script.js"></script>
		<!--<script type="text/javascript" src="coolclock/excanvas.js"></script>
		<script type="text/javascript" src="coolclock/coolclock.js"></script>
		<script type="text/javascript" src="coolclock/moreskins.js"></script>-->
		<title>DUMP1090</title>
	</head>
	
	<body onload="initialize()">
		<div id="loader" class="hidden">
			<img src="spinny.gif" id="spinny" alt="Loading...">
			<progress id="loader_progress"></progress>
		</div>

		<!--
			This is hideous. airframes.org insists on getting a POST with a "submit" value specified,
			but if we have an input control with that name then it shadows the submit() function that
			we need. So steal the submit function off a different form. Surely there is a better way?!
		-->
		<form id="horrible_hack" class="hidden">
		</form>
		<form id="airframes_post" method="POST" action="http://www.airframes.org/" target="_blank" class="hidden">
			<input type="hidden" name="reg1" value="">
			<input type="hidden" name="selcal" value="">
			<input id="airframes_post_icao" type="hidden" name="ica024" value="">
			<input type="hidden" name="submit" value="submit">
		</form>

		<div id="map_container">
			<div id="map_canvas"></div>
		</div>

		<div id="sidebar_container">
			<div id="sidebar_canvas">
				<div id="timestamps">
					<table style="width: 100%">
						<tr>
							<td style="text-align: center"> <canvas id="utcclock"></canvas> </td>
							<td style="text-align: center"> <canvas id="receiverclock"></canvas> </td>
						</tr>

						<tr>
							<td style="text-align: center">UTC</td>
							<td style="text-align: center">Last Update</td>
						</tr>
					</table>
				</div> <!-- timestamps -->
				
				<div id="sudo_buttons">
					<table style="width: 100%">
						<tr>
							<td style="width: 150px; text-align: center;" class="pointer">
								[ <span onclick="resetMap();">Reset Map</span> ]
							</td>
						</tr>
					</table>
				</div> <!-- sudo_buttons -->

				<div id="dump1090_infoblock">
					<table style="width: 100%">
						<tr class="infoblock_heading">
							<td>
								<b id="infoblock_name">DUMP1090</b>
							</td>
							<td style="text-align: right">
								<a href="https://github.com/mutability/dump1090" id="dump1090_version" target="_blank"></a>
							</td>
						</tr>

						<tr class="infoblock_body">
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>

						<tr class="infoblock_body dim">
							<td>(no aircraft selected)</td>
							<td>&nbsp;</td>
						</tr>

						<tr class="infoblock_body">
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>

						<tr class="infoblock_body">
							<td>Aircraft (total): <span id="dump1090_total_ac">n/a</span></td>
							<td>Messages: <span id="dump1090_message_rate">n/a</span>/sec</td>
						</tr>

						<tr class="infoblock_body">
							<td>(with positions): <span id="dump1090_total_ac_positions">n/a</span></td>
							<td>History: <span id="dump1090_total_history">n/a</span> positions</td>
						</tr>
					</table>
				</div> <!-- dump1090_infoblock -->

				<div id="selected_infoblock" class="hidden">
					<table style="width: 100%">
						<tr class="infoblock_heading">
							<td colspan="2">
								<b>
									<span id="selected_callsign" onclick="toggleFollowSelected();" class="pointer">n/a</span>
								</b>
								<span id="selected_follow" onclick="toggleFollowSelected();" class="pointer">&#x21D2;</span>

								<span id="selected_flag">
									<img style="width: 20px; height=12px" src="about:blank" alt="Flag">
								</span>
								<a href="http://www.airframes.org/" onclick="document.getElementById('horrible_hack').submit.call(document.getElementById('airframes_post')); return false;">
									<span id="selected_icao"></span>
								</a>
								<span id="selected_registration"></span>
								<span id="selected_icaotype"></span>
								<span id="selected_emergency"></span>
								<a id="selected_flightaware_link" href="" target="_blank">[FlightAware]</a>
								<span id="selected_links">
									<a id="selected_fr24_link" href="" target="_blank">[FR24]</a>
									<a id="selected_flightstats_link" href="" target="_blank">[FlightStats]</a>
									<a id="selected_planefinder_link" href="" target="_blank">[PlaneFinder]</a>
									<a href="" id="selected_history_link" target="blank">[Heatmap]</a>
								</span>
							</td>
						</tr>

						<tr id="infoblock_country" class="infoblock_body">
							<td colspan="2">Country of registration: <span id="selected_country">n/a</span></td>
						</tr>

						<tr class="infoblock_body">
							<td style="width: 55%">Altitude: <span id="selected_altitude"></span></td>
							<td style="width: 45%">Squawk: <span id="selected_squawk"></span></td>
						</tr>

						<tr class="infoblock_body">
							<td>Speed: <span id="selected_speed">n/a</span></td>
							<td>RSSI: <span id="selected_rssi">n/a</span></td>
						</tr>

						<tr class="infoblock_body">
							<td>Track: <span id="selected_track">n/a</span></td>
							<td>Last seen: <span id="selected_seen">n/a</span></td>
						</tr>

						<tr class="infoblock_body">
							<td colspan="2">Position: <span id="selected_position">n/a</span></td>
						</tr>

						<tr class="infoblock_body">
							<td colspan="2">Distance from Site: <span id="selected_sitedist">n/a</span></td>
						</tr>
					</table>
<a href="" id="selected_pic0_link"><img id="selected_pic0" src="" style="width:391px;margin-left:2px"alt="Kein Bild von dieser Maschine vorhanden"onError="$('#selected_pic_link').attr('href','planepics/'+selected.icaotype+'.jpg');"></a><br>
<a href="" id="selected_pic_link"><img id="selected_pic" src="" style="width:391px;margin-left:2px"alt="Kein allgemeines Bild vorhanden"></a>
				</div> <!-- selected_infoblock -->

				<div id="planes_table">
					<table id="tableinfo" style="width: 100%">
						<thead style="background-color: #BBBBBB; cursor: pointer;">
							<tr>
								<td id="icao" onclick="sortByICAO();">ICAO</td>
								<td id="flag" onclick="sortByCountry()"><!-- column for flag image --></td>
								<td id="flight" onclick="sortByFlight();">Flight</td>
								<td id="squawk" onclick="sortBySquawk();" style="text-align: right">Squawk</td>
								<td id="altitude" onclick="sortByAltitude();" style="text-align: right">Altitude</td>
								<td id="speed" onclick="sortBySpeed();" style="text-align: right">Speed</td>
								<td id="distance" onclick="sortByDistance();" style="text-align: right">Distance</td>
								<td id="track" onclick="sortByTrack();" style="text-align: right">Track</td>
								<td id="msgs" onclick="sortByMsgs();" style="text-align: right">Msgs</td>
								<td id="seen" onclick="sortBySeen();" style="text-align: right">Age</td>
							</tr>
						</thead>
						<tbody>
							<tr id="plane_row_template" class="plane_table_row hidden">
								<td>ICAO</td>
								<td><img style="width: 20px; height=12px" src="about:blank" alt="Flag"></td>
								<td>FLIGHT</td>
								<td style="text-align: right">SQUAWK</td>
								<td style="text-align: right">ALTITUDE</td>
								<td style="text-align: right">SPEED</td>
								<td style="text-align: right">DISTANCE</td>
								<td style="text-align: right">TRACK</td>
								<td style="text-align: right">MSGS</td>
								<td style="text-align: right">SEEN</td>
							</tr>
						</tbody>
					</table>
				</div> <!-- planes_table -->
				
			</div> <!-- sidebar_canvas -->
		</div> <!-- sidebar_container -->

		<div id="SpecialSquawkWarning" class="hidden">
			<b>Squawk 7x00 is reported and shown.</b><br>
			This is most likely an error in receiving or decoding.<br>
			Please do not call the local authorities, they already know about it if it is a valid squawk.
		</div>

		<div id="update_error" class="hidden">
			<b>Problem fetching data from dump1090.</b><br>
			<span id="update_error_detail"></span><br>
			The displayed map data will be out of date.
		</div>

		<div id="container_splitter"></div>
<?php include('/var/www/airlog/navbar.php') ?>
	</body>
</html>
