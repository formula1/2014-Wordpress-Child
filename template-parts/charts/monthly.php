<?php
include_once(dirname(__FILE__)."/../Calander.php");
include_once dirname(__FILE__)."/pie.php";

global $url;
$url = (is_author())?get_author_posts_url(get_the_author_meta( 'ID' )):get_permalink();


$is = (is_author())?"devuser":"project";
$request = (is_author())?"project":"devuser";
$id = (is_author())?get_the_author_meta( 'ID' ):get_the_ID();
if(is_author()){
	$search = get_user_meta(get_the_author_meta('ID'), 'github', true );
}else $search = get_post_meta(get_the_ID(), "github-full_name", true);
global $cl_monthlycounter;
if(!isset($cl_monthlycounter)) $cl_monthlycounter=0;
else $cl_monthlycounter++;

global $leftovers;
$leftovers = array();
class dailyclockins extends CalenderUI{

	public function day_data($string){
		global $url;
		$day = new DateTime($string);
		$day->setTimeZone(new DateTimeZone(date_default_timezone_get()));
		$day->setTime(0,0,0);
		$ds = $day->format("U");
		$de = $ds+86400;
		global $mdury;
		global $leftovers;
		$total = 0;
		
		while(isset($leftovers[0]) && $leftovers[0]["day"] == $day->format("j")){
			if($leftovers[0]["stoptime"] >  $de){
				array_push($leftovers, array("day"=>($leftovers[0]["day"]+1), "starttime"=>$de, "stoptime"=>$leftovers[0]["stoptime"]));
				$leftovers[0]["stoptime"] = $de;
			}
			$total += $leftovers[0]["stoptime"] - $leftovers[0]["starttime"];
			array_shift($leftovers);
		}
		
		
		while(isset($mdury[0]) && $mdury[0]->day <= $day->format("j")){
			if($mdury[0]->starttime < $ds) $mdury[0]->starttime = $ds;
			if($mdury[0]->stoptime == 0) $mdury[0]->stoptime = time();
			if($mdury[0]->stoptime >  $de){
				array_push($leftovers, array("day"=>($mdury[0]->day+1), "starttime"=>$de, "stoptime"=>$mdury[0]->stoptime));
				$mdury[0]->stoptime = $de;
			}
			$total += $mdury[0]->stoptime - $mdury[0]->starttime;
			array_shift($mdury);
		}
		
		if($total > 0){
			$di = new DateIntervalEnhanced("PT".$total."S"); 
			$di->recalculate();

			$ret =  '<a class="dailychoice" href="'.$url.'?date='.$ds.'">
						<time class="amountoftime" datetime="'.$di->format("%h:%I:%s").'">'.$di->format("%h:%I").'</time> hours worked
					</a>';
		}else{
			$ret =  '<time class="amountoftime" datetime="0:00:00">no work</time>';
		}
		$ret .='<div class="chart" style="width:100%;height:100%;position:absolute;top:0px;left:0px;z-index:1;" ></div>';
		
		return $ret;
		
//		$date = new DateTime();
//		$date->modify
		
	}
};



if(isset($_GET["date"])){
$dizzle = DateTime::createFromFormat("U", $_GET["date"]);
$month = $dizzle->format("m");
$year = $dizzle->format("Y");
}else{
$month = (isset($_GET["month"]))?$_GET["month"]:date("m");
$year = (isset($_GET["year"]))?$_GET["year"]:date("Y");
}



$start = new DateTime('01-'.$month.'-'.$year);

if($month != 1) $before = new DateTime('01-'.($month-1).'-'.($year));
else $before = new DateTime('01-12-'.($year-1));
if($month != 12) $after = new DateTime('01-'.($month+1).'-'.$year);
else $after = new DateTime('01-1-'.($year+1));
?>
<div class="cl_month <?php echo $is.$id ?>">
<h1><?php echo __("Monthly Report"); ?></h1>
<div class="monthchooser" style="text-align:center;">
	<a class="monthchoice" href="<?php echo $url."?date=".$before->format("U"); ?>">&#60;&#60;</a><?php
	?><span><?php echo $start->format("F"); ?></span><?php
	?><a class="monthchoice" href="<?php echo $url."?date=".$after->format("U"); ?>">&#62;&#62;</a>
</div>
<?php 
$cal = new dailyclockins();

global $mdury;
$mdury = $wpdb->get_results("SELECT DAYOFMONTH( FROM_UNIXTIME(starttime) ) AS day, starttime, stoptime
FROM clock_ins
WHERE ( 
	( ".$is." = '".$search."')
	AND	( 
			starttime BETWEEN ".$start->format("U")." AND ".$after->format("U")." 
		OR	stoptime BETWEEN ".$start->format("U")." AND ".$after->format("U")." 
		OR  (stoptime = 0 && UNIX_TIMESTAMP() BETWEEN ".$start->format("U")." AND ".$after->format("U").")
	)
) ORDER BY starttime
", "OBJECT");

echo $cal->get_calender($month,$year);?>
</div>
<script type="text/javascript">
jQuery(function($){
	$(".cl_month.<?php echo $is.$id ?> table td.calendar-day").each(function(index, value){
		var input = moment.duration($(value).find(".amountoftime").attr("datetime")).asSeconds();
		console.log(input);
		var data = [{label:'',data:input}, {label:'',data:86400-input}]
		$.plot('.cl_month.<?php echo $is.$id ?> table td.calendar-day:eq('+index+') .chart', data, {
			series: {
				pie: {
					show: true,
					radius: 1,
					label: {
						show: false
					}
				}
			},
			legend: {
				show: false
			}
		});
	
	});


});
</script>