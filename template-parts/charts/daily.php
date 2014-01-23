<?php
	require_once(dirname(__FILE__)."/../dte.php");
	global $cl_utils;


	if(!isset($_GET["date"])) $date = time();
	else $date = $_GET["date"];
	$date = DateTime::createFromFormat("U",$date);
	$date->setTimeZone(new DateTimeZone(date_default_timezone_get()));
	$date->setTime(0,0,0);

	$is = (is_author())?"devuser":"project";
	$request = (is_author())?"project":"devuser";

	
	//Prepare number of lines
	$pretty = (is_author())?"Projects":"Users";
	$total_hours = 0;

$id = (is_author())?get_the_author_meta( 'ID' ):get_the_ID();
	if(is_author()){
		$dev = $id;
	}else{
		$proj = $id;
	}


	$clockins = $wpdb->get_results( "
		SELECT UNIX_TIMESTAMP( starttime ) AS starttime, UNIX_TIMESTAMP(stoptime) AS stoptime, duration, devuser, project 
		FROM clock_ins WHERE (
		(".$is." =".$id.")
		AND ( DAYOFMONTH( starttime ) = ".$date->format("j")." OR DAYOFMONTH(stoptime) = ".$date->format("j").")
		)
		ORDER BY ".$request.",starttime ASC
		", "OBJECT" );
		echo $id;

ob_start();?>
<div class="<?php echo $request ?>s daily <?php echo $is.$id; ?>" style="padding:10px;">
<h3><?php echo $pretty; ?></h3>
<ul class="vertical">
<?php	$current = '';
	foreach($clockins as $clockin){
		if($current != $clockin->$request){
			if($current != ''){
			?></ul></li><?php
			} ?><li><h4><?php			if($is == "project"){
				$name = get_the_author_meta("display_name",$clockin->$request);
				$href = get_author_posts_url( $clockin->$request);
				$dev = $clockin->$request;
			}else{
				$name = get_the_title($clockin->$request);
				$href = get_permalink( $clockin->$request);
				$proj = $clockin->$request;
			}
			$current = $clockin->$request;
			if(strlen($name) > 20) $name = substr($name,0,17)."...";
?><a href="<?php echo $href; ?>"><?php echo $name ?></a></h4><ul>
<?php	}
	$start = DateTime::createFromFormat("U", $clockin->starttime);
	if($clockin->duration == 0) $stop = new DateTime("NOW");
	else $stop = DateTime::createFromFormat("U", $clockin->stoptime);
 ?>
	<li>
		<span class="starttime">
			<span class="label">Started</span>
			<time datetime="<?php echo $start->format(DATE_W3C); ?>"><?php echo $start->format("H:m:s"); ?></time>
		</span>, <?php

		$projname = get_post_meta($proj, "full_name", true);
		$meta = get_user_meta($dev, 'clockin');
		$devname = $meta[0]["github"];
		$token = $meta[0]["token"];
		$url = "https://api.github.com/repos/".$projname."/commits";
		$url .= "?author=".$devname;
		$url .= "&since=".$start->format(DATE_W3C);
		$url .= "&until=".$stop->format(DATE_W3C);
		try{
			$response = $cl_utils::getUrl($url, $dev);
			$response = json_decode($response);
			foreach($response as $commit){
				$committime = DateTime::createFromFormat(DATE_W3C,$commit->commit->committer->date);
		?><span class="commit">
			<span class="label"><?php echo $commit->message; ?></span>
			<time datetime="<?php echo $committime->format(DATE_W3C); ?>"><?php echo $committime->format("H:m:s"); ?></time>
		</span>, <?php

			}
		}catch(Exception $e){
		
		}
		?><span class="<?php if($clockin->duration == 0) echo "false "; ?>stoptime">
			<span class="label"><?php if($clockin->duration == 0) echo "Still Going"; else echo "Stopped"; ?></span>
			<time datetime="<?php echo $stop->format(DATE_W3C); ?>"><?php echo $stop->format("H:m:s"); ?></time>
		</span>
	</li>
<?php
	
?>
<?php
	}
	if(count($clockins) > 0){
?>
</ul></li>
<?php } ?>
</ul>
<div class="chart" style="width:100%;height:200px;"></div>
</div>
<?php
$content = ob_get_clean();

?>
<div class="dailyreport">
<h2>Daily Report for <?php echo $date->format('m/d/Y'); ?></h2>
<?php
	echo $content;
?>
<script type="text/javascript">
	jQuery(function($) {
		var data = [];
		var ticks = [];
		var daystart = <?php echo $date->format("U")?>000;
		var dayend = daystart + 24*60*60*1000;
		$(".daily.<?php echo $is.$id ?>>ul>li").each(function( indexli, value ) {
			var el = $(this);
			var title = el.find("h4").html();
			var elems = el.find("ul>li");
			ticks.push([indexli+1, title]);
			elems.each(function( indexel, value){
				data.push([Date.parse($(value).find(".starttime>time").attr("datetime")),indexli+1]);
				data.push([Date.parse($(value).find(".stoptime>time").attr("datetime")), indexli+1]);
				data.push(null);
			});
		});
				
		console.log(data);
		$.plot(".daily.<?php echo $is.$id ?>>.chart", [ data ], {
			series: {
				lines: {
					show: true,
					lineWidth:50
				}
			},
			grid:{
			markings: function (axes) {
				var markings = [];
				for (var x = Math.floor(axes.xaxis.min); x < axes.xaxis.max; x += (120*60*1000))
					markings.push({ xaxis: { from: x, to: x + (60*60*1000) } });
				return markings;
			}},
			xaxis: {
				min:daystart,max:dayend,
				mode: "time",
			},

			yaxis: {
				min:0.5,max:ticks.length+.5,
				ticks: ticks
			}
		});

	});	
</script>
</div>