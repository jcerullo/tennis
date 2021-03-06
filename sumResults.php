<?php
  session_start();
  $leagueID = $_SESSION["leagueID"];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Tennis</title>
  <meta charset="utf-8"/>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
  <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,700' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" type="text/css" href="main.css">
  
  <link rel = "stylesheet"
        type = "text/css" href="jquery-ui-1.10.3.custom.css" />
 	
</head>
<body>

<?php
   	$providedBalls = "N";	  
    $setsWon = 0;	  
    $setsPlayed = 0;
	$monthChanged = false;
	$thisDateMonth = 0;
	$thisDateMonthName = "#";
	$priorDateMonth = 0;
	$date = "";
	$monthNames = array("January", 
	                    "February", 
						"March", 
						"April", 
						"May", 
						"June",
                        "July", 
						"August", 
						"September", 
						"October", 
						"November", 
						"December");
    $dateArray = array();
 
  function init() {
	global $mbrID;
	global $isAdmin;
	global $isMbr;
	global $status;
	global $facility;
	global $playDate;	
	
	if ($_SESSION["facility"] == "") $_SESSION["facility"] = "No Facility";

	$playDate = $_SESSION["playDate"];
	$facility = $_SESSION["facility"];
	
	if (isSet (  $_SESSION["mbrID"])) 
	{
        $mbrID =   $_SESSION["mbrID"];
	    $isMbr =   $_SESSION["isMbr"];
	    $isAdmin = $_SESSION["isAdmin"];
		$status =  $_SESSION["status"];
	}
	else 
	{
		$mbrID = "Guest";
		$isMbr = false;
		$isAdmin = false;
		$status = "";
	}
  }    //end function init
 
  function setDateMonth($fdate){

	  global $thisDateMonth;
	  global $priorDateMonth;
	  global $thisDateMonthName;
	  global $priorDateMonthName;
	  global $monthNames; 
      global $monthChanged;	  
      	  
	  $dateArray = explode("-", $fdate);
	  $thisDateMonth = (int) $dateArray[1] - 1;
      $thisDateMonthName = $monthNames[$thisDateMonth];
      $priorDateMonthName = $monthNames[$priorDateMonth];	  
  }  
  
  init();
  
  // retrieve dates
  if (filter_has_var(INPUT_POST, "fromDate") && $mbrID != "Guest") {
        $fromDate = filter_input(INPUT_POST, "fromDate");
        $toDate = filter_input(INPUT_POST, "toDate");
        $showDetail = filter_input(INPUT_POST, "showDetail");
        $showSubtotals = filter_input(INPUT_POST, "showSubtotals");		
  }
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
				<li><a href="schedule.php">WeeklyCourtAssignments</a></li>>				
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

<div id=detail>

  <style type = "text/css">
		  
	h3  {
		margin-left: 165px;
        margin-right:0px;
		text-align: left;
		}
		
	table {
		margin-left: 300px;
        margin-right:100px;
		text-align: left;
	}
	
  </style>

<?php	    

    print "<h3> <span style='margin-left:6em'><strong> Play Results for $mbrID from $fromDate to $toDate </strong></h2>";
    print "<table> \n";
    print "  <tr> \n";
	print "    <th><u>Date</u><span style='margin-left:7em'></th> \n";
	print "    <th><u>Sets Played</u><span style='margin-left:6em'> </th> \n";
	print "    <th><u>Sets Won</u><span style='margin-left:0em'></th> \n";
	print "    <th><u> </u><span style='margin-left:6em'></th> \n";
	print "    <th><u>Ball Cans</u><span style='margin-left:0em'></th> \n";
	print "    <th><u> </u><span style='margin-left:6em'> </th> \n";
	print "    <th><u>Facility</u></th> \n" ;
	print "  </tr> \n";

    try {                                                // retrieve SQL record
		$con= new PDO('mysql:host=localhost;dbname=tennis', "root", "jjc003");
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
        $result = $con->query("SELECT * FROM assignment 
		  WHERE mbrID = '$mbrID' AND date > '$fromDate' AND date < '$toDate' AND leagueID = '$leagueID'
		  ORDER BY date");
		$result->setFetchMode(PDO::FETCH_ASSOC);
		
        $monthBallCans = 0;
		$monthSetsWon = 0;
		$monthSetsPlayed = 0;
		$monthDaysPlayed = 0;
		
		$totalBallCans = 0;
		$totalSetsWon = 0;
		$totalSetsPlayed = 0;
		$totalDaysPlayed = 0;
		                                             // processs each assignment record
		foreach ($result as $row) {		  
		  foreach ($row as $name =>$value ) {            
			if ($name == 'balls') {
				$ballCans = 0;
			    $providedBalls = $value;
				if ($providedBalls == "Y") {
					$ballCans = 1;
					$monthBallCans += 1;
					$totalBallCans += 1;
				}
			}
			if ($name == 'setsWon') {
				$setsWon  = $value;
				$monthSetsWon += $setsWon;
				$totalSetsWon += $setsWon;
			}	
			if ($name == 'setsPlayed') {
				$setsPlayed  = $value;
				$monthSetsPlayed += $setsPlayed;
				$totalSetsPlayed += $setsPlayed;
			}	
			if ($name == 'date') {
				$date = $value;
				$monthDaysPlayed += 1;
				$totalDaysPlayed += 1;
			}	
			if ($name == 'facilityName') {
				$facility  = $value;
			}
		  }
                                                       // record fields are now set		  
		  setDateMonth($date);
		  
		  // change month 
          if ($thisDateMonth != $priorDateMonth && $priorDateMonth != 0 && $showSubtotals == "Y") {
			 $monthSetsPlayed -= $setsPlayed;  // back off next month
			 $monthSetsWon -= $setsWon;
			 $monthBallCans -= $ballCans;
			 $monthDaysPlayed -= 1;
			 $setsWonPct = intval(100*$monthSetsWon/$monthSetsPlayed);
		     $ballCansPct = intval(100*$monthBallCans/$monthDaysPlayed);
			 print "  <tr> \n";
			 print "    <td><strong>$priorDateMonthName Totals</strong></td> \n";
			 print "    <td>$monthSetsPlayed</td> \n";
			 print "    <td>$monthSetsWon</td> \n";
			 print "    <td>$setsWonPct %</td> \n";
			 print "    <td>$monthBallCans</td> \n";
			 print "    <td>$ballCansPct %</td> \n";
			 print "    <td> </td>\n";
			 print "  </tr> \n";
			 $priorDateMonth = $thisDateMonth;
			 $monthBallCans = $ballCans;
			 $monthSetsWon = $setsWon;
			 $monthSetsPlayed = $setsPlayed;
			 $monthDaysPlayed = 1;
		  }
		  else {
			 $priorDateMonth = $thisDateMonth;
          }		
		  // daily detail
		  if ($showDetail == "Y") {
			print "  <tr> \n";  
			print "    <td>$date</td> \n";
			print "    <td>$setsPlayed</td> \n";
			print "    <td>$setsWon</td> \n";
			print "    <td> </td> \n";
			print "    <td>$ballCans</td> \n";
			print "    <td> </td> \n";
			print "    <td>$facility</td> \n";
		    print "  </tr> \n";           
		  }
        		  
	    }
		  // final month
		  if ($showSubtotals == "Y") {
            $setsWonPct = intval(100*$monthSetsWon/$monthSetsPlayed);     
		    $ballCansPct = intval(100*$monthBallCans/$monthDaysPlayed);	
            print "  <tr> \n";
			print "    <td><strong>$thisDateMonthName Totals</strong></td> \n";
			print "    <td>$monthSetsPlayed</td> \n";
			print "    <td>$monthSetsWon</td> \n";
			print "    <td>$setsWonPct %</td> \n";
			print "    <td>$monthBallCans</td> \n";
			print "    <td>$ballCansPct %</td> \n";
			print "    <td> </td> \n";
            print "  </tr> \n";
		  }
		  // grand total
          $setsWonPct = intval(100*$totalSetsWon/$totalSetsPlayed);
		  $ballCansPct = intval(100*$totalBallCans/$totalDaysPlayed);	
            print "  <tr> \n";
            print "    <td><strong>Period Totals</strong></td> \n";
			print "    <td>$totalSetsPlayed</td> \n";
			print "    <td>$totalSetsWon</td> \n";
			print "    <td>$setsWonPct %</td> \n";
			print "    <td>$totalBallCans</td> \n";
			print "    <td>$ballCansPct %</td> \n";
			print "    <td> </td> \n";			
			print "  </tr> \n";
			
		   	print "</table> \n";
			
    }		 //end of try
 	
    catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }	
?>
</div>
</body>
</html> 
