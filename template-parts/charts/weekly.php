<?php

$counter=0;

if(!isset($date)) $date = time();
$date = DateTime::createFromFormat("U",$date);


$date->setTimeZone(new DateTimeZone(date_default_timezone_get()));


$date->setTime(0,0,0);
$day_of_week = $date->format("w");
$date->modify(-$day_of_week." day");

$b = $date->format("U");
$date->modify("+7 day");
$e = $date->format("U");

$is = (is_author())?"devuser":"project";
$request = (is_author())?"project":"devuser";
$id = get_the_ID();


$days = $wpdb->get_results( "
	SELECT SUM(CASE WHEN duration=0 THEN UNIX_TIMESTAMP(DATEDIFF(CURRENT_TIMESTAMP(), starttime)) ELSE duration END) AS day_total
	FROM clock_ins WHERE ((".$is." =".$id.")
	AND ( UNIX_TIMESTAMP( starttime ) 
		BETWEEN ".$b." 
		AND ".$e."
		)
	)GROUP BY floor(( UNIX_TIMESTAMP(starttime)- ".$b.") / 86400)
	", "OBJECT" );
	
//Parsing into days
//creating the image with map
	$im = @imagecreate(490, 240)
		or die("Cannot Initialize new GD image stream");
	
	$background_color = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);

	$colors = array(
		imagecolorallocate($im, 0xFF, 0x00, 0x00),
		imagecolorallocate($im, 0xFF, 0x77, 0x00),
		imagecolorallocate($im, 0xFF, 0xFF, 0x00),
		imagecolorallocate($im, 0x00, 0xFF, 0x00),
		imagecolorallocate($im, 0x00, 0x00, 0xFF),
		imagecolorallocate($im, 0xFF, 0x00, 0xFF)
	);
	$doc = new DOMDocument();
	$doc->loadHTML("<map name=\"weeklyreport\"></map>");
	$html = simplexml_import_dom($doc);
	$map = $html->body->map;
	
	foreach($days as $key=>$d){
		$rectnum = 0;
		$dq = $d / 360;
		while($dq > 0){
			if($rectnum == 5 || $dq < 20){
				$height = 240 - $d/360;
			}else $height = 240 - ($rectnum+1)*20;
			
			imagefilledrectangle ( $im , 
				10+$key*70, 240-$rectnum*20,
				60+$key*70, $height,
				$colors[$rectnum]
			);
			$dq -= 20;
			$rectnum++;
			$counter++;
		}
		$area = $map->addChild("area");
		$area->addAttribute("shape","rect");
		$area->addAttribute("coords", (10+$key*70).",".(240).",".(60+$key*70).",".($height));
		
		$h = floor($d/3600);
		$m = round(($d%3600)/60);
		
		$area->addAttribute("title","Total hours: ".$h.":".$m);
	}

	ob_start ();
	imagepng ($im);
	$image_data = ob_get_contents ();
	imagedestroy($im);
	ob_end_clean ();

	$i64 = base64_encode ($image_data);
?>	
<div>
<h1>Weekly Report</h1>
<img src="data:image/png;base64, <?php echo $i64; ?>" usemap="#weeklyreport" />
<?php echo $map->asXML(); ?>
</div>
