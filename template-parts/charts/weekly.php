<?php
require_once(dirname(__FILE__)."/../dte.php");

$dow = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');


if(!isset($_GET["date"])) $date = time();
else $date = $_GET["date"];
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
$permalink = (is_author())?get_author_posts_url(get_the_author_meta( 'ID' )):get_permalink();
$id = (is_author())?get_the_author_meta( 'ID' ):get_the_ID();

$cis = $wpdb->get_results( "
SELECT ".$request.", starttime, stoptime
FROM clock_ins
WHERE ( 
	( ".$is." = ".$id.")
	AND	( 
			starttime BETWEEN ".$b." AND ".$e." 
		OR	stoptime BETWEEN ".$b." AND ".$e." 
		OR  (stoptime = 0 && UNIX_TIMESTAMP() BETWEEN ".$b." AND ".$e.")
	)
)
", "OBJECT");


	$prepdays = array();
	$dd = array();
	$day = 24*60*60;
	$now = new DateTime("NOW");
	
	function createDay($day, $time, &$dd, $name){
		if(!isset($dd[$day])) $dd[$day] = array("#total"=>0);


		if(!isset($dd[$day][$name])) $dd[$day][$name] = $time;
		else $dd[$day][$name] += $time;

		$dd[$day]["#total"] +=$time;
	}
	$urls = array();
	foreach($cis as $k=>$ci){
		if(is_author()){
			$name = get_the_title($ci->$request);
			$urls[$name] = get_permalink($ci->$request);
		}else{
			$name = get_the_author_meta("display_name",$ci->$request);
			$urls[$name] = get_author_posts_url($ci->$request);
		}

		$st = DateTime::createFromFormat("U", $ci->starttime);
		if($ci->stoptime == 0)
			$et = $now;
		else
			$et = DateTime::createFromFormat("U", $ci->stoptime);
		if($st->format("w") != $et->format("w")){
			$leftover = $st->format("U")%86400;
			createDay($st->format("w"), 86400 - $leftover, $dd, $name);
			$daystart = DateTime::createFromFormat("U", $st->format("U")-$leftover + 86400);
			$w = $st->format("w");
			$counter = 0;
			while($et->format("w") >= $daystart->format("w") && $w+$counter < 7 ){
				createDay($daystart->format("w"), min(86400, $et->format("U")-$daystart->format("U")), $dd, $name);
				$daystart->modify("+1 day");
				$counter++;
			}
		}else createDay($st->format("w"), $et->format("U")-$st->format("U"), $dd, $name);
	}
	
	
$date = DateTime::createFromFormat("U",$b);	
?>	
<div class="cl-weekly-report <?php echo $is.$id; ?>">
<h1><?php echo __("Weekly Report"); ?></h1>
<span><?php echo __("Starting on")." ".$date->format("m/d/Y" ); ?></span><br/>
<ul class="horizontal cl-weekly-days"><?php
for($i=0;$i<7;$i++){

	?><li><h4><?php
	if(isset($dd[$i])){ 
		?><a href="<?php echo $permalink."?date=".$date->format("U"); ?>"><?php
	}?>
		<time class="title" datetime="<?php echo $date->format(DATE_W3C); ?>"><?php 
	echo __( $date->format("l" ))."<br/>"; 
	echo $date->format("d")."</time><br/>";
	if(isset($dd[$i])){
		$di = new DateIntervalEnhanced("PT".$dd[$i]["#total"]."S"); 
		$di->recalculate();
			?><time datetime="<?php echo $di->format("%h:%i:%s"); ?>"><?php echo $di->format("%h:%I"); ?></time>
		</a><?php
	}else{ ?>
		<time datetime='0:0:0'>no work</time><?php
	}
?></h4><?php 
	if(isset($dd[$i])){ 
	?><ul><?php
		foreach($dd[$i] as $k=>$v){
		if($k == "#total") continue;
		$di = new DateIntervalEnhanced("PT".$v."S"); 
		$di->recalculate();

		?><li><span class="label"><a href="<?php echo $urls[$k]; ?>"><?php echo $k; ?></a></span>, <span class="value" >
			<time datetime="<?php echo $di->format("%h:%i:%s"); ?>"><?php echo $di->format("%h:%I"); ?></time></span></li>
		<?php
		}
	?></ul><?php
	}
?></li><?php
$date->modify("+1 day");
}

?></ul>
<div class="chart" style="width100%;height:200px;">
</div>
</div>
<script type="text/javascript">


	jQuery(function($) {
		var data = [];
		var ref = [];
		var ticks = [];
		$(".cl-weekly-report.<?php echo $is.$id ?>>ul>li").each(function( indexli, value ) {
			var el = $(this);
			var title = moment(el.find("h4 time.title").attr("datetime"));
			title.zone(0);
			var elems = el.find("ul>li");
			console.log(title.date(), title.month());
			ticks.push([indexli, title.date()+"/"+(title.month()+1)]);
			if(elems.length > 0){
				elems.each(function( indexel, value){
					var key = $(this).find(".label").html();
					if(typeof ref[key] == "undefined"){ ref[key] = ref.length; data[ref[key]] = {label:key,data:[]};}
					data[ref[key]].data.push([indexli, moment.duration($(this).find(".value>time").attr("datetime")).asMilliseconds()]);
				});
			}
		});
		console.log(ticks);
		$.plot(".cl-weekly-report.<?php echo $is.$id ?>>.chart", data, {
			series: {
				stack: 0,
				lines: {
					show: false,
					fill: true,
					steps: false
				},
				bars: {
					show: true,
					barWidth: 0.6,
					align:"center"
				}
			},
			legend: {
				show:true,
				backgroundOpacity:0.0
			},
			yaxis: {
				mode: "time"
			},

			xaxis: {
				min:-.5,max:6.5,
				ticks: ticks
			}
		});
	});

	
</script>