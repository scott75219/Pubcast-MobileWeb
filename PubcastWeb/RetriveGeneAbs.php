<?php

 $servername = "localhost";
 $username = "webcast";
 $password = "Caster53";
 $dbname = "webcasts";
 $conn = new mysqli($servername, $username, $password,$dbname);
$Tab=$_GET["Tab"];
 //executing SQL statement 
$query="SELECT PMID,Title,PubDate,Abstract,Authors,Affiliation,Journal,partAbs,Gene,Disease,Mutation,Weight,Tab_name FROM Pubcast Where Tab_name='$Tab' ORDER BY Weight DESC;";
$result = $conn->query($query);

$array=array();
 while ($row = $result->fetch_assoc()) {
// $info = array('PMID'=>$row["PMID"],'Title'=>$row["Title"],'PubDate'=>$row["PubDate"],'Abstract'=>$row["Abstract"],'Authors'=>$row["Authors"],'Affiliation'=>$row["Affiliation"],'Journal'=>$row["Journal"],'partAbs'=>$row["partAbs"],'Gene'=>$row["Gene"])
 $Title=$row['Title'];
 $ser=unserialize($Title);
 $Title=stripslashes($ser); 
 $row['Title']=$Title; 
 
 
$Affiliation=$row['Affiliation'];	
//$ser=unserialize($Affiliation);	
$Affiliation=stripslashes($Affiliation);
$row['Affiliation']=$Affiliation; 

 $partAbs=$row['partAbs'];
 $ser=unserialize($partAbs);
 $partAbs=stripslashes($ser); 
 $row['partAbs']=$partAbs;
 
 $Abstract=$row['Abstract'];
 $ser=unserialize($Abstract);
 $Abstract=stripslashes($ser); 
 $row['Abstract']=$Abstract; 
 
 $Authors=$row['Authors'];
 $ser=unserialize($Authors);
 $Authors=stripslashes($ser); 
 $row['Authors']=$Authors; 
 
$Disease=$row['Disease'];
$ser=unserialize($Disease);
$Disease=stripslashes($ser); 
$row['Disease']=$Disease; 
 
$Mutation=$row['Mutation'];
$ser=unserialize($Mutation);
$Mutation=stripslashes($ser); 
$row['Mutation']=$Mutation; 

 //echo $Genes."\n";
 array_push($array, $row);

}
 $jsonstring = json_encode($array);
 echo $jsonstring;
?>
