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
			#error-log {
			}
			#error-log td {
				padding:0 10px 5px 0;
			}
			#error-log th {
				text-align: left;
			}
			
		</style>
		<script type="text/javascript">
			setTimeout("location.reload(true);",1000);
		</script>
	</head>
	<body>
		<?php
		
		
			$conn = db_connect();
			$error_log_results = db_query($conn, "SELECT * FROM log_errors ORDER BY error_time DESC LIMIT 200");
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
			
			<?
		
		?>
	</body>
</html>
