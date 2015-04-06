<?php
$servername = "localhost";
$username = "webcast";
$password = "Caster53";
$dbname = "webcasts";
$conn = new mysqli($servername, $username, $password,$dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$query="";
$handle = fopen("BioMuta_genelist_nonredun.csv", "r");

$file = escapeshellarg( 'BioMuta_genelist_nonredun.csv' );
$last_line = `tail -n 1 $file`;
$last_line = str_replace("\n", "", $last_line);
$last_line = str_replace("\r", "", $last_line);
$last_line = ltrim($last_line, " ");
$last_line = rtrim($last_line, " ");


$x=0;
if ($handle) 
{
    while (($line = fgets($handle)) !== false) 
	{
		$line = str_replace("\n", "", $line);
		$line = str_replace("\r", "", $line);
		$line = ltrim($line, " ");
		$line = rtrim($line, " ");
		
		if ($last_line!=$line)
		{
			$query.="INSERT INTO Genes (Gene) VALUES ('$line');";
			$x++;
		}
		else
		{
		   $query.="INSERT INTO Genes (Gene) VALUES ('$line')";
		   if ($conn->multi_query($query) === TRUE) 
			{
				echo "New records created successfully\n";
				$x=0;
				$query="";
			}
			else 
			{
				echo "Error: " . $conn->error;
			}
			$conn->close();
		}
		
		if($x==500)
		{
			
			if ($conn->multi_query($query) === TRUE) 
			{
				echo "New records created successfully\n";
				$x=0;
				$query="";
			}
			else 
			{
				echo "Error: " . $conn->error;
			}
			$conn->close();
			$conn = new mysqli($servername, $username, $password,$dbname);

		}
    }
}


?>