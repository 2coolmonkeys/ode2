<?php

function rd2wgs ($x, $y)
{
	// Calculate WGS84 cošrdinates
	$dX = ($x - 155000) * pow(10, - 5);
	$dY = ($y - 463000) * pow(10, - 5);
	$SomN = (3235.65389 * $dY) + (- 32.58297 * pow($dX, 2)) + (- 0.2475 *
			pow($dY, 2)) + (- 0.84978 * pow($dX, 2) *
					$dY) + (- 0.0655 * pow($dY, 3)) + (- 0.01709 *
							pow($dX, 2) * pow($dY, 2)) + (- 0.00738 *
									$dX) + (0.0053 * pow($dX, 4)) + (- 0.00039 *
											pow($dX, 2) * pow($dY, 3)) + (0.00033 * pow(
													$dX, 4) * $dY) + (- 0.00012 *
															$dX * $dY);
	$SomE = (5260.52916 * $dX) + (105.94684 * $dX * $dY) + (2.45656 *
			$dX * pow($dY, 2)) + (- 0.81885 * pow(
					$dX, 3)) + (0.05594 *
							$dX * pow($dY, 3)) + (- 0.05607 * pow(
									$dX, 3) * $dY) + (0.01199 *
											$dY) + (- 0.00256 * pow($dX, 3) * pow(
													$dY, 2)) + (0.00128 *
															$dX * pow($dY, 4)) + (0.00022 * pow($dY,
																	2)) + (- 0.00022 * pow(
																			$dX, 2)) + (0.00026 *
																					pow($dX, 5));

	$Latitude = 52.15517 + ($SomN / 3600);
	$Longitude = 5.387206 + ($SomE / 3600);

	return array(
			'latitude' => $Latitude ,
			'longitude' => $Longitude);
}


require_once 'citysdkfunctions.php';

$citySDKProxy = new CitySDKProxy();
$layer = "2cm.vergunningen";

if (!$citySDKProxy->startSession()){
	die($citySDKProxy->message);
} else {
	//$citySDKProxy->deleteNodes($layer);
	//$citySDKProxy->destroySession();

}
$filename="vergunning.json";
$data = file_get_contents($filename);

$item_arr = json_decode($data, true);


foreach ($item_arr["vergunning"] as $item){
	$location = $item["location"];
	$location_arr = explode(",", $location);
	$x = $location_arr[0];
	$y = $location_arr[1];
	$rd_arr = rd2wgs ($x, $y);
	$lat = $rd_arr["latitude"];
	$lon = $rd_arr["longitude"];
	$url = "http://api.citysdk.waag.org/nodes?layer=bag.vbo&geom&per_page=1&lat=$lat&lon=$lon";
	$t = file_get_contents($url);
	$json  = json_decode($t);
	$results = $json->results;
	if (count($results)>0){
		$cdk_id =  $results[0]->cdk_id;
		$data = $item;
		$res = $citySDKProxy->addData($cdk_id, $layer, $data);
		echo $res;
	}
}
$citySDKProxy->destroySession();

?>
