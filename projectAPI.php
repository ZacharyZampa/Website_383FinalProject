<?php

// author - zachary zampa
// since - 2019/11/27

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
    // header("location:javascript://history.go(-1)");  // return user to prior page
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

require_once("quickModel.php");
require_once("passwd.php");

function getJson() {
	$jsonStringIn = file_get_contents('php://input');
	$json = array();
	$response = array();
	try {
		$json = json_decode($jsonStringIn, true);
		return $json;
	} catch (Exception $ex) {
		header("HTTP/1.0 500 Invalid content -> probably invalid JSON format");
		$response['status'] = "fail";
		$response['message'] = $ex->getMessage();
		print json_encode($response);
		exit;
	}
}

// verify token
$time = Date("YMDh");
$token = hash_hmac("ripemd160","$time",$secret);

$method = $_SERVER['REQUEST_METHOD'];  // get request method

$path = $_SERVER['PATH_INFO'];  // get url path 
$pathArr = explode("/", $path);  // split path into array
// var_dump($pathArr);

if ($method == "GET" && sizeof($pathArr) == 3 && $pathArr[1] == "v1" && $pathArr[2] == "quickLink") {
	// This is a get request - wanting to obtain the quickLink list
	$data = getLinks();
	// var_dump($data);

	// add status data
	$response = array();
	$response['status'] = "Ok";
	$response['msg'] = "";

	// add response data
	$response['quicklinks'] = $data;

	// encode response to JSON
	$json_response = json_encode($response);
	echo $json_response;
} elseif ($method == "GET" && sizeof($pathArr) == 3 && $pathArr[1] == "v1" && $pathArr[2] == "videoLink" && !isset($_GET['del'])) {
	// This is a get request - wanting to obtain the videoLink list
	$data = getVideos();
	// var_dump($data);

	// add status data
	$response = array();
	$response['status'] = "Ok";
	$response['msg'] = "";

	// add response data
	$response['videoLinks'] = $data;

	// encode response to JSON
	$json_response = json_encode($response);
	echo $json_response;
	// redirect back to original page
	// if(isset($_REQUEST["destination"])){
 //    	header("Location: {$_REQUEST["destination"]}");
	// }elseif(isset($_SERVER["HTTP_REFERER"])){
 //    	header("Location: {$_SERVER["HTTP_REFERER"]}");
	// }else{
 //    	header("location:index.php"); 
	// }
} elseif ($method == "DELETE" && sizeof($pathArr) == 5 && $pathArr[1] == "v1" && $pathArr[2] == "quickLink") {
	// delete a link
	$item = $pathArr[3];  // item id from url
	$tok = $pathArr[4];  // auth token
	// ensure authorised request
	if ($token == $tok) {
		$msg = deleteLink($item);
	    echo $msg;
	    $response['status'] = "OK";
	    $response["data"] = $msg;
	    $json_response = json_encode($response);
		print $json_response;
	}
} elseif ($method == "GET" && sizeof($pathArr) == 3 && $pathArr[1] == "v1" && $pathArr[2] == "videoLink") { 
	// delete a video
	$item = $_GET['del'];  // get GET query of delete
	// ensure authorised request
	if ($token == $_GET['token']) {
		$msg = deleteVideo($item);
    	echo $msg;
	}
    // redirect back to original page
    if(isset($_REQUEST["destination"])){
    	header("Location: {$_REQUEST["destination"]}");
	}elseif(isset($_SERVER["HTTP_REFERER"])){
    	header("Location: {$_SERVER["HTTP_REFERER"]}");
	}else{
    	header("location:index.php"); 
	}
} elseif ($method == "PUT" && sizeof($pathArr) == 3 && $pathArr[1] == "v1" && $pathArr[2] == "quickLink") {
	// add a quick link
	$body = getJson();
    $response = array();
    // ensure authorised request
    if ($token == $body['token']) {
	    $title = $body['title'];
	    $url = $body['url'];
	    $msg = addLink($title, $url);
	    echo $msg;
	    $response['status'] = "Ok";
	    $response["data"] = $msg;
	    $json_response = json_encode($response);
		print $json_response;
    }
} elseif ($method == "POST" && sizeof($pathArr) == 3 && $pathArr[1] == "v1" && $pathArr[2] == "videoLink") {
	// add a video link
	// ensure authorised request
    if ($token = $_POST['token']) {
    	$title = "";
		$id = "";
	    if (isset($_POST['title'])) {
	    	$title = $_POST['title'];
	    }
	    if (isset($_POST['videoID'])) {
	    	$id = $_POST['videoID'];
	    }
	    $msg = addVideo($title, $id);
	    echo $msg;
	    $response['status'] = "Ok";
	    $response["data"] = $msg;
	    $json_response = json_encode($response);
		print $json_response;
    }

	// redirect back to original page
	if(isset($_REQUEST["destination"])){
    	header("Location: {$_REQUEST["destination"]}");
	}elseif(isset($_SERVER["HTTP_REFERER"])){
    	header("Location: {$_SERVER["HTTP_REFERER"]}");
	}else{
    	header("location:index.php"); 
	}
}


?>