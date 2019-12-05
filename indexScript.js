// author - zachary zampa
// since - 2019/11/25


function getWeather() {
	// check if saved to local storage
	if (typeof(Storage) !== "undefined") {
 		// local storage supported
 		// check if sq already filled
		if (!sq) {
			// collect entered user search query ("sq")
			var sq = $('#zip').val();
			if (!sq) {
				// variable is empty so local storage if available
				if (!localStorage.getItem("weatherZipLocation")) {
					// local storage does not exist and variable is empty: add default
					localStorage.setItem("weatherZipLocation", 45056); 
				}
				sq = localStorage.getItem("weatherZipLocation");
			}
		}

 		// save to localstorage
 		localStorage.setItem("weatherZipLocation", sq);
	} else {
	  	// Web Storage not supported so operate off session if needed
	  	// check if sq already filled
		if (!sq) {
			// collect entered user search query ("sq")
			var sq = $('#zip').val();
			if (!sq) {
				// variable is empty so add session variable
				sq = wZip;
			}
		}
	}
	console.log(sq);

	// send search
	a=$.ajax({
	    url: "https://ceclnx01.cec.miamioh.edu/~campbest/weather.php/v1/54321/" + sq,
	    method: "GET"
	});
	a.done(function(pdata) {
		// gather information
	    var location = pdata.location;
	    var currentTemperature = pdata.currentTemperature;
	    var currentConditions = pdata.currentConditions;
	    var forecast = pdata.forecast;

	    // add to weather html
	    $('#wLocation').text("You are monitoring the weather in " + location);
	    $('#wCondTemp').text("Current Conditions: " + currentConditions 
	    	+ " with a temperature of " + currentTemperature + "℉");
	    $('#wForcast').text("Expect: " + forecast);
	    $('#wZipcode').text(sq);
	})
	.fail(function(error) {
	    console.log(error); 
	    $("#weatherAlert").show();
	});

	$.ajax({
        method: "POST",
        url: "index.php",
        data: {zip:sq}
    });
}

function getLinks() {
   a=$.ajax({
    url: "https://zampaze.383.csi.miamioh.edu/cse383-f19-zampaze/FinalProject/projectAPI.php/v1/quickLink",
    method: "GET",
    headers: {"Content-Type":"application/json"}
    });
    a.done(function(data) {
        console.log(data);
        pdata = JSON.parse(data);
        let len = pdata.quicklinks.length;

        $("#section2List").html("");  // clear out old data
        for (let i=0;i<len;i++) {
            // display all links
            s='<a href="' + pdata.quicklinks[i].url + '" class="list-group-item">' + pdata.quicklinks[i].title + '</a>';
            $("#section2List").append(s)
        }
    })
    .fail(function(error) {
        console.log(error); 
        $("#linkAlert").show();
    }); 
}

function getVideos() {
   a=$.ajax({
    url: "https://zampaze.383.csi.miamioh.edu/cse383-f19-zampaze/FinalProject/projectAPI.php/v1/videoLink",
    method: "GET",
    headers: {"Content-Type":"application/json"}
    });
    a.done(function(data) {
        console.log(data);
        pdata = JSON.parse(data);
        let len = pdata.videoLinks.length;

        for (let i=0;i<len;i++) {
            // display all links
			s = '<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title">'
				+ '<a data-toggle="collapse" data-parent="#section3List" href="#collapse' + (i+1) + '">' + pdata.videoLinks[i].title 
				+ '</a></h4></div><div id="collapse' + (i+1) + '" class="panel-collapse collapse">'
				+ '<div class="panel-body"><div class="embed-responsive embed-responsive-16by9">'
				+ '"<iframe class="embed-responsive-item" src="https://www.youtube.com/embed/' + pdata.videoLinks[i].id 
				+ '" allowfullscreen></iframe></div></div></div></div>'
            $("#section3List").append(s)
        }

        let pxSize = 100 * (len + 1);
        let actual = pxSize.toString() + "px";
        $('#mainContainerDiv').css("padding-bottom", actual);
    })
    .fail(function(error) {
        console.log(error); 
        $("#videoAlert").show();
    }); 
}


$(document).ready(function() {
	$("#weatherAlert").hide();
	$("#linkAlert").hide();
	$("#videoAlert").hide();
	getWeather();
	getLinks();
	getVideos();
	var modalA = document.getElementById('weatherPopup');

	// Get the modal
	var modal = document.getElementById('mePopup');

	// open the set weather div
    $("#SetWeather").click(function() {
      	$("#weatherPopup").show();
    });
    // close the set weather div
    $('#weatherClose').click(function() {
      	$("#weatherPopup").hide();
    });

    // When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
	  modal = document.getElementById("weatherPopup");
	  if (event.target == modal) {
	    modal.style.display = "none";
	  }
	}

	// display mePopup when logo is clicked
	$("#logos").click(function() {
      	$("#mePopup").show();
    });

    // When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
	  if (event.target == modal) {
        modal.style.display = "none";
      } 
	  if(event.target == modalA) {
	    modalA.style.display = "none";      
	  }
	}

    // stop browser submission process for weather div
	$("#weatherSetDiv").submit(function(evt) {
		evt.preventDefault();
		$("#weatherPopup").hide();
		getWeather();
	});
});