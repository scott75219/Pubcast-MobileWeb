<?php
 
 $servername = "localhost";
 $username = "webcast";
 $password = "Caster53";
 $dbname = "webcasts";
 $conn = new mysqli($servername, $username, $password,$dbname);
 $query="";
 $query="Delete FROM Pubcast";
 if ($conn->query($query) === TRUE) 
				{
					echo "Table Deleted successfully\n";
				}
 $conn->close();
  $conn = new mysqli($servername, $username, $password,$dbname);
 
 //Getting Genes from database
 $connGene = new mysqli($servername, $username, $password,$dbname);
 $queryGene="SELECT Gene FROM Genes";
 $GeneResults=$connGene->query($queryGene);
 $connGene->close();
 $Genes = array();
 $QueryArray=array();
  while($row = $GeneResults->fetch_assoc())
	{
		if ($row["Gene"]!=="")
		$Genes[]=$row["Gene"]. " OR ";

	}
	
$x=0;
$y=0;
$last=end($Genes);
$Pubquery="";
 while ($x<count($Genes))
	{
		$Pubquery.=$Genes[$x];
		if($y === 500) 
		{
			array_push($QueryArray, $Pubquery);
			$Pubquery="";
			$y=0;
		}
		else if($Genes[$x]===$last)
		{
			array_push($QueryArray, $Pubquery);
			$Pubquery="";

		}		
		$x++;
		$y++;
		
	}
for ($z=0;$z<count($QueryArray);$z++)
{
	echo "In iteration: $z\n";
	 $Pubquery = $QueryArray[$z];
	 $Pubquery = substr($Pubquery, 0, -4);
	 $url="https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term=(".$Pubquery."[Title/Abstract]) AND cancer mutation AND hasabstract/&reldate=20&datetype=edat&retmax=1000";
	 $dom = new DOMDocument();
	 $dom->load($url);
	 $idList = array();
	 $ids = $dom->getElementsByTagName("Id");
	 
	 //storing each id in to an array
	foreach ($ids as $id)
		{
			$idList[]= $id->nodeValue."\n";
		}
		
	//looping through each ID to get info
	$amnt=count($idList);
	for ($i=0;$i<$amnt;$i++)
		{
		   $Title="";
		   $PubDate="";
		   $Affiliation=" ";
		   $Abstract="";
		   $Authors="";
		   $PMID="";
		   $Gene="";
		   $partAbs="";
		   $Journal="";
		   $TimeStamp="";
		   
			$id=$idList[$i];
			$url= "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=".$id."&retmode=xml";
			$dom = new DOMDocument();
			$dom->load($url);

			//Title
			if ($dom->getElementsByTagName("ArticleTitle")->length>0)
			{
				$Titletemp=$dom->getElementsByTagName("ArticleTitle")->item(0)->nodeValue;
				if (strlen($Titletemp)>0){
					$Title=$Titletemp;
					$ser=serialize($Title);    # safe -- won't count the slash 
					$Title=addslashes($ser);
					
				}
			}
		   
		   //Abstract
			$AbstractParts=$dom->getElementsByTagName("AbstractText");
			if ($AbstractParts!=null){
				foreach ($AbstractParts as $Part)
				{
					$Abstract.=" ".$Part->nodeValue;
				}
			}			
				//partAbs
			$partAbs =substr($Abstract,0,120);
			$ser=serialize($partAbs);    # safe -- won't count the slash 
			$partAbs=addslashes($ser); 
			$ser=serialize($Abstract);    # safe -- won't count the slash 
			$Abstract=addslashes($ser); 
			
			//Date
			 $Year="";
			 $Month="";
			 $Day="";
			 $History=$dom->getElementsByTagName("PubMedPubDate");
			 $Dates=$History->item($History->length-1)->nodeValue;
			 $arr = explode("\n", $Dates);
			if(count($arr)>0)
			{
				$Year= ltrim($arr[1],' ');
				$Month= ltrim($arr[2],' ');
				$Day= ltrim($arr[3],' ');
				
				$PubDate=$Month."/".$Day."/".$Year;
			//	$PubDate = preg_replace('/\s+/', ' ', $PubDate);
			}
				date_default_timezone_set('America/New_York');
				$date = date("Y-m-d",strtotime(str_replace('/','-',$Day."-".$Month."-".$Year)));
			
			//PMID
			$PMIDtemp=$dom->getElementsByTagName("PMID")->item(0)->nodeValue;
			 if (strlen($PMIDtemp)>0)
			 {
				$PMID=$PMIDtemp;
		   }
		   
		   //First Author Affiliation
			if ($dom->getElementsByTagName("Affiliation")->length>0)
			{
				$Affiltemp=$dom->getElementsByTagName("Affiliation")->item(0)->nodeValue;
				if (strlen($Affiltemp)>0)
				{
					
				}
				$Affiliation=$Affiltemp;
				//	$ser=serialize($Affiliation);    # safe -- won't count the slash 
					$Affiliation=addslashes($Affiliation); 
			}
			//Journal	
			if ($dom->getElementsByTagName("ISOAbbreviation")->length>0)
			{
				$Journaltemp=$dom->getElementsByTagName("ISOAbbreviation")->item(0)->nodeValue;
				if (strlen($Journaltemp)>0)
				{
					$Journal=$Journaltemp;
				}
			}
			
		
			
			//AuthorList
			if ($dom->getElementsByTagName("AuthorList")->length>0)
			{
					$AuthorList=$dom->getElementsByTagName("Author");
					for ($l=0;$l<$AuthorList->length;$l++)
					{
						$LastName="";
						$ForeName="";
						$Initials="";
						
						$Length=$AuthorList->item($l)->nodeValue;
						$arr = explode("\n", $Length);
						if(hasChild($AuthorList->item($l))&&$arr>=5){
							$LastName=$AuthorList->item($l)->childNodes->item(1)->nodeValue;
							$Initials=$AuthorList->item($l)->childNodes->item(5)->nodeValue;
							$Authors=$Authors." ".$LastName." ".$Initials.",";
						
						}
					}
					$Authors = rtrim($Authors, ",");
					$ser=serialize($Authors);    # safe -- won't count the slash 
					$Authors=addslashes($ser); 
						
			}
		$Gene=FindGene($Abstract,$Genes);
		 //  echo $Gene;
		 // $Gene="TEST";
		 //  $ser=serialize($Gene);    # safe -- won't count the slash 
		 //  $Gene=addslashes($ser); 
		//  echo "$PMID ".$Gene."\n";
		$Today=date("Y-m-d");
		$query="INSERT INTO Pubcast (PMID,Title,PubDate,Abstract,Authors,Affiliation,Journal,partAbs,Gene,Date) VALUES ('$PMID','$Title','$PubDate','$Abstract','$Authors','$Affiliation','$Journal','$partAbs','$Gene','$date')";
		$Gene = str_replace(' ', '', $Gene);
		if($Gene!=""&&$date<=$Today)
		{
			if ($conn->connect_error) 
			{
				die("Connection failed: " . $conn->connect_error);
			}
			if ($conn->query($query) === TRUE) 
					{
				//		echo "New record created successfully\n";
					}
			else 
					{
						echo "Error: " . $conn->error."PMID: $PMID\n";
						
					}
			$conn->close();
		   $conn = new mysqli($servername, $username, $password,$dbname);
			FindMD($PMID,$conn);
			
		}
		$conn->close();
		$conn = new mysqli($servername, $username, $password,$dbname);

		}
	$conn->close();
	echo "Sleeping\n";
	sleep(60);
	$conn = new mysqli($servername, $username, $password,$dbname);
}

function FindGene($Abs,$Genes) {
    $G="";
	$Dec=stripslashes($Abs);
	$Abs=unserialize($Dec);
	
	$patterns = array();
	$patterns[0] = '/\-/';
	$patterns[1] = '/\(/';
	$patterns[2] = '/\)/';
	$patterns[3] = '/\./';
	$patterns[4] = '/\,/';

	$replacements = array();
    $replacements[4] = ' ';
	$replacements[3] = ' ';
	$replacements[2] = ' ';
	$replacements[1] = ' ';
	$replacements[0] = ' ';
	$Abs=preg_replace($patterns, $replacements, $Abs);
	$arrlength = count($Genes);

	for($x = 0; $x < $arrlength; $x++) 
	{
		 $Symbol=$Genes[$x];
		 $Symbol = substr($Symbol, 0, -4);
		// if(strlen($Symbol)===1){echo "Symbol:".$Symbol."\n";}
		if ($Symbol!==""&&$Symbol!==" "&&strlen($Symbol)>2)
		{
			$SymbolLength=strlen($Symbol);
			$SymbolTail=substr($Symbol,1,$SymbolLength);
			$SymbolTail=strtolower ($SymbolTail);
			$SymbolHead=substr($Symbol,0,1);
			$Symbol2=$SymbolHead.$SymbolTail; 
			
			if (strpos($Abs, ' '.$Symbol.' ') !== FALSE)
				$G.=$Symbol." ";
		}
	}
	return $G;
}	

function hasChild($p) {
	if ($p->hasChildNodes()) {
	  foreach ($p->childNodes as $c) {
	   if ($c->nodeType == XML_ELEMENT_NODE)
		return true;
	  }
	}
}

function FindMD($ID,$connection)
{
	$Mutations="";
	$Diseases="";
	$url= file_get_contents("http://www.ncbi.nlm.nih.gov/CBBresearch/Lu/Demo/PubTator/abstract_ann.cgi?Disease=1&Gene=1&Chemical=1&Mutation=1&Species=1&pmid=".$ID);
	$lines = explode("\n", $url);
	foreach ($lines as $line)
    {
        $row = explode("\t", $line);
		$size=count($row);
		if($size>1)
		{
			if($row[4]==="Disease") 
				$Diseases=$Diseases.$row[3].",";	
			
			else if($row[4]==="Mutation") 
				$Mutations=$Mutations.$row[3].",";
		}
	}
	$Diseases = rtrim($Diseases, ",");
	$ser=serialize($Diseases);    # safe -- won't count the slash 
	$Diseases=addslashes($ser); 
	
	$Mutations = rtrim($Mutations, ",");
	$ser=serialize($Mutations);    # safe -- won't count the slash 
	$Mutations=addslashes($ser); 
	
	$query="UPDATE Pubcast SET Mutation='$Mutations', Disease='$Diseases' WHERE PMID=$ID";
	if ($connection->connect_error) 
	{
		die("Connection failed: " . $connection->connect_error);
	}
	if ($connection->query($query) === TRUE) 
	{
		//echo "New record created successfully\n";
	}
	else 
	{
		echo "Error: " . $connection->error."$query\n";				
	}

}
?>
