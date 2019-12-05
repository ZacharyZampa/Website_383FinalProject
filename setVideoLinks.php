<?php

require_once('passwd.php');      // load password file

function setToken() {
  // get a token for content modification
  global $secret;
  global $user;

  if ($user != "campbest" && $user != "zampaze") {
    print "Not authorised to use this site";
    die;
  }

  $time = Date("YMDh");
  $token = hash_hmac("ripemd160","$time",$secret);  // secret obtained in addon
  return $token;
}

// Start the session
session_start();

require_once 'config.php';   // get configuraiton
require_once 'CAS.php';      // load CAS

// Initialize phpCAS from settings in config.php
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

phpCAS::setNoCasServerValidation();  // disables ssl server verification - ok for testing and required for now

$user;

// add a timeout
$lastSeen = 0;
if (isset($_SESSION['lastSeen'])) {
  $lastSeen = $_SESSION['lastSeen'];
}

if ((time() - $lastSeen) > 15*60) { // 15 minutes * 60 seconds per minute)
    unset($_SESSION['user']);  // force reauthentication
}

$_SESSION['lastSeen'] = time();


// check if user login stored
if (isset($_SESSION['user'])) {
  // logged in so all good
  $user = $_SESSION['user'];
  $token = setToken();
} else {
  // force CAS authentication -> this is where the system does the CAS authentication.
  phpCAS::forceAuthentication();

  // If we get here then the user has been authenticated by the CAS server
  // and the user's login name can be read with phpCAS::getUser().

  $user = phpCAS::getUser();

  $_SESSION['user'] = $user;

  $token = setToken();

  // logout if desired
  if (isset($_REQUEST['logout'])) {
      phpCAS::logout();
  }
}


// connect to the database
$mysqli = new mysqli('3.95.149.246',$user,$pwd,$database);
if ($mysqli->connect_error != "") {
  print "Error connecting to db";
  die;
}


function getAllVideos() {
  global $mysqli;
  $result = "";
  // get data from database
  $sql = "select * from videoLink";
  $stmt = $mysqli->prepare($sql);
  if (!$stmt) {
    return null;
  }

  $stmt->bind_result($hpk, $htitle, $hid);
  $stmt->execute();

  // loop through processing returned data
  while ($stmt->fetch()) {
    $result .= "<tr><td>$htitle" . '</td><td><a href="https://www.youtube.com/watch?v=' 
                . $hid . '">' . $hid
                . '</a></td><td><form method="GET" action="https://zampaze.383.csi.miamioh.edu/cse383-f19-zampaze/FinalProject/projectAPI.php/v1/videoLink">'
                . '<input type="hidden" name="del" value="' .$hpk . '">' 
                . '<input type="submit" name="submit" value="Delete">'
                . '<input class="AuthToken" type="hidden" name="token" value="$token"></form></td></tr>';
  }
  return $result;
}






// get the authorisation token made during authorisation
function getToken() {
  global $token;
  return $token;
}






?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Set Video Links</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">  <!-- Easier for mobile -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
  <script>
    var token = '<?php print getToken();?>';
  </script>
  <style>
    table, th, td {
      border: 1px solid black;
      border-collapse: collapse;
    }
    th, td {
      padding: 15px;
    }
</style>
</head>
<body>
<div class="container-fluid mycenter">
	<h1>Video Link Configuration Page</h1>
  <div id="videoAlert" class="alert alert-warning">
    <strong>No Internet Connection!</strong> This page will not work without a connection to the server.
  </div>
	<h2>Videos In List</h2>
	<table id="VideoList">
      <?php echo getAllVideos() ?>
    </table>
    <h2>Video Link Submission Form</h2>
    <div id='videoFormDiv'>
    <form class= "needs-validation" method="POST" action="https://zampaze.383.csi.miamioh.edu/cse383-f19-zampaze/FinalProject/projectAPI.php/v1/videoLink">
      <div class="form-group">
        <label for="title">Title</label> <input type='text' class="form-control"
        name='title' placeholder="My fav title"id='title'> 
        <small id="titleHelp" class="form-text text-muted">What to call the video</small>
      </div>
      <div class="form-group">
        <label for="videoID">ID</label> <input type="text" class="form-control"
        name='videoID'id="videoID"> 
        <small class="form-text text-muted">The ID for the video</small>
      </div><input class="btn btn-success" type='submit' name="submit" value="Add">
      <button type="button" class="btn btn-primary" onclick="window.location.href = 'index.php';">Back to Homepage</button>
      <input class="AuthToken" type="hidden" name="token" value="">
    </form>
  </div>
</div>

<script>
$(document).ready(function() {
  $('.AuthToken').val(token);
  $("#videoAlert").hide();
});

</script>
</body>
</html>