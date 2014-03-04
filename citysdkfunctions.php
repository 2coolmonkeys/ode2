<?php

require_once"phpsettings.php";

//http://api.citysdk.waag.org/layers/reload__

class CitySDKProxy{

	private $xauth_session_key = "";
	public $message = "";
	public $status = "fail";
	public $results = nil;
	public $lasturl;
	
	function __construct() {
		date_default_timezone_set("Europe/Amsterdam");
	}
	function startSession(){
		global $endpoint,$username,$password;

		$query = http_build_query(array("e"=>$username,"p"=>$password ));
		$sesson_url = $endpoint . "/get_session?$query";
		$ch = curl_init($sesson_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($ch);
		var_dump($response);
		if ($this->processResponse($response)){
			$this->xauth_session_key = $this->results[0];
			return (isSet($this->xauth_session_key));
				
		}
		return false;
	}

	function destroySession(){
		global $xauth_session_key, $endpoint;

		$release_session_url = $endpoint . "/release_session";
		$ch = curl_init($release_session_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		$headers = array(
				'X-Auth: ' . $this->xauth_session_key,
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		return $this->processResponse($response);
	}


	function processResponse($response){
		global $status;
		$success = false;
		$response_json = json_decode($response);
		if (isSet($response_json->status)){

			$this->message = "";
			$this->status = $response_json->status;
			if ($this->status=="success"){
				$this->results = $response_json->results;
				return true;
			} else {
				$this->message = $response_json->results[0];
			}
		}
		return false;
	}

	function uploadNodes($layer, $request){
		global $endpoint;
		
		$upload_url = $endpoint . "/nodes/" . $layer;
		
		echo $upload_url;
		$this->lasturl	= $upload_url;
		$bodydata = json_encode($request);
		var_dump($bodydata);
		
		$ch = curl_init($upload_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$bodydata);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		$headers = array(
				'Content-type: application/json',
				'X-Auth: ' . $this->xauth_session_key,
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		echo $response;
		
		return $this->processResponse($response);
	}

	
	function deleteNodes($layer){
		global $endpoint;
		$upload_url = $endpoint . "/layer/" . $layer . "?delete_node=true";
		$bodydata = json_encode($request);
		$ch = curl_init($upload_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		$headers = array(
				'Content-type: application/json',
				'X-Auth: ' . $this->xauth_session_key,
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
	
	
		echo "\n\n$response\n\n";
		return $this->processResponse($response);
	}
	
	
	function createNode($lat, $lon, $id, $data){
	//	$data->datetime = $this->getDateTime();
		$geom = array();
		$geom["type"] = "Point";
		$geom["coordinates"] = array(floatval($lat), floatval($lon));
		$node = array("id" => $id, "name" => $id, "geom" => $geom, "data"=> $data);
		return $node;
	}

	function createRequest($nodes){
		$request = array();
		$request["create"] = array("params" => array("create_type"=>"create","srid"=>4326));
		$request["nodes"] = $nodes;
		return $request;
	}
	
	/*Adding Data
PUT /<cdk_id>/<layer>	 Add data to layer <layer> of node <cdk_id>.
This call expects a JSON body in the following form:

{
  "modalities": ["rail"],        
  "data" : {
    "naam_lang": "Amsterdam Centraal",
    "code": "ASD"
  }
}   */
	
	function addData($cdk_id, $layer, $data){
		global $endpoint2;
		$upload_url = $endpoint2 . "/$cdk_id/" . $layer;
		$this->lasturl = $upload_url;
		$request = array(data=>$data);
		
		$bodydata = json_encode($request);
		$ch = curl_init($upload_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$bodydata);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		$headers = array(
				'Content-type: application/json',
				'X-Auth: ' . $this->xauth_session_key,
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		echo "addData: $response\n\n";
		return $this->processResponse($response);	
	}
	
	function addDataToNode($cdk_id, $layer, $data){
		
//	"$cdk_id/2cm.dev.meldapp";
	//	$put
		
	}

	function getDateTime(){
		$datetime = new DateTime();
		return $datetime->format('c');
	}
	
	
	function getUniqueID(){
		list($usec, $sec) = explode(" ", microtime());
		$id= ((float)$sec + (float)$usec);
		return str_ireplace(".", "", $id);
	}
}

?>