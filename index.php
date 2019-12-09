<?php 
  include('includes/init.inc.php'); // include the DOCTYPE and opening tags
  //include('includes/functions.inc.php'); // functions
?>
<title>Find Me Now</title>   

<?php 
  include('includes/head.inc.php'); 
  // include global css, javascript, end the head and open the body
?>

<div id="header">
  <img id="logo" src="resources/logo.png">
  <h3 id="titleHeader">Find Me Now</h3>
</div>
 
<!--php include('includes/menubody.inc.php'); -->

<?php
  // We'll need a database connection both for retrieving records and for 
  // inserting them.  Let's get it up front and use it for both processes
  // to avoid opening the connection twice.  If we make a good connection, 
  // we'll change the $dbOk flag.
  $dbOk = false;
  
  /* Create a new database connection object, passing in the host, username,
     password, and database to use. The "@" suppresses errors. */
  @ $db = new mysqli('localhost', 'root', 'root', 'iit');
  
  if ($db->connect_error) {
    echo '<div class="messages">Could not connect to the database. Error: ';
    echo $db->connect_errno . ' - ' . $db->connect_error . '</div>';
  } else {
    $dbOk = true; 
  }

  // Now let's process our form:
  // Have we posted?
  $havePost = isset($_POST["submit"]);
  
  // Let's do some basic validation
  $errors = '';
  if ($havePost) {
    
    // Get the output and clean it for output on-screen.
    // First, let's get the output one param at a time.
    // Could also output escape with htmlentities()
    $item = htmlspecialchars(trim($_POST["item"]));  
    $description = htmlspecialchars(trim($_POST["description"]));
    $latitude = htmlspecialchars(trim($_POST["latitude"]));
    $longitude = htmlspecialchars(trim($_POST["longitude"])); 
    
    $focusId = ''; // trap the first field that needs updating, better would be to save errors in an array
    
    if ($item == '') {
      $errors .= '<li>Item may not be blank</li>';
      if ($focusId == '') $focusId = '#item';
    }
    if ($description == '') {
      $errors .= '<li>Description may not be blank</li>';
      if ($focusId == '') $focusId = '#description';
    }
  
    if ($errors != '') {
      echo '<div class="messages"><h4>Please correct the following errors:</h4><ul>';
      echo $errors;
      echo '</ul></div>';
      echo '<script type="text/javascript">';
      echo '  $(document).ready(function() {';
      echo '    $("' . $focusId . '").focus();';
      echo '  });';
      echo '</script>';
    } else {
      if ($dbOk) {
        // Let's trim the input for inserting into mysql
        // Note that aside from trimming, we'll do no further escaping because we
        // use prepared statements to put these values in the database.
        $itemForDb = trim($_POST["item"]);  
        $descriptionForDb = trim($_POST["description"]);
        $latitudeForDb = trim($_POST["latitude"]);
        $longitudeForDb = trim($_POST["longitude"]);
        
        // Setup a prepared statement. Alternately, we could write an insert statement - but 
        // *only* if we escape our data using addslashes() or (better) mysqli_real_escape_string().
        $insQuery = "INSERT INTO `itemlist`(`Item`, `Description`, `Latitude`, `Longitude`) VALUES (?,?,?,?)";
        $statement = $db->prepare($insQuery);
        // bind our variables to the question marks
        $statement->bind_param("ssss",$itemForDb,$descriptionForDb,$latitudeForDb,$longitudeForDb);
        // make it so:
        $statement->execute();
        
        // give the user some feedback
        // echo '<div class="messages"><h4>Success: ' . $statement->affected_rows . ' actor added to database.</h4>';
        // echo 'Item: ' . $itemForDb . '; Description: ' . $descriptionForDb . '</div>';
        
        // close the prepared statement obj 
        $statement->close();

        $_POST["submit"] = NULL;

      }
    } 
  }
?>

<div class="tab">
  <button class="tablinks" onclick="openTab(event, 'Items')" id="defaultOpen">Items</button>
  <button class="tablinks" onclick="openTab(event, 'SLI')">Submit Lost Item</button>
</div>

<div id="Items" class="tabcontent">
  <table id="itemTable">
  <?php
    if ($dbOk) {

      $query = 'select * from itemlist order by DatePosted desc';
      $result = $db->query($query);
      $numRecords = $result->num_rows;
      
      echo '<tr><th style="width:20%">Item:</th><th style="width:40%">Description:</th><th style="width:15%">Location:</th><th style="width:10%">Date Posted:</th><th style="width:10%">Status</th></tr>';
      for ($i=0; $i < $numRecords; $i++) {
        $record = $result->fetch_assoc();
        if ($i % 2 == 0) {
          echo "\n".'<tr class="clicktr" id="item-' . $record['PostID'] . '"><td>';
        } else {
          echo "\n".'<tr class="odd clicktr" id="item-' . $record['PostID'] . '"><td>';
        }
        echo htmlspecialchars($record['Item']);
        echo '</td><td>';
        echo htmlspecialchars($record['Description']);
        echo '</td><td>';
        if (!(htmlspecialchars($record['Latitude']) == '' || htmlspecialchars($record['Longitude']) == '')) {
          echo '<div class="mapouter"><div class="gmap_canvas"><iframe id="gmap_canvas" src="https://maps.google.com/maps?q=';
          echo htmlspecialchars($record['Latitude']);
          echo ',';
          echo htmlspecialchars($record['Longitude']);
          echo '&amp;t=&amp;z=18&amp;ie=UTF8&amp;iwloc=&amp;output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe></div><style>.mapouter{position:relative;text-align:right;}.gmap_canvas {overflow:hidden;background:none!important;}</style></div>';
        }
        echo '</td><td>';
        echo htmlspecialchars($record['DatePosted']);
        echo '</td><td class="';
        if (htmlspecialchars($record['Status']) == "Missing")
          echo 'cactus">';
        else
          echo 'rainbow">';
        echo htmlspecialchars($record['Status']);
        echo '</td></tr>';
        // echo '<img src="resources/delete.png" class="deleteActor" width="16" height="16" alt="delete actor"/>';
        // echo '</td></tr>';
        // Uncomment the following three lines to see the underlying 
        // associative array for each record.
        /*echo '<tr><td colspan="3" style="white-space: pre;">';
        print_r($record);
        echo '</td></tr>';*/
      }
      
      $result->free();
      
      // Finally, let's close the database
      $db->close();
    }
    
  ?>
  </table>
</div>

<div id="SLI" class="tabcontent">
  <form id="addForm" name="addForm" action="index.php" method="post" onsubmit="return validate(this);">
    <fieldset> 
      <div class="formData">
                      
        <label class="field" for="item">Item:</label>
        <div class="value"><input type="text" size="60" maxlength="255" value="<?php if($havePost && $errors != '') { echo $item; } ?>" name="item" id="item"/></div>
        
        <label class="field" for="description">Description:</label>
        <div class="value"><input type="text" size="60" maxlength="3072" value="<?php if($havePost && $errors != '') { echo $description; } ?>" name="description" id="description"/></div>
        
        <label class="field" for="latitude">Latitude:</label>
        <div class="value"><input type="text" size="60" maxlength="255" value="<?php if($havePost && $errors != '') { echo $latitude; } ?>" name="latitude" id="latitude"/></div>

        <label class="field" for="longitude">Longitude:</label>
        <div class="value"><input type="text" size="60" maxlength="255" value="<?php if($havePost && $errors != '') { echo $longitude; } ?>" name="longitude" id="longitude"/></div>
        
        
        <script>
        function getLocation() {
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
          } else {
            x.innerHTML = "Geolocation is not supported by this browser.";
          }
        }

        function showPosition(position) {
          document.getElementById('latitude').value = position.coords.latitude;
          document.getElementById('longitude').value =  position.coords.longitude;
        }
        </script>
        <input type="submit" value="Submit" id="submit" name="submit"/>
      </div>
    </fieldset>
  </form>
  <button id="getLoc" onclick="getLocation()">Get Your Location!</button>
</div>

<!-- <div id="Tokyo" class="tabcontent">
  <h3>Tokyo</h3>
  <p>Tokyo is the capital of Japan.</p>
</div> -->

<script>
function openTab(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" currentTab", "");
  }
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " currentTab";
}

document.getElementById("defaultOpen").click();
</script>


<?php include('includes/foot.inc.php'); 
  // footer info and closing tags
?>
