<?php

require_once 'citysdkfunctions.php';
require 'solr.class.php';

$solr = new Solr('<your solr repository>');

function getRandomArray($arr) {
	$r =  count($arr)-1;
	$txt = trim($arr[rand(0,$r)]);
	return $txt;
}

function createRandomData($ip_id,$citySDKProxy, $cdk_id, $layer, $label, $arr){
	global $idSite, $idSite, $auth;
	$stub_data = array();
	$name = getRandomArray($arr);
	if ($name!=""){
		$stub_data["warning"] = "this is stub data";
		$stub_data[$label] = $name;
		$stub_data["datum"] = date("Y-m-d H:i:s");
		echo "cdk_id: $cdk_id, layer: $layer\n";
		var_dump($stub_data);
		
		$res = $citySDKProxy->addData($cdk_id, $layer, $stub_data);
		echo $res;
	} else {
		echo "no results!";
	}
	
	$t = new PiwikTracker( $idSite, $pwiki_url);
	$t->setTokenAuth( $auth);
	$t->setUrl( $url = $citySDKProxy->lasturl );
	$t->setCustomVariable( 1, 'user', $label );
	$t->setIp( "127.0.0.$ip_id" );
	$t->doTrackPageView("added data to $layer");	
	return $name;
}


$nodes = array();
$citySDKProxy = new CitySDKProxy();
$layer = "2cm.p2000bag";
if (!$citySDKProxy->startSession()){
	die($citySDKProxy->message);
} else {
	//	$citySDKProxy->deleteNodes($layer);
	//$citySDKProxy->destroySession();
}

$crawlids = array(1,2,3);
$crawlsnames = array("Brandweer","Ambulance","Politie");

$t0 = new PiwikTracker( $idSite, $pwiki_url);
$t0->setTokenAuth( $auth);
$t0->setCustomVariable( 1, 'user', "p2000 crawler" );
$t0->setIp( "127.0.0.1" );

for ($j = 0; $j < 3; $j++){

	$purl = 'http://feeds.livep2000.nl/?d=' . $crawlids[$j];
	$t0->setUrl( $purl );
	$t0->doTrackPageView("crawl " . $crawlsnames[0]);
	
	echo $purl . "\n";
	$ch = curl_init($purl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds
	$xml_str = curl_exec($ch);
	$DOM = new DOMDocument;
	$DOM->loadXML($xml_str);

	//get all H1
	$items = $DOM->getElementsByTagName('item');

	for ($i = 0; $i < $items->length; $i++){
		$elem =  $items->item($i);

		$data = array();

		foreach($elem->childNodes as $child) {
			$val = trim(strtolower($child->nodeValue));
			$node = $child->nodeName;
			if (strlen($val)>0){
				$node = str_ireplace(array("ms:","gml:"), "", $node);
				$node = trim(strtolower($node));
				$data[$node] = $val;
			}
		}

		if(isSet($data["geo:lat"])){
			$x = $data["geo:lat"];
			$y = $data["geo:long"];
			$data["afzender"] = $crawlsnames[$j];

			unset($data["geo:lat"]);
			unset($data["geo:long"]);
			
			$url = "http://api.citysdk.waag.org/nodes?layer=bag.vbo&geom&per_page=1&lat=$x&lon=$y";
			
			$t = file_get_contents($url);
			$t = str_ireplace("bag.vbo", "bag_vbo", $t);
				
			$json  = json_decode($t);
			
			$results = $json->results;
			
			if (count($results)>0){
				$cdk_id =  $results[0]->cdk_id;
				$geom =  $results[0]->geom->coordinates;
				$cdk_id = str_ireplace("bag_vbo.", "", $cdk_id);
				//first add stuff to layer
				$nodes = array();
				$node = $citySDKProxy->createNode($geom[0], $geom[1], $cdk_id, $results[0]->layers->bag_vbo->data);
				
				array_push($nodes, $node);
				$request =$citySDKProxy->createRequest($nodes);
				$citySDKProxy->uploadNodes("2cm.bag.vbo", $request);	
				
				$cdk_id = "2cm.bag.vbo." .$cdk_id;
				$data["datum"] = date("Y-m-d H:i:s");
				$fixdata = str_replace(array("<","/", ">","\n","\t","\t"), " ", $data["description"]);
				$doc = array(
						'id' => $cdk_id,
						'description' => "" . $fixdata,
						'subject' => "" . $data["afzender"],
						'geo' => "$y,$x",
						'last_modified' => gmdate("Y-m-d\TH:i:s\Z"),
						'dataType' => 'CITYSDK'
				);
				
				$doc["adres_s"] = $results[0]->layers->bag_vbo->data->adres;
				$doc["plaats_s"] = $results[0]->layers->bag_vbo->data->plaats;
				$doc["postcode_s"] = $results[0]->layers->bag_vbo->data->postcode;
				$doc["gebruiksdoel_s"] = $results[0]->layers->bag_vbo->data->gebruiksdoel;
				$doc["oppervlakte_s"] = $results[0]->layers->bag_vbo->data->oppervlakte;
					
				$res = $citySDKProxy->addData($cdk_id, $layer, $data);
				$doc["vergunning_s"] = createRandomData(1,$citySDKProxy, $cdk_id, "2cm.stub_vergunningen", "vergunning", array("verbouwing gaande","afwijking awbz","gasfles","andere ingang","bijzondere situatie",""));
				$doc["woningwaarde2013_s"] = createRandomData(13,$citySDKProxy, $cdk_id,  "2cm.stub_woningwaarde", "woningwaarde2013", array("200000","400000","600000","800000","1000000","12000000"));
				$doc["voorziening_s"] = createRandomData(14,$citySDKProxy,  $cdk_id, "2cm.stub_wmo", "voorziening", array("traplift","invalidetoilet","","wmo_3","wmo_4","wno_5","","",""));
				$doc["bewoners_s"] = createRandomData(15,$citySDKProxy,  $cdk_id, "2cm.stub_gba", "bewoners", array("0","1","2","3","4","5","6","7","8","9","10"));
				$aanrijtijd_min = rand(1,10);
				$aanrijtijd_sec = rand(0,59);
				$doc["aanrijtijd_s"] = createRandomData(16,$citySDKProxy,  $cdk_id, "2cm.aanrijtijd", "aanrijtijd", array("$aanrijtijd_min.$aanrijtijd_sec"));
				$doc["risicoprofielggd_s"] = createRandomData(17,$citySDKProxy,  $cdk_id, "2cm.risicoprofiel_ggd", "voorziening", array("groen","orange","orange","rood"));
				$doc["risicoprofielpolitie_s"] = createRandomData(18,$citySDKProxy,  $cdk_id, "2cm.risicoprofiel_politie", "voorziening", array("groen","groen","groen","orange","orange","rood"));
				$solr->add_document($doc);
			}

		}

	}
}
$solr->commit();	

$citySDKProxy->destroySession();
?>
