<?php
	//Kozlovna, Pět penez, Kolkovna, Avion 58, Port 58, Motoburger, Uholiše, EXTOL, Kantína
	$restorantZomatoID = array(16506954, 16506974, 17830773, 16510994, 16507232, 16506093, 18381797, 16506143, 18311839);
	$zomatoKey = "1267dfea6aa1434b69b194530d52bc8c";

	function getData($id, $restorant = false){
		// Errors on
		error_reporting(E_ALL);

		// Get cURL resource
		$curl = curl_init();

		// Curl options restorant info/dailymenu info
		if ($restorant == true){
			curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_HTTPHEADER => ['Accept: application/json', 'user-key: ' . $zomatoKey],
			    CURLOPT_URL => 'https://developers.zomato.com/api/v2.1/restaurant?res_id='.$id
			));
		}else{
			curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_HTTPHEADER => ['Accept: application/json', 'user-key: ' . $zomatoKey],
			    CURLOPT_URL => 'https://developers.zomato.com/api/v2.1/dailymenu?res_id='.$id
			));
		}	

		// Send the request & save response to $resp
		$resp = curl_exec($curl);

		// Check for errors if curl_exec fails
		if(!curl_exec($curl)){
		    die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
		}

		// Close request to clear up some resources
		curl_close($curl);

		// Decode json
		$json = json_decode($resp, true);

		if(isset($json['daily_menus'][0]['daily_menu']['daily_menu_id']) || $restorant == true){	
			return $json;
		}else{
			return 400;
		}
	}

	// define the path and name of cached file
	$cachefile = 'cached-files/'.date('M-d-Y').'.php';
	// define how long we want to keep the file in seconds. I set mine to 5 hours.
	$cachetime = 2000;
	// Check if the cached file is still fresh. If it is, serve it up and exit.
	if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
   		include($cachefile);
    	exit;
	}else{
		$files = glob('cached-files/*'); // get all file names
		foreach($files as $file){ // iterate files
		  	if(is_file($file)){
		    		unlink($file); // delete file
		  	}
		}
	}
	// if there is either no file OR the file to too old, render the page and capture the HTML.
	ob_start();
?>

<!DOCTYPE html>
<html lang="cz-cs">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="css/base.css">
		<title>Menus</title>
		
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
	</head>
	<body>
		<!-- Show when JS is disable -->
		<noscript>
			<style>
				.pagecontainer {display:none;}
			</style>
			<div id="nojsmsg">
				<p>You don't have javascript enabled.  Good luck with that :-).</p>
			</div>
		</noscript>

	<nav class="navbar navbar-expand-lg navbar-light bg-light">	
		<h1>Menus</h1>
	</nav>

	<section>
	<?php
	foreach ($restorantZomatoID as $value) {
		$jsonRestorant = getData($value, true);
		echo "<article>";
			echo "<h3 class='restorant'><a href='".$jsonRestorant['menu_url']."'>".$jsonRestorant['name']."</a></h3>";
			echo "<p class='address'>".$jsonRestorant['location']['address']."</p>";
			echo "<p class='userRate' style='color:#".$jsonRestorant['user_rating']['rating_color'].";''>".$jsonRestorant['user_rating']['aggregate_rating']." / 5</p>";




		$jsonDish = getData($value);
		if($jsonDish != 400){
			echo "<table>";
			foreach ($jsonDish['daily_menus'][0]['daily_menu']['dishes'] as $menu) {
				echo "<tr>";				
					echo "<th scope='row' class='price'>".$menu['dish']['name']."</th>";
					echo "<th scope='row' class='price'>".$menu['dish']['price']."</th>";
				echo "</tr>";
			}
			echo "</table>";
		}else{
			echo "<p id='noMenu'>No Daily Menu</p>";
		}
		echo "</article>";   
	}
	?>
	</section>

	<!-- Main table of links -->
	<footer>
		<!-- Load JS -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="js/custom.js"></script>
	</footer>
	</body>
</html>

<?php
	// We're done! Save the cached content to a file
	$fp = fopen($cachefile, 'w');
	fwrite($fp, ob_get_contents());
	fclose($fp);
	// finally send browser output
	ob_end_flush();
?>
