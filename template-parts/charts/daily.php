<?php
	require_once(dirname(__FILE__)."/../dte.php");


	if(!isset($_GET["date"])) $date = time();
	else $date = $_GET["date"];
	$date = DateTime::createFromFormat("U",$date);
	$date->setTimeZone(new DateTimeZone(date_default_timezone_get()));
	$date->setTime(0,0,0);
	$b = $date->format("U");
	$date->modify("+1 days");
	$e = $date->format("U");
	$date->modify("-1 days");

	$doc = new DOMDocument();
	$doc->loadHTML("<div class=\"projects list inline\" style=\"vertical-align:top;padding:10px;border:1px solid #DDD;\"></div>");
	$temp = simplexml_import_dom($doc);
	$phtml = $temp->body->div;

	//Prepare number of lines
	$lines = array();
	
	$total_hours = 0;
	$phtml->addChild("h3", "Projects");
	$ul = $phtml->addChild("ul");
	$ul->addAttribute("class","vertical");
	$is = (is_author())?"devuser":"project";
	$request = (is_author())?"project":"devuser";

$id = (is_author())?get_the_author_meta( 'ID' ):get_the_ID();
	if(is_author()){
		$dev = $id;
	}else{
		$proj = $id;
	}


	$clockins = $wpdb->get_results( "
		SELECT UNIX_TIMESTAMP( starttime ) AS starttime, duration, devuser, project 
		FROM clock_ins WHERE ((".$is." =".$id.")
		AND ( DAYOFMONTH( starttime ) = ".$date->format("j").
//			BETWEEN ".$b." 
//			AND ".$e."
"			)
		)", "OBJECT" );
	foreach($clockins as $clockin){
		if(array_key_exists($clockin->$request, $lines)){
			array_push($lines[$clockin->$request], $clockin);
			continue;
		}else{
			$lines[$clockin->$request] = array($clockin);
			$li = $ul->addChild("li");

			if($is == "project"){
				$name = get_the_author_meta("display_name",$clockin->$request);
				$href = get_author_posts_url( $clockin->$request);
				
			}else{
				$name = get_the_title($clockin->$request);
				$href = get_permalink( $clockin->$request);
			}
			if(strlen($name) > 20) $name = substr($name,0,17)."...";
			$a = $li->addChild("a", $name);
			$a->addAttribute("href",$href);
		}
	}

	$scale = .25;
	
	$im = @imagecreate(864*$scale, max(20,count($lines)*20))
		or die("Cannot Initialize new GD image stream");
	
	$doc = new DOMDocument();
	$doc->loadHTML("<map name=\"dailyreport\"></map>");
	$map = simplexml_import_dom($doc);

	$background_color = imagecolorallocate($im, 0, 0, 0);

	$inactive = imagecolorallocate($im, 0xFF, 0x77, 0xFF);
	$active = imagecolorallocate($im, 0xff, 0xff, 0x77);

	$scolor = imagecolorallocate($im, 0x00, 0xff, 0x00);
	$ecolor = imagecolorallocate($im, 0xff, 0x00, 0x00);

	$comcol = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
	
	$createcol = imagecolorallocate($im, 0xFF, 0x77, 0x00);
	$savecol = imagecolorallocate($im, 0x77, 0x77, 0x77);
	$deletecol = imagecolorallocate($im, 0x00, 0x00, 0xFF);

	imagesetthickness($im, 3);


	$offset = 10;
	$num = 0;
	foreach($lines as $key=>$line){
		if(is_author()){
			$proj = $key;
		}else{
			$dev = $key;
		}
		$offset = 10 + $num*20;
		$num++;
		imageline($im, 0,$offset,864*$scale,$offset, $inactive);
		foreach($line as $cl){
			$s_mm = max($cl->starttime-$b, 0);
			$slx = round($scale*$s_mm/100);
			
			imagefilledrectangle ( $im , 
				$slx-4, $offset-4,
				$slx+4 , $offset+4,
				$scolor 
			);
			
			$stopped = ($cl->duration != 0)?$cl->duration+$cl->starttime:time();
			$ecol = ($cl->duration != 0)?$ecolor:$active;
			$e_mm = min($stopped-$b, 86400);
			
			$elx = $scale*$e_mm/100;
			imageline($im,
				$slx,$offset, 
				$elx,$offset, 
				$active
			);

			$total_hours += $stopped - $cl->starttime;
			

			imagefilledrectangle ( $im , 
				$elx-4, $offset-4,
				$elx+4 , $offset+4,
				$ecol 
			);

			$d = DateTime::createFromFormat("U",intval($cl->starttime));
			$d->setTimeZone(new DateTimeZone(date_default_timezone_get()));

			$stf = date_format($d, 'm/d/Y H:i:s');
			
			$mizzle = $map->body->map;
			$area = $mizzle->addChild("area");
			$area->addAttribute("shape","rect");
			$area->addAttribute("coords", ($slx-4).",".($offset-4).",".($slx+4).",".($offset+4));
			$area->addAttribute("title","Started:".$stf);

			$d = DateTime::createFromFormat("U",intval($stopped));
			$d->setTimeZone(new DateTimeZone(date_default_timezone_get()));
			$etf = date_format($d, 'm/d/Y H:i:s');

			
			$area = $mizzle->addChild("area");
			$area->addAttribute("shape","rect");
			$area->addAttribute("coords", ($elx-4).",".($offset-4).",".($elx+4).",".($offset+4));
			if($cl->duration != 0){
				$area->addAttribute("title","Ended:".$etf);
			}else{
				$area->addAttribute("title","Still Going:".$etf);
			}

			
/*			$works = DevWork::find(array("clockin"=>$cl->ID), 100);
			foreach($works as $w){
				if($w->time-$b < 0 || $w->time-$b > 86400) continue;
			
				$slx = ($w->time-$b)/200;
				if($w->type == "create") $col = $ccol;
				if($w->type == "save") $col = $scol;
				if($w->type == "delete") $col = $dcol;

				
				imagefilledellipse ( $im , 
					$slx, $offset,
					8 , 8,
					$col
				);

				
				$d = DateTime::createFromFormat("U",intval($w->time));
				$d->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				$stf = date_format($d, 'm/d/Y H:i:s');

				$area = $mizzle->addChild("area");
				$area->addAttribute("shape","circle");
				$area->addAttribute("coords", ($slx).",".($offset).","."8");
				$area->addAttribute("title",$w->type.":".$stf);
			}
*/			
		}
		
		$proj = get_post($proj);
		$meta = get_user_meta($dev, 'clockin');
		$dev = $meta[0]["github"];
		$token = $meta[0]["token"];
		print_r($meta);
		echo $proj->post_title;
		$h = array();
		array_push($h, 'User-Agent: Clock-In-Prep');
		$url = "https://api.github.com/repos/".$proj->post_title."/git/commits";
		$url .= "?author=".$dev;
		$url .= "&since=".$date->format('Y-m-d').'T00:00:00Z';
		$date->modify("+1 days");
		$url .= "&until=".$date->format('Y-m-d').'T00:00:00Z';
		$url .="&access_token=".$token;

		echo $url;
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		// Set so curl_exec returns the result instead of outputting it.
		curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		// Get the response and close the channel.
		$response = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($http_status != 200 && $http_status != 301){
			throw new Exception($http_status);
		}
		
		$response = json_decode($response);
		foreach($response as $commit){
			$time = DateTime::createFromFormat("Y-m-d*H:i:s*",$commit->committer->date);
			$slx = $scale*($time->format("U")-$b)/100;
			$col = $comcol;
			$col = $dcol;

			imagefilledellipse ( $im , 
				$slx, $offset,
				8 , 8,
				$col
			);

			$area = $mizzle->addChild("area");
			$area->addAttribute("shape","circle");
			$area->addAttribute("coords", ($slx).",".($offset).","."8");
			$area->addAttribute("title","Commit:".$time->format("H:i"));
		}

	}
				
		
	ob_start (); 
	imagepng ($im);
	$image_data = ob_get_contents (); 
	imagedestroy($im);
	ob_end_clean (); 

	$i64 = base64_encode ($image_data);	

?>
<div class="dailyreport">
<h2>Daily Report for <?php echo date_format(DateTime::createFromFormat("U",$b,new DateTimeZone(date_default_timezone_get())), 'm/d/Y'); ?></h2>

<?php
	echo $phtml->asXML();

	$di = new DateIntervalEnhanced("PT".$total_hours."S"); 
	$t = $di->recalculate()->format("%h:%I");
	
?><div class="image-hold inline" style="vertical-align:top;padding:10px;border:1px solid #DDD;">
<h3>Total hours : <?php echo $t; ?></h3>
<img src="data:image/png;base64, <?php echo $i64; ?>" usemap="#dailyreport" />
<?php echo $map->asXML(); ?>
</div>
</div>
<?php 
date_default_timezone_set('UTC');
