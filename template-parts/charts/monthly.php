<?php
include_once(dirname(__FILE__)."/../Calander.php");
include_once dirname(__FILE__)."/pie.php";

global $url;
$url = (is_author())?get_author_posts_url(get_the_author_meta( 'ID' )):get_permalink();


$is = (is_author())?"devuser":"project";
$request = (is_author())?"project":"devuser";
$id = (is_author())?get_the_author_meta( 'ID' ):get_the_ID();
global $cl_monthlycounter;
if(!isset($cl_monthlycounter)) $cl_monthlycounter=0;
else $cl_monthlycounter++;


class dailyclockins extends CalenderUI{

	public function day_data($string){
		global $url;
		$day = new DateTime($string);
		$day->setTimeZone(new DateTimeZone(date_default_timezone_get()));
		$day->setTime(0,0,0);
		$ds = $day->format("U");
		global $md_dury;
		if(isset($md_dury[$day->format("j")])){
		$i64 = pie(25, intval($md_dury[$day->format("j")]->day_total), 24*60*60);
			return '<a class="dailychoice" href="'.$url.'?date='.$ds.'">
				<img src="data:image/jpeg;base64, '.$i64.'" />
			</a>';
		}else{
			$i64 = pie(25, 0, 1);
		return '<img src="data:image/jpeg;base64, '.$i64.'" />';

		}
		
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
<div class="cl_month">
<h1><?php echo __("Monthly Report"); ?></h1>
<div class="monthchooser" style="text-align:center;">
	<a class="monthchoice" href="<?php echo $url."?date=".$before->format("U"); ?>">&#60;&#60;</a><?php
	?><span><?php echo $start->format("F"); ?></span><?php
	?><a class="monthchoice" href="<?php echo $url."?date=".$after->format("U"); ?>">&#62;&#62;</a>
</div>
<?php 
$cal = new dailyclockins();

global $md_dury;
$md_dury = $wpdb->get_results( "
	SELECT DAYOFMONTH( starttime ) AS day , 
	SUM( CASE duration
		WHEN 0 THEN	UNIX_TIMESTAMP()-UNIX_TIMESTAMP(starttime)
		ELSE	duration
		END
	) AS day_total 
	FROM clock_ins
	WHERE ( 
		( ".$is." = ".$id.")
	AND	( UNIX_TIMESTAMP( starttime )  BETWEEN ".$start->format("U")." AND ".$after->format("U")." )
	)GROUP BY DAYOFMONTH(starttime)
	
	LIMIT 50
	", "OBJECT_K" );
	

echo $cal->get_calender($month,$year);?>
</div>
<?php

date_default_timezone_set('UTC');
?>