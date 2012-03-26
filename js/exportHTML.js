function exportToFile(content,fileName)
{
	ajaxCreateExport(content,fileName);
	openFile(fileName+".html");
}

function openFile(fileName)
{
	window.open (fileName, 'newwindow', config='fullscreen=yes, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=no, directories=no, status=no');	
}

function ajaxCreateExport(content,fileName)
{
	var xmlhttp;
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}	
	var url = "export.php";
	/*YOUR EYES HERE*/
	var params = "exportContent="+content+"&fileName="+fileName;
	xmlhttp.open("POST", url, false);
	//Send the proper header information along with the request
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.setRequestHeader("Connection", "close");
	
	xmlhttp.onreadystatechange = function() {//Call a function when the state changes.
		if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			//done
		}
	}
	xmlhttp.send(params);	
}
