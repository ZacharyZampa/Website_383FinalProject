<?php

// author - zachary zampa
// since - 2019/11/27


require_once("passwd.php");

$mysqli = new mysqli('3.95.149.246',$user,$pwd,$database);
if ($mysqli->connect_error != "") {
	print "Error connecting to db";
	die;
}


//returns array of quickLinks
function getLinks() {
	global $mysqli;
	$sql = "select * from quickLink";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt)
	{
		return null;
	}

	$stmt->bind_result($hpk, $htitle, $hurl);
	$stmt->execute();
	$result = array();
	while ($stmt->fetch()) {
		$link = array('pk'=>$hpk, 'title'=>$htitle, 'url'=>$hurl);
		array_push($result,$link);
	}
	return $result;
}

//returns array of quickVideos
function getVideos() {
	global $mysqli;
	$sql = "select * from videoLink";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt)
	{
		return null;
	}

	$stmt->bind_result($hpk, $htitle, $hid);
	$stmt->execute();
	$result = array();
	while ($stmt->fetch()) {
		$video = array('pk'=>$hpk, 'title'=>$htitle, 'id'=>$hid);
		array_push($result,$video);
	}
	return $result;
}

//returns string msg
function addLink($title,$url) {
	global $mysqli;
	if ($title == "" || $url == "")
		return "Error - invalid inputs";
	$sql = "insert into quickLink(title,url) values (?,?)";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt)
	{
		print "Error on prepare in addLink";
		return "Failed on prepare";
	}

	$htitle =htmlspecialchars($title);
	$hurl =htmlspecialchars($url);
	$stmt->bind_param("ss",$htitle,$hurl);
	$stmt->execute();
	return null;
}

//returns string msg
function addVideo($title,$id) {
	global $mysqli;
	if ($title == "" || $id == "")
		return "Error - invalid inputs\nHave:$title and $id";
	$sql = "insert into videoLink(title,id) values (?,?)";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt)
	{
		print "Error on prepare in addVideo";
		return "Failed on prepare";
	}

	$htitle =htmlspecialchars($title);
	$hid =htmlspecialchars($id);
	$stmt->bind_param("ss",$htitle,$hid);
	$stmt->execute();
	return null;
}

function deleteLink($pk) {
	global $mysqli;
	if ($pk == "" )
		return "Error - invalid inputs";
	$sql = "delete from quickLink where pk=?";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt)
	{
		print "Error on prepare in deleteLink";
		return "Failed on prepare";
	}

	$stmt->bind_param("i",$pk);
	$stmt->execute();
	return("ok");
}

function deleteVideo($pk) {
	global $mysqli;
	if ($pk == "" )
		return "Error - invalid inputs";
	$sql = "delete from videoLink where pk=?";
	$stmt = $mysqli->prepare($sql);
	if (!$stmt)
	{
		print "Error on prepare in deleteVideo";
		return "Failed on prepare";
	}

	$stmt->bind_param("i",$pk);
	$stmt->execute();
	return("ok");
}



?>