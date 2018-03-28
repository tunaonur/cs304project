<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Query 1</title>
</head>
<body>
	<h1>Search player by jersey number</h1>
	<h5> Enter the jersey number</h5>
	<form method="GET" action="q1.php">
		<input type="text" name="jerseyno">
		<input type="submit" value="Submit" name="get_player_by_jn">
	</form>
</body>
</html>

<?php
  $success = True;
  $db_conn = OCILogon('ora_y2l0b', 'a57023153', "dbhost.ugrad.cs.ubc.ca:1522/ug");

	function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
		//echo "<br>running ".$cmdstr."<br>";
		global $db_conn, $success;
		$statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work

		if (!$statement) {
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn); // For OCIParse errors pass the
			// connection handle
			echo htmlentities($e['message']);
			$success = False;
		}

		$r = OCIExecute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
			echo htmlentities($e['message']);
			$success = False;
		} else {

		}
		return $statement;
	}

	function executeBoundSQL($cmdstr, $list) {
		/* Sometimes a same statement will be excuted for severl times, only
		 the value of variables need to be changed.
		 In this case you don't need to create the statement several times;
		 using bind variables can make the statement be shared and just
		 parsed once. This is also very useful in protecting against SQL injection. See example code below for       how this functions is used */

		global $db_conn, $success;
		$statement = OCIParse($db_conn, $cmdstr);

		if (!$statement) {
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn);
			echo htmlentities($e['message']);
			$success = False;
		}

		foreach ($list as $tuple) {
			foreach ($tuple as $bind => $val) {
				//echo $val;
				//echo "<br>".$bind."<br>";
				OCIBindByName($statement, $bind, $val);
				unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

			}
			$r = OCIExecute($statement, OCI_DEFAULT);
			if (!$r) {
				echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
				$e = OCI_Error($statement); // For OCIExecute errors pass the statementhandle
				echo htmlentities($e['message']);
				echo "<br>";
				$success = False;
			}
		}
	}

	function printResult($result) { //prints results from a select statement
	echo "<br>Got player data from table players:<br>";
	echo "<table>";
	echo "<tr><th>Player ID</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Date of Birth</th>
						<th>Jersey Number</th>
						<th>Position</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row["PID"] . "</td>
							<td>" . $row["PFNAME"] . "</td>
							<td>" . $row["PLNAME"] . "</td>
							<td>" . $row["DOB"] . "</td>
							<td>" . $row["JNO"] . "</td>
							<td>" . $row["POS"] . "</td></tr>"; //or just use "echo $row[0]"
	}
	echo "</table>";

}


if($db_conn){
  if(array_key_exists('get_player_by_jn', $_GET)){
    $players = array (
      ":jerseyno" => $_GET['jerseyno']
    );
    $alltuples = array (
      $players
    );
    executeBoundSQL("select * from players where jno = :jerseyno", $alltuples);
    OCICommit($db_conn);
	}

	if ($_GET && $success) {
	header("location: q1.php");
	} else {
	$result = executePlainSQL("select * from players");
	printResult($result);
	}

	OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}

?>
