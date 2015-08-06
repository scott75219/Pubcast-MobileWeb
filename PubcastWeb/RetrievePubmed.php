<?php
 $Action = $argv[1];
 $Tab = $argv[2];

 $servername = "localhost";
 $username = "webcast";
 $password = "Caster53";
 $dbname = "webcasts";
 $query="";
 $QueryArray=array();
 $Genes = array();
 $Abbr = array("Nature"=>41.5, 
 "Science"=>31.4, 
 "Nucleic Acids Res."=>8.8, 
 "Database"=>1,
 "Nat Commun"=>11.47,
 "Sci Rep"=>5.57,
 "Elife"=>9.32,
 "N. Engl. J. Med."=>1,
 "Nat. Med."=>27.4,
 "Proc. Natl. Acad. Sci. U.S.A"=>9.674,
 "Cells"=>1,
 "Nat. Genet."=>29.35,
 "Lancet"=>45.2,
 "PLoS Med."=>14.42, 
 "Blood"=>9.775,
 "Trends Genet."=>11.57);
 echo $Action."\n";
 echo $Tab."\n";
 


 function MakeGeneQuery()
 {
	 global $servername,$username,$password,$dbname,$QueryArray,$Genes;
	 $connGene = new mysqli($servername, $username, $password,$dbname);
	 $queryGene="SELECT Gene FROM Genes";
	 $GeneResults=$connGene->query($queryGene);
	 $connGene->close();
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

 }

function GetContents($GetGenes,$url)
{ 
		 global $servername,$username,$password,$dbname,$QueryArray,$Abbr,$Tab,$Genes;
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
			   $Weight=0;
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
						if (in_array($Journal, $Abbr)) 
						{
							$Weight=$Abbr[$Journal];
						}
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
			
			$Today=date("Y-m-d");
			if($GetGenes==="Yes")
			{
				$Gene=FindGene($Abstract,$Genes);
				$Gene = str_replace(' ', '', $Gene);
			}
			else
			{
				$Gene="NONE";
			}
			if($Gene!=""&&$date<=$Today)
			{
				$query="INSERT INTO Pubcast (PMID,Title,PubDate,Abstract,Authors,Affiliation,Journal,partAbs,Gene,Date,Weight,Tab_name) VALUES ('$PMID','$Title','$PubDate','$Abstract','$Authors','$Affiliation','$Journal','$partAbs','$Gene','$date','$Weight','$Tab')";
				$conn = new mysqli($servername, $username, $password,$dbname);
				if ($conn->connect_error) 
				{
					die("Connection failed: " . $conn->connect_error);
				}
				if ($conn->query($query) === TRUE) 
						{
							//echo "New record created successfully\n";
						}
				else 
				{
						echo "Error: " . $conn->error."PMID: $PMID\n";
								
				}
				$conn->close();	
				if($GetGenes=="Yes")
				{
					$conn = new mysqli($servername, $username, $password,$dbname);
					FindMD($PMID,$conn);
					$conn->close();
				}
			}
	}	
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
		echo "New record created successfully\n";
	}
	else 
	{
		echo "Error: " . $connection->error."$query\n";				
	}
}
function GetGenes()
{
	global $QueryArray;
	MakeGeneQuery();
	for ($z=0;$z<count($QueryArray);$z++)
	{
		echo "In iteration: $z\n";
		$Pubquery = $QueryArray[$z];
		$Pubquery = substr($Pubquery, 0, -4);
		$url="https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term=(".$Pubquery."[Title/Abstract]) AND cancer mutation AND hasabstract/&reldate=20&datetype=edat&retmax=1000";
		GetContents("Yes",$url);
		echo "Sleeping\n";
		sleep(60);
	}
}

function GetGenomics()
{
	$query=60;
	$url="https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term=(Genomics)+NOT+Proteomics+AND%20hasabstract/&reldate=10&datetype=edat&retmax=".$query."&usehistory=y";
	GetContents("No",$url);
	
}

function GetProteomics()
{
	$query=60;
	$url="https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term=(Proteomics)+NOT+Genomics+AND%20hasabstract/&reldate=10&datetype=edat&retmax=".$query."&usehistory=y";
	GetContents("No",$url);
}
function Activate_Action()
{
		 global $servername,$username,$password,$dbname,$Tab,$Action;

	if ($Action === "Delete")
	{
		$conn = new mysqli($servername, $username, $password,$dbname);
		$query="Delete FROM Pubcast";
		if ($conn->query($query) === TRUE) 
					{
						echo "Table Deleted successfully\n";
					}
		$conn->close();
		exit;
	}
	elseif($Action === "Add")
	{
		switch ($Tab)
		{
			case "Genes":
				 echo "Getting Genes\n";
				 GetGenes();
				break;
			case "Genomics":
				echo "Getting Genomics\n";
				GetGenomics();
				break;
			case "Proteomics":
				echo "Getting Proteomics\n";
				GetProteomics();
				break;
		}
	}

}
Activate_Action();
?>
