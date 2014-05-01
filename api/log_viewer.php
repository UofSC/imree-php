<?
	require_once("../../config.php");
?><!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title></title>
		<style>
			#error-log, #locations-log {
				float:left; 
				max-width: 500px;
			}
			#error-log td {
				padding:0 10px 5px 0;
			}
			#error-log th {
				text-align: left;
			}
			
		</style>
		<script type="text/javascript">
			setTimeout("location.reload(true);",2000);
		</script>
	</head>
	<body>
		<?php
		
		
			$conn = db_connect();
			$error_log_results = db_query($conn, "SELECT * FROM log_errors ORDER BY error_time DESC LIMIT 200");
			$location_log_results = db_query($conn, "SELECT * FROM log_location_calculations ORDER BY datetime DESC LIMIT 200");
			echo "<p>Current Server Time:".date("Y-m-d H:i:s")."</p>";
			?>
		<table id='error-log'>
			<thead>
				<tr><th>Time</th><th>IP</th><th>MSG</th></tr>
			</thead>
			<tbody>
				<?
				foreach($error_log_results as $item) {
					echo "
						<tr><td>".$item['error_time']."</td><td>".$item['error_ip']."</td><td>".$item['error_msg']."</td></tr>";
				}
				?>
			</tbody>
		</table>
		<table id='locations-log'>
			<thead>
				<tr><th>device_id</th><th>location_id</th><th>score</th><th>duration</th><th style="width:120px;">notes</th><th>datetime</th></tr>
			</thead>
			<tbody>
				<?
				foreach($location_log_results as $item) {
					echo "
						<tr><td>".$item['device_id']."</td><td>".$item['location_id']."</td><td>".$item['score']."</td><td>".$item['duration']."</td><td>".$item['note']."</td><td>t-".(time() - strtotime($item['datetime']))."s</td></tr>";
				}
				?>
			</tbody>
		</table>
			
			<?
		
		?>
	</body>
</html>
