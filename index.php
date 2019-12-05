<?php

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

$tmpLoc = $_SESSION['weatherZip'];
if ((time() - $lastSeen) > 1*60) { // 15 minutes * 60 seconds per minute)
    unset($_SESSION['user']);  // force reauthentication
}

$_SESSION['lastSeen'] = time();
if ($tmpLoc) {
	// saved variable is already there
	$_SESSION['weatherZip'] = $tmpLoc;
}


// check if user login stored
if (isset($_SESSION['user'])) {
  // logged in so all good
  $user = $_SESSION['user'];
} else {
  // force CAS authentication -> this is where the system does the CAS authentication.
  phpCAS::forceAuthentication();

  // If we get here then the user has been authenticated by the CAS server
  // and the user's login name can be read with phpCAS::getUser().

  $user = phpCAS::getUser();

  $_SESSION['user'] = $user;

  // logout if desired
  if (isset($_REQUEST['logout'])) {
      phpCAS::logout();
  }
}


$weatherZip = 45056;  // default zip code
if (isset($_SESSION['weatherZip'])) {
  $weatherZip = $_SESSION['weatherZip'];
}

// check if updated zip request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // collect value of input field
    $weatherZip = htmlspecialchars($_REQUEST['zip']);
    if (empty($weatherZip)) {
        // user did not really supply a zipcode
        $weatherZip = 45056;
    } else {
        $_SESSION['weatherZip'] = $weatherZip;   // store to local storage not cookie
    }
}



// check if user is authorised - if so open up link
function isAuthorised() {
  global $user;
  // check if one of two users: hardcoded as only two, not a whole list
  if ($user == "campbest" || $user == "zampaze") {
    return "style='display:inline'";
  }
  return "style='display:none'";
}



?>



<!DOCTYPE html>
<html lang="en">
<head>
  <title>Zachary Zampa Final</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">  <!-- Easier for mobile -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
  <script type="text/javascript">var wZip = "<?php echo htmlspecialchars_decode($weatherZip) ?>";</script>
  <script src="indexScript.js"></script>
  <style>
	body {
	    position: relative; 
	}

	#section1 {padding-top:50px;height:500px;}
	#section2 {padding-top:50px;height:500px;}
	#section3 {padding-top:50px;height:500px;}

	nav.sticky {
		position: -webkit-sticky;
		position: sticky;
		top: 0;
    z-index: 9999;
	}

	.modal {
	  display: none;
	  position: fixed; 
	  z-index: 1; 
	  overflow: auto; 
	  background-color: rgb(0,0,0); 
	  background-color: rgba(0,0,0,0.4); 
	}

	.modal-content {
	  background-color: white;
	  margin: 15% auto;
	  padding: 20px;
	  border: 1px solid grey;
	  width: 80%;
	}

	.close {
	  color: black;
	  float: right;
	  font-size: 30px;
	  font-weight: 900;
	}

	</style>
</head>  <!-- End HTML header -->
<body data-spy="scroll" data-target=".navbar" data-offset="100">

<div class="page-header" style="max-height: 50px; text-align: center">
  <h1><a href="https://miamioh.edu/cec/index.html">
        <img src="cec.jpg" class="img-rounded" alt="Miami University CEC Logo" width="100" style="float: left;">
      </a>
      Zachary Zampa's Final 383 Project
      <a id="logos" href="#">
        <img src="logo.png" class="img-rounded" alt="Personal Logo" width="100" style="float: right;">
      </a>
  </h1>
</div>  <!-- End Page Header -->

<!-- Begin Navbar -->
<nav class="navbar navbar-inverse sticky">
  <div class="container-fluid">
      <div class="navbar-header" id="myNavbar">
        <ul class="nav navbar-nav">
          <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Menu<span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li ><a href="index.php">Home</a></li>
	          <li class="normal" id="SetWeather"><a href="#">Set Weather</a></li>
	          <li <?php echo isAuthorised() ?> id="SetVideo">
              <a href="setVideoLinks.php">Set Video Links</a>
            </li>  <!-- class="disabled" if not authorised user -->
	          <li <?php echo isAuthorised() ?> id="SetLinks">
              <a href="setQuickLinks.php">Set Quick Links</a>
            </li>  <!-- class="disabled" if not authorised user -->
            </ul>
          </li>
          <li><a href="#section1">Weather</a></li>
          <li><a href="#section2">Quick Links</a></li>
          <li><a href="#section3">Video Links</a></li>
          <li><a id="welcUser">Welcome <?php echo $user ?>!</a></li>   <!-- Welcomes the current logged in user -->
        </ul>
    </div> <!-- End location nav-bar div -->
  </div>
</nav>  <!-- End nav-bar -->

<div id="mainContainerDiv" style="padding-bottom: 100px;">
<div id="section1" class="container">
 <h1>Weather</h1>
  <div id="weatherAlert" class="alert alert-warning">
  	<strong>No Internet Connection!</strong> Connection to the weather service is not working.
  </div>
  <p id="wLocation">You are monitoring the weather in [location]</p>
  <p id="wCondTemp">It is currently [currentConditions] at [currentTemperature]&#8457;</p>
  <p id="wForcast">Expect: [forecast]</p>
  <p id="wZipcode" hidden></p>
</div>  <!-- End section1 container -->

<div id="section2" class="container">
  <h1>Quick Links</h1>
  <div id="linkAlert" class="alert alert-warning">
  	<strong>No Internet Connection!</strong> Loading the links will not work.
  </div>  
  <div id="section2List" class="list-group">
  </div>
</div>  <!-- End section2 container -->

<div id="section3" class="container">
  <div class="panel-group" id="section3List">
    <h1>Video Links</h1>
    <div id="videoAlert" class="alert alert-warning">
      <strong>No Internet Connection!</strong> Loading the links will not work.
    </div>
  </div> 
</div> <!-- End section3 container -->


<div id="weatherPopup" class="modal">
	<div class="modal-content" style="text-align: center;">
		<h2>Set Weather Location<span id="weatherClose" class="close">&times;</span></h2>
		<div id="weatherSetDiv" class="row justify-content-center align-self-center">  <!-- Center everything -->
          <form class = form-horizontal action = "getWeather()" target="_self" method="get">
            <div class="form-group">
              <label class="control-label col-sm-2" for="zip">Zipcode:</label>
              <div class="col-sm-10">
              <input type="text" class="form-control" name="zip" id="zip" placeholder="55555" pattern="[0-9]{5}">
              </div> <!-- end div for input -->
              <small class="form-text text-muted">This is the zipcode for the city you desire</small>
              <br>
            </div> <!-- End div for username -->
            <div class="form-group">
              <br><br>
              <input class="btn btn-success" type="submit" value="submit">
            </div> <!-- End div for submit -->
          </form> 
        </div> 
	</div>
</div>  <!-- End Weather Set pop-up -->


<div id="mePopup" class="modal">
	<div class="modal-content" style="text-align: center; width: 60%; max-width: 350px">
		<img src="me.jpg" alt="A photo of me" style="width:100%; max-width: 200px">
	</div>
</div>  <!-- End Me pop-up -->

</div>


<div id="footer" class="jumbotron text-center" style="margin-bottom:0">
  <p>Zachary Zampa </p>
  <p>CSE 383</p>
  <p>Copyright (C) 2019 zampaze@miamioh.edu</p>
</div>  <!-- End footer -->
</body>
</html>




