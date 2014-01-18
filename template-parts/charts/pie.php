<?php

function pie($radius, $amount, $limit){

		$im=imagecreatetruecolor($radius*2, $radius*2);

		imagesavealpha( $im, true );
		$rgb = imagecolorallocatealpha( $im, 0, 0, 0, 127 );
		imagefill( $im, 0, 0, $rgb );
		$pi = imagecolorallocatealpha($im, 0x00, 0x00, 0xFF, 63);
		imagefilledellipse($im, $radius-1, $radius-1, $radius*2, $radius*2, $pi);

		if(gettype($amount) == "array"){
			$pie = imagecolorallocatealpha($im, 0xFF, 0x77, 0x00, 63);
			foreach($clockins as $cl){
			/*
			need to scale each difference by to clock
			*/
				$start = $cl->start;
				$stopped = ($cl->duration == 0)?time():$cl->duration;
				$degreestart = round(360*($start)/$limit);
				$degreeend = round(360*($start+$stopped)/$limit);
				
				if($degreestart == $degreeend) $degreeend++;
				
				imagefilledarc($im, $radius-1, $radius-1, $radius*2, $radius*2, -90+$degreestart, -90+$degreeend,  $pie, IMG_ARC_PIE);
			}
		}else if(gettype($amount) == "integer"){
				$fra = $amount/$limit;
				$c = 0;
				while($fra > 0){
					$m = min($fra,1);
					$pie = imagecolorallocatealpha($im, 0xFF, 0x77+$c*(0x0F), $c*(0x1E), 63);
					imagefilledarc($im, $radius-1, $radius-1, $radius*2, $radius*2, -90, -90+round(360*$fra),  $pie, IMG_ARC_PIE);
					$fra--;
					$c++;
				}
		}
		else throw new Exception("can't work with unknown type");
		
		ob_start (); 
		imagepng ($im);
		$image_data = ob_get_contents (); 
		imagedestroy($im);
		ob_end_clean (); 

		return base64_encode ($image_data);	
		


}

?>