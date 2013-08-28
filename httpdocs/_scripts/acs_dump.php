<?php
	// $con = mysql_connect("localhost","journeychurchtv","j0urney3801");
	// $global_arr = array(); // Contains each decoded json (TABLE ROW)
	// $global_keys = array(); // Contains columns for SQL
	// 
	// 
	// if (!$con)
	// 	{
	// 		die('Could not connect: ' . mysql_error());
	// 	}
	// mysql_select_db("ee_", $con);
	// 
	// $f = file_get_contents('/_scripts/access_acs.php');
	// 
	// if(!function_exists('json_decode')) die('Your host does not support json');
	// 
	// $feed = json_decode($f);
	// for($i=0; $i<count($feed['data']); $i++)
	// {
	//     $sql = array();
	//     foreach($feed[$i] as $key => $value){
	//         $sql[] = (is_numeric($value)) ? "`$key` = $value" : "`$key` = '" . mysql_real_escape_string($value) . "'";
	//     }
	//     $sqlclause = implode(",",$sql);
	//     $rs = mysql_query("INSERT INTO testdump SET $sqlclause");
	// }
	// 
	// echo "winning";
?>

<?php
// Create connection
$con=mysqli_connect("localhost","journeychurchtv","j0urney3801","ee_");

// Check connection
if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
$f = file_get_contents('/_scripts/actie.json');
$feed = json_decode($f);

for($i=0; $i<count($feed['data']); $i++)
{
    $sql = array();
    foreach($feed[$i] as $key => $value){
        $sql[] = (is_numeric($value)) ? "`$key` = $value" : "`$key` = '" . mysql_real_escape_string($value) . "'";
    }
    $sqlclause = implode(",",$sql);
    $rs = mysql_query("INSERT INTO acs_calendar SET $sqlclause");
}
  
?>