<?php
require_once(dirname(__FILE__)."/../dte.php");

$dow = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');


if(!isset($_GET["date"])) $date = time();
else $date = $_GET["date"];
$date = DateTime::createFromFormat("U",$date);

$tz = (get_option('timezone_string') !== null)?get_option('timezone_string'):'UTC';
date_default_timezone_set($tz);

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
	SELECT DAYOFWEEK( starttime ) AS day , 
	SUM( CASE duration
		WHEN 0 THEN	UNIX_TIMESTAMP()-UNIX_TIMESTAMP(starttime)
		ELSE	duration
		END
	) AS day_total 
	FROM clock_ins
	WHERE ( 
		( ".$is." = ".$id.")
	AND	( UNIX_TIMESTAMP( starttime )  BETWEEN ".$b." AND ".$e." )
	)GROUP BY DAYOFWEEK(starttime)
", "OBJECT" );
	
//Parsing into days
//creating the image with map
	$im = @imagecreate(430, 240)
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
	$dd = array();
	$date = DateTime::createFromFormat("U",$b);
	foreach($days as $key=>$d){
		$rectnum = 0;
		$dq = $d->day_total / 360;
		while($dq > 0){
			if($rectnum == 5 || $dq < 20){
				$height = 240 - $d->day_total/360;
			}else $height = 240 - ($rectnum+1)*20;
			
			imagefilledrectangle ( $im , 
				10+$d->day*60, 240-$rectnum*20,
				50+$d->day*60, $height,
				$colors[$rectnum]
			);
			$dq -= 20;
			$rectnum++;
		}
		$date->modify("+".$d->day." day");
		$area = $map->addChild("area");
		$area->addAttribute("shape","rect");
		$area->addAttribute("coords", (10+$d->day*60).",".floor(240 -$d->day_total / 360).",".(50+$d->day*60).",".(240));
		$area->addAttribute("href", get_permalink()."?date=".$date->format("U"));
		
		$di = new DateIntervalEnhanced("PT".$d->day_total."S"); 
		$h = floor($d->day_total/3600);
		$m = round(($d->day_total%3600)/60);
		$dd[$d->day] = $di->recalculate()->format("%h:%I");
		
		$area->addAttribute("title","Total hours: ".$dd[$d->day]);
	$date = DateTime::createFromFormat("U",$b);
	}

	ob_start ();
	imagepng ($im);
	$image_data = ob_get_contents ();
	imagedestroy($im);
	ob_end_clean ();

	$i64 = base64_encode ($image_data);

	
$date = DateTime::createFromFormat("U",$b);	
?>	
<div class="cl-weekly-report">
<h1><?php echo __("Weekly Report"); ?></h1>
<h6><?php echo __("Starting on")." ".$date->format("m/d/Y" ); ?></h6>
<img src="data:image/png;base64, <?php echo $i64; ?>" usemap="#weeklyreport" />
<?php echo $map->asXML();
?>
<ul class="horizontal cl-weekly-days"><?php
for($i=0;$i<7;$i++){
?><li><a href="<?php echo get_permalink()."?date=".$date->format("U") ?>"><?php
echo __( $date->format("l" ))."<br/>"; 
echo $date->format("d")."<br/>";
if(isset($dd[$i])){
echo $dd[$d->day];
}else echo "0:00";
?></a></li><?php
$date->modify("+1 day");
}

?></ul>
</div>
