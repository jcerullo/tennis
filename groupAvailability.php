<?php
  session_start();
  $leagueID = $_SESSION["leagueID"];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Tennis</title>
  <meta charset="utf-8"/>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
     integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
  <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,700' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" type="text/css" href="main.css">
  <script type="text/javascript" src = "jquery-3.3.1.js"> </script>
  <script> type = "text/javascript">
  
  $(init);
  
  function init() {
	  // load up active members from DB
	  $("#activeMembersList").load("activeMembersList.php");
  }  //end init
  
  </script>  
</head>
<body>
<?php

    $leagueID = $_SESSION["leagueID"] ;                                                                      // retrieve SQL record
	try {                                                
        $con= new PDO('mysql:host=localhost;dbname=tennis', "root", "jjc003");
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
        $result = $con->query("SELECT * FROM league WHERE leagueID = '$leagueID'");
		$result->setFetchMode(PDO::FETCH_ASSOC);

		foreach ($result as $row) {
		  foreach ($row as $name =>$value ) {                              // populate values
			if ($name == 'leagueName')  $leagueName = $value;
			if ($name == 'moderatedBy')  $moderatedBy = $value;
		  }		 		  
	    }           		
    }		 //end of try
 	
    catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
					 
    if (filter_has_var(INPUT_POST, "schedDate")) {
        $schedDate = filter_input(INPUT_POST, "schedDate");
        if ($schedDate == "") $schedDate = "0000-00-00";		
	    $_SESSION["schedDate"] = $schedDate;
	} 
	else {
		$schedDate = $_SESSION["schedDate"];
	}

	$isAdmin = $_SESSION["isAdmin"];
	
	$player = "";
		
//                                                                	 load up active members into $activeMbrs string
    $activeMbrs = "";
	$mbrArray = array();
		
	try {
        $con= new PDO('mysql:host=localhost;dbname=tennis', "root", "jjc003"); 
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
        $query = "SELECT   mbrID	
		          FROM     member
				  WHERE    status = 'A' AND leagueID = '$leagueID' ";
        
        //first pass just gets the column names

        $result = $con->query($query);
        //return only the first row (we only need field names)
        $row = $result->fetch(PDO::FETCH_ASSOC);

        foreach ($row as $field => $value){
        } // end foreach
 
        //second pass gets the data
        $data = $con->query($query);
        $data->setFetchMode(PDO::FETCH_ASSOC);

        foreach($data as $row){
			foreach ($row as $name=>$value){ 
			  if ($name == 'mbrID') $activeMbrs .= $value . ",";              
			} // end field loop
        } // end record loop


    } catch(PDOException $e) {
          echo 'ERROR: ' . $e->getMessage();
    } // end try
	

    if (filter_has_var(INPUT_POST, "selMembers")) {
        $values = $_POST['selMembers'];
		$i = 0;
		foreach ($values as $selMembers ) {
			$mbrArray[$i] = $selMembers;
			$i = $i + 1;
		}
	}
	
    if (filter_has_var(INPUT_POST, "makeAvailable"))
        $makeAvailable = filter_input(INPUT_POST, "makeAvailable");		
		
	if ($mbrArray[0] == "*All") $mbrArray = explode(",",$activeMbrs);
		
        try {			
        
        $con= new PDO('mysql:host=localhost;dbname=tennis', "root", "jjc003");
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		    if ($mbrArray[0] == "*All") {
				$result = $con->prepare("DELETE FROM available
					WHERE dates = '$schedDate' AND leagueID = '$leagueID' ");
				$result->execute();
			}
            else {
				for ($i=0; $i < sizeof($mbrArray); $i++) {
				  $result = $con->prepare("DELETE FROM available
					WHERE mbrID = '$mbrArray[$i]' AND dates = '$schedDate' AND leagueID = '$leagueID' ");
				  $result->execute();
				}
			}					
			
		 
        }		 //end of try
 	
        catch(PDOException $e) {
          echo 'ERROR: ' . $e->getMessage();
	    }

	for ($i=0; $i < sizeof($mbrArray); $i++) { 
		$player = $mbrArray[$i];

		if ($player != "" && $schedDate != "0000-00-00" && $makeAvailable == "Y"){		
			try {
        
			$con= new PDO('mysql:host=localhost;dbname=tennis', "root", "jjc003");
			$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			 
	        $result = $con->prepare("INSERT INTO available 
			            (rcdNbr,  
						mbrID,
						dates,
						leagueID
						)			
			     VALUES
			            (NULL,
						'$player',
						'$schedDate',
						'$leagueID'
                        )            ");

			$result->execute();
					 
			}		 //end of try
 	
			catch(PDOException $e) {
				echo 'ERROR: ' . $e->getMessage();
			}
		}  //end if
	}      //end for
 	
?>
	
<header class="container">
<div id="wrapper">

<nav id="nav">
	<ul id="navigation">
		<li><a href="indexProcess.php">Login</a></li>
		<li><a href="#">Member Info &raquo;</a>
			<ul>
				<li><a href="members.php">MemberRoster</a></li>
				<li><a href="mbrProfile.php">MemberProfile</a></li>
				<li><a href="availability.php">MemberAvailability</a></li>
				<li><a href="groupAvailability.html">GroupAvailability</a></li>
				<li><a href="mbrRanking.php">Tennis Rankings</a></li>
				<li><a href="sumResults.html">MemberStats</a></li>				
			</ul>
		</li>
		<li><a href="#">Court Info &raquo;</a>
			<ul>
				<li><a href="facilities.php">Facilities</a></li>
				<li><a href="facility.php">CourtAvailability</a></li>
				<li><a href="assignments.html">DailyCourtAssignments</a></li>
				<li><a href="schedule.php">WeeklyCourtAssignments</a></li>				
			</ul>				
		</li>
		<li><a href="#">Play Results &raquo;</a>
			<ul>
				<li><a href="setResults.html">ResultsEntry</a></li>
				<li><a href="sumResults.html">ResultsSummary</a></li>				
			</ul>				
		</li>
		<li><a href="changeMonth.php">Change Month</a></li>
		<li><a href="sendEmail.php">Format Email</a></li>
		<li><a href="help.php">Help</a></li><br>
	</ul>
</nav>

</div><!--end wrapper-->
</header>

  <div class = "member"> 
  <style type = "text/css">
  
          fieldset {
            width: 600px;
            background-color: #eeeeee;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 5px 5px 5px gray;
          }
		  
          label {
            float: left;
            clear: left;
            width: 250px;
            text-align: right;
            padding-right: 1em;
          }
                   
          input {
            float: left;
          }
		  
		  select {
			  float:left;
		  }
          
          :required {
            border: 1px solid red;
          }
          
          :invalid {
            color: white;
            background-color: red;
          }
          
          button {
		    text-align: center;
            display: block;
            margin-left: auto;
            margin-right: auto;
            clear: both;
          }
		  
		  p {
			margin-left: 100px;
			text-align: center;
		  }
		  
		  h1 {
			margin-left: 100px;
            margin-right:100px;
			text-align: center;
		  }
		  
		  h2 {
			margin-left: 100px;
            margin-right:100px;
			text-align: center;
		  }
		  
		  #submit {
			margin-left: 330px;
		    background-color: lightyellow;
		  }
  </style>
  
  </div>
  
<?php

$timestamp = strtotime($schedDate);                // get day name
$dayName = date("l", $timestamp);
  
print ("	  <h1><strong> Set Member Availability </strong></h1> "); 
print ("	  <h2><strong> for $dayName, $schedDate </strong></h2> ");
if ($isAdmin) {
print <<<HERE
    <form action = "" method= "post">
      <fieldset> 
		<label>Active Member(s)</label>
        <select id="activeMembersList" name="selMembers[]" multiple size=9>
		</select>
		
        <label>Make Available</label>
        <select id="makeAvailable" name=makeAvailable >
		   <option value = "Y"> Yes </option> 
		   <option value = "N"> No </option> 
		</select>
		
        <input type="submit" id = "submit" value = "submit"/> <br>
      </fieldset>
    </form>
HERE;
}  //end if
?>
  
	
<section>
    <figure class="col-sm-6">
	<style type = "text/css">
		  
	#availableList {
		margin-top:  40px;
		margin-left: 200px;
		
        h3 {
			margin-left: 150px;
            margin-right:150px;
			text-align: left;
		  }
	}
    </style>
	
  <div id = "availableList"> 
  
<?php

	print "<h3><strong>  Players Available on $dayName</strong></h3>";
	
	                                  //          load up available members 
    try {
        $con= new PDO('mysql:host=localhost;dbname=tennis', "root", "jjc003");
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT mbrID as 'Members' 
		                 FROM available WHERE dates = '$schedDate' AND leagueID = '$leagueID'
                          ";
        
        //first pass just gets the column names
        print "<table> \n";

        $result = $con->query($query);
        //return only the first row (we only need field names)
        $row = $result->fetch(PDO::FETCH_ASSOC);

        print "  <tr> \n";
        foreach ($row as $field => $value){
          print "    <th>$field</th> \n";
        } // end foreach
        print "  </tr> \n";
 
        //second pass gets the data
        $data = $con->query($query);
        $data->setFetchMode(PDO::FETCH_ASSOC);

        foreach($data as $row){
          print "  <tr> \n";
          foreach ($row as $name=>$value) {			  
            print "    <td>$value</td> \n";              
          } // end field loop
          print "  </tr> \n";
        } // end record loop

        print "</table> \n";
	}   //end of try
 	
	catch(PDOException $e) {
		echo 'ERROR: ' . $e->getMessage();
	}

?>
   </div>   
   </figure> 
	
</section>   
</body>
</html>
