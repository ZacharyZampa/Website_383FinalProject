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

        $("#quickList").html("");  // clear out old data
        $("#quickList").append("<tr><th>Title</th><th>URL</th><th>Action</th></tr>");
        for (let i=0;i<len;i++) {
            // display all links
            s="<tr><td>" + pdata.quicklinks[i].title + '</td><td><a href="' 
                + pdata.quicklinks[i].url + '">' + pdata.quicklinks[i].url 
                + '</a></td><td><button class="deleteBTN" onclick="remove(' 
                + pdata.quicklinks[i].pk + ')">DELETE</button></td></tr>';
            $("#quickList").append(s)
        }
        $("#quickList").append("</table>");
    })
    .fail(function(error) {
        console.log(error); 
        $("#linkAlert").show();
    }); 
}

function submit() {
    var title = $('#title').val();
    if (!title) {
        // variable is empty
        return false;
    }
    var url = $('#url').val();
    if (!url) {
        // variable is empty
        return false;
    }

    // store the data
    var obj = {"token": token,
                "title": title, 
                "url": url
              };
    var jsonObj = JSON.stringify(obj);

    // send ajax put
    a=$.ajax({
    "url": "https://zampaze.383.csi.miamioh.edu/cse383-f19-zampaze/FinalProject/projectAPI.php/v1/quickLink",
    "method": "PUT",
    "data": jsonObj
    });
    a.done(function(data) {
        console.log(data);
        getLinks();
        $('#title').val('');
        $('#url').val('');
        $("#linkGood").fadeTo(2000, 500).slideUp(500, function(){
		    $("#linkGood").slideUp(500);
		});
    })
    .fail(function(error) {
        console.log(error); 
    }); 
}

function remove(pk) {
    a=$.ajax({
    "url": "https://zampaze.383.csi.miamioh.edu/cse383-f19-zampaze/FinalProject/projectAPI.php/v1/quickLink/" + pk + "/" + token,
    "method": "DELETE"
    });
    a.done(function(data) {
        console.log(data);
        getLinks();
    })
    .fail(function(error) {
        console.log(error); 
    }); 
}



$(document).ready(function() {
    $("#linkAlert").hide();
    $("#linkGood").hide();
    getLinks();
    // stop browser submission process for add quickLink
    $("#quickFormDiv").submit(function(evt) {
        evt.preventDefault();
        submit();
    });

});


