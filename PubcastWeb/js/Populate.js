var obj={};
var objAlt={};

function Parse(url, type) {
		//$.mobile.loading( 'show', { text: "Loading. Please wait...", textVisible: true, theme: "c"});
      if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }
    else
  {// code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTPS");
  }
    xmlhttp.open("GET",url,false);
    xmlhttp.send();
    xmlDoc=xmlhttp.responseXML;
    var amnt;
    var ids=xmlDoc.getElementsByTagName("Id");
	if(ids.length>=60)
	{
		amnt=60;
	}
	else
	{
		amnt=ids.length;
	}
    for (i=0;i<amnt;i++)
  {
       var Title="";
       var PubDate="";
       var Affiliation=" ";
       var Abstract="";
       var Authors=""; 
		if(ids[i].childNodes[0].nodeValue==undefined)
		{
			console.log("IDS"+ids[i].childNodes[0].nodeValue);
			$("Process").innerHTML="<div id=\"text\" class=\"abs\" style=\"margin-top:20%\">Enter a query for your custom feed<br /><br />Example: (breast cancer[MeSH Terms]) AND \"PloS one\"[Journal]</div>"+
			"<a  class=\"PMID\" href=\"http://www.youtube.com/watch?v=dncRQ1cobdc&feature=relmfu\">PubMed Advanced Search Builder</a>"+
			"<textarea id =\"quereyfeed\"cols=\"30\" rows=\"5\" style=\"width:90%;margin-top:5%\"value=\"\">"+
			"</textarea><br /><br /><div onclick=\"setCustom()\" class=\"notclicked\" style=\"border: 1px solid black;text-align:center;width:25%;margin-left:35%\">Submit</div>"+
			"<div id= \"error\" style=\"color:red;text-align-center;text-size 11px;\">Query did not have any results please try again</div>";			localStorage.removeItem("Custom_Query");
				Custom();
		}
	   var id=ids[i].childNodes[0].nodeValue;
        var url= "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id="+id+"&retmode=xml";
        xmlhttp=new XMLHttpRequest();
        xmlhttp.open("GET",url,false);
        xmlhttp.send();
        xmlDoc=xmlhttp.responseXML;
        //Title
        Title=xmlDoc.getElementsByTagName("ArticleTitle");
        if (Title.length>0){
            Title=Title[0].childNodes[0].nodeValue;
        }
        
        //Abstract
        AbstractParts=xmlDoc.getElementsByTagName("AbstractText");
        if (AbstractParts!=null){
            for (k=0;k<AbstractParts.length;k++){
                Abstract=" "+AbstractParts[k].childNodes[0].nodeValue;
            }
        }
        
     //Date
     var Dates=xmlDoc.getElementsByTagName("PubMedPubDate");
        var Year="";
        var Month="";
        var Day="";
	if (Dates.length>0){
        for (j=0;j<Dates[Dates.length - 1].childNodes.length;j++){
            if(Dates[Dates.length - 1].childNodes[j].tagName=="Year"){Year= Dates[Dates.length - 1].childNodes[j].textContent;}
            if(Dates[Dates.length - 1].childNodes[j].tagName=="Month"){Month= Dates[Dates.length - 1].childNodes[j].textContent;}
            if(Dates[Dates.length - 1].childNodes[j].tagName=="Day"){Day= Dates[Dates.length - 1].childNodes[j].textContent;}

        }
	}
       PubDate=Month+"/"+Day+"/"+Year;
       
	   //PMID
        var PMID=xmlDoc.getElementsByTagName("PMID");
        if (PMID.length>0)
		{
            PMID=PMID[0].childNodes[0].nodeValue;
		}
        
        //First Author Affiliation
        var Affil=xmlDoc.getElementsByTagName("Affiliation");
         if (Affil.length>0){
            Affiliation=Affil[0].childNodes[0].nodeValue;}
        
		//Journal
        var Journal=xmlDoc.getElementsByTagName("ISOAbbreviation");
        if (Journal.length>0){
            Journal=Journal[0].childNodes[0].nodeValue;}
        
		//AuthorList
        var AuthorList=xmlDoc.getElementsByTagName("Author");
         if (AuthorList.length>0){
            for (l=0;l<AuthorList.length;l++){
                    var LastName="";
                    var ForeName="";
                    var Initials=""; 
                for (h=0;h<AuthorList[l].childNodes.length;h++){
                    if(AuthorList[l].childNodes[h].tagName=="LastName"){LastName= AuthorList[l].childNodes[h].textContent;}
                    if(AuthorList[l].childNodes[h].tagName=="Initials"){Initials= AuthorList[l].childNodes[h].textContent;}
                }
                Authors=Authors+" "+LastName+" "+Initials+ ",";
            }
				Authors = Authors.substring(0, Authors.length - 2);
			}	
        
		//partAbs
		var partAbs=Abstract.substring(0, 120)+"...";
  
             obj[i]={PMID:PMID,Title:Title,PubDate:PubDate,Abstract:Abstract,Authors:Authors,Affiliation:Affiliation,Journal:Journal,partAbs:partAbs};

  }
    var d = new Date();
    var n = Date.now();
    var all={};

  //  localStorage.setItem(type,JSON.stringify(obj));
	localStorage.setItem(type, JSON.stringify(obj));
    localStorage.setItem("Date",n);
    drawList(obj,20);
	document.getElementById("super").removeChild(load);
   
   
}
   
function drawList(obj, amt){
	    objAlt=obj;
    var list="";
    count=0;
    for(var prop in obj) {
        if(obj.hasOwnProperty(prop))
            ++count;
    }
	if(count>0){
		if(count<amt)
		{
			amt=count;
		}
		for (var i=0;i<amt;i++){    
			list=list+"<div id="+i+" class=\"divtable\" onclick=\"drawArticle("+i+")\"><div id =\"item\" class=\"processinner\"><div id =\"title\"class=\"articletitle\">"+obj[i]["Title"]+"</div>"+
			"<br/><div id =\"datejournal\"class=\"datejournal\">"+obj[i]["Journal"]+"  "+obj[i]["PubDate"]+"</div>"+
			"<br/><div id =\"abs\"class=\"subabs\">"+obj[i]["partAbs"]+"</div></div></div>";
        
		
		}
		document.getElementById("Process").innerHTML=list;
	}
	
	
}    


function drawArticle(i){
        localStorage.setItem("title",objAlt[i]["Title"]);
        localStorage.setItem("authors",objAlt[i]["Authors"]);
        localStorage.setItem("date",objAlt[i]["PubDate"]);
        localStorage.setItem("abstract",objAlt[i]["Abstract"]);
        localStorage.setItem("PMID",objAlt[i]["PMID"]);
        localStorage.setItem("affiliation",objAlt[i]["Affiliation"]);
        localStorage.setItem("journal",objAlt[i]["Journal"]);
		localStorage.setItem("gene",objAlt[i]["Gene"]);
		localStorage.setItem("disease",objAlt[i]["Disease"]);
		localStorage.setItem("mutation",objAlt[i]["Mutation"]);

       location.href = "./PubcastAbstract.html";       
}   

 

 function getOutput() {
  getRequest(
      'https://hive.biochemistry.gwu.edu/tools/HivePubcast/TwitterAPI.php', // URL for the PHP file
       drawOutput,  // handle successful request
       drawError    // handle error
  );
  return false;
}  
// handles drawing an error message
function drawError () {
}
// handles the response, adds the html
function drawOutput(responseText) {
    var Twitterobj = responseText;
	Twitterobj =JSON.parse(Twitterobj);
	  localStorage.setItem("Opinion",JSON.stringify(Twitterobj));
	  localStorage.setItem("TwitterDate",Date.now());
	drawTwitter(Twitterobj);
}
// helper function for cross-browser request object
function getRequest(url, success, error) {
    var req = false;
    try{
        // most browsers
        req = new XMLHttpRequest();
    } catch (e){
        // IE
        try{
            req = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            // try an older version
            try{
                req = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e){
                return false;
            }
        }
    }
    if (!req) return false;
    if (typeof success != 'function') success = function () {};
    if (typeof error!= 'function') error = function () {};
    req.onreadystatechange = function(){
        if(req .readyState == 4){
            return req.status === 200 ? 
                success(req.responseText) : error(req.status)
            ;
        }
    }
    req.open("GET", url, true);
    req.send(null);
    return req;
}



