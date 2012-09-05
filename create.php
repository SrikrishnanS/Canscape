<?php
$myFile = $_POST['filename'];
$file_contents="
<html>
<head>
<title>".$myFile." | Canscape</title>
<meta http-equiv='Content-Type' content='text/html;charset=utf-8'>
<title>Canscape</title>
<link rel='stylesheet' href='css/jtext.css' type='text/css' />
<link rel='stylesheet' href='css/farbtastic.css' type='text/css' />
<link rel='stylesheet' href='css/jquery-ui-1.8.17.custom.css' />
<link rel='stylesheet' href='css/ui.css' type='text/css'/>
<script type='text/javascript' src='js/jquery.min.js'></script>
<script type='text/javascript' src='js/jquery-ui-1.8.17.custom.min.js'></script>
<script type='text/javascript' src='js/farbtastic.js'></script>
<script type='text/javascript' src='js/jquery.svg.js'></script>
<script type='text/javascript' src='js/jquery.svganim.js'></script>
<script type='text/javascript' src='js/jtext.js'></script>
<script type='text/javascript' src='js/exportHTML.js'></script>
<script type='text/javascript'>

var selectedItem;
var textId=0;
var textCache=new Array();
var textMatrix,font_size,font_color;
var editing=false;
var editingText,parent;
var filePath;
var angle=15;
var cx,cy;
var element = new Array();
/* Variables for resizing */
var clientPoint = null;
var rectX = null;
var rectY = null;
var rectWidth = null;
var rectHeight = null;

var resizingLeft = false;
var resizingRight = false;
var resizingTop = false;
var resizingBottom = false;
/* ----- */
$(function() {
	$('#viewport').svg(drawBackground);
	$('#rect,#line,#circle,#ellipse').click(drawShape);
	$('#clear').click(function() 
	{
		$('#clear-confirm').dialog('open');
	});

	$('#delete').click(function()
	{
			deleteNode();
	});

	$('#left').click(function(){
		handleKeyLeft();
	});

	$('#right').click(function(){
			handleKeyRight();
	});

	$('#up').click(function(){
			handleKeyUp();
	});

	$('#down').click(function(){
			handleKeyDown();
	});

	$('#plus').click(function(){
			zoomPlus();
	});

	$('#minus').click(function(){
			zoomMinus();
	});	

	$('#back').click(function(){
			sendToBack();
	});	

	$('#front').click(function(){
			sendToFront();
	});					

	$('#edit_text').click(function(){
			var shape=selectedItem;
			editText(shape);
	});
	
	$('#save').click(function(){
			savePresentation();		
	});

/*	$('#addTextToShape').click(function(){
			addToShape();
	});
*/
	$('#rLeft').click(function(){
			rotateLeft();
	});	

	$('#rRight').click(function(){
			rotateRight();
	});	
	
	$('#ppath').click(function() {
			loadPathContents();
	});

	$('#zoomin').click(function(evt) {
			handleZoomPlus(evt);
	});

	$('#zoomout').click(function(evt) {
			handleZoomMinus(evt);
	});
	
	$('#colorPicker').farbtastic('#color');
	$('#export').click(function() {
		if(selectedItem!=undefined && selectedItem.getAttribute('stroke-dasharray')!=null)
		{
			selectedItem.removeAttribute('stroke-dasharray');
			selectedItem.setAttribute('stroke-opacity','1.0');
		}
		$('#export-text').dialog('open');

	});
	window.onbeforeunload = function (evt) 
	{
		return 'All changes after recent save will be permanently discarded and cannot be recovered.';
	}
});
</script>
<script type='text/javascript'>
var svgNS = 'http://www.w3.org/2000/svg';
var colours = ['purple', 'red', 'orange', 'yellow', 'lime', 'green', 'blue', 'navy', 'black'];
var count=0;
var tileStrokeWidth;
function drawBackground()
{
	var svg = $('#viewport').svg('get');
	svg.circle(0,0,100000,{id:'background',fill:'url(#rulerPattern)',onclick:'deselectChoice(event)'});
	tileStrokeWidth = document.getElementById('tile').getAttribute('stroke-width');
}
function deleteNode()
{
	removeElementByItem(element,window.selectedItem.getAttribute('item'));
	var parent = window.selectedItem.parentNode;
	parent.removeChild(window.selectedItem);
	if(parent.getAttribute('class')=='frame' && !parent.hasChildNodes())
		parent.parentNode.removeChild(parent);
	$('#colorPicker').hide();
}

function drawShape()
{
	var shape = this.id;
	var svg = $('#viewport').svg('get');
	var item;
	if (shape == 'rect') 
	{
		item = svg.rect(random(300), random(200), random(100) + 100, random(100) + 100,
			{onclick: 'select(evt.target,evt)',fill: colours[random(9)], stroke: 'black','stroke-width':1});
	}
	else if (shape == 'line') 
	{
		item = svg.line(random(400), random(300), random(400), random(300),
			{onclick: 'select(evt.target,evt)',stroke: 'black', 'stroke-width':2});
	}
	else if (shape == 'circle') 
	{
		item = svg.circle(random(300) + 50, random(200) + 50, random(80) + 20,
			{onclick: 'select(evt.target,evt)',fill: colours[random(9)], stroke: 'black',
			'stroke-width':2});
	}
	else if (shape == 'ellipse') 
	{
		item = svg.ellipse(random(300) + 50, random(200) + 50, random(80) + 20, random(80) + 20,
			{onclick: 'select(evt.target,evt)',fill: colours[random(9)], stroke: 'black',
			'stroke-width':2});
	}
	count++;
	var id='item'+count;
	item.setAttribute('item',id);
	item.setAttribute('class','shape');
	select(item);
	window.element.push(item)
}

function getShapeCenter(shape)
{
	if(shape.nodeName=='line')
	{
		var x1=parseFloat(shape.getAttribute('x1'));
		var x2=parseFloat(shape.getAttribute('x2'));
		cx=(x1+x2)/2;

		var y1=parseFloat(shape.getAttribute('y1'));
		var y2=parseFloat(shape.getAttribute('y2'));
		cy=(y1+y2)/2;
	}
	
	else if(shape.nodeName=='circle' || shape.nodeName=='ellipse')
	{
		cx=parseFloat(shape.getAttribute('cx'));
		cy=parseFloat(shape.getAttribute('cy'));
		var Sx,Sy,Tx,Ty;
	}
	else if(shape.nodeName=='rect' || shape.nodeName=='image')
	{
		var x1=parseFloat(shape.getAttribute('x'));
		var y1=parseFloat(shape.getAttribute('y'));
		var width=parseFloat(shape.getAttribute('width'));
		var height=parseFloat(shape.getAttribute('height'));
		var x2=x1+width;
		var y2=y1+height;

		cx=(x1+x2)/2;
		cy=(y1+y2)/2;
				
	}
	else if(shape.nodeName=='text')
	{
		var x=parseFloat(shape.getAttribute('x'));
		var y=parseFloat(shape.getAttribute('y'));

		cx=x+10;
		cy=y+10;
	}

}

function rotateRight()
{
	var shape=window.selectedItem;
	getShapeCenter(shape);
	var transform=shape.getAttribute('transform');
	var newTransform='';
	if(transform==null)
	{
		newTransform='matrix(1,0,0,1,0,0),rotate('+angle+','+cx+','+cy+')';
	}
	else
	{
		var rotator=transform.indexOf('rotate');
		var degree;
		if(rotator>=0)
		{
			if(rotator>0)
			{
				newTransform=transform.substring(0,transform.indexOf(',rotate'));
			}
			else
			{
				newTransform='matrix(1,0,0,1,0,0)';
			}
			var r=transform.substring(rotator+7);
			degree=parseFloat(r);
			degree+=angle;
			var addrotate='rotate('+degree+','+cx+','+cy+')';
			if(newTransform=='')
				newTransform+=addrotate;
			else
				newTransform+=','+addrotate;
		}
		else
		{
			degree=angle;
			var addrotate='rotate('+degree+','+cx+','+cy+')';
			newTransform=transform+','+addrotate;
		}
	}
	shape.setAttribute('transform',newTransform);
}

function rotateLeft()
{
	var shape=window.selectedItem;
	getShapeCenter(shape);
	var transform=shape.getAttribute('transform');
	var newTransform='';
	if(transform==null)
	{
		newTransform='matrix(1,0,0,1,0,0),rotate('+(-angle)+','+cx+','+cy+')';
	}
	else
	{
		var rotator=transform.indexOf('rotate');
		var degree;
		if(rotator>=0)
		{
			if(rotator>0)
			{
				newTransform=transform.substring(0,transform.indexOf(',rotate'));
			}
			else
			{
				newTransform='matrix(1,0,0,1,0,0)';
			}
			var r=transform.substring(rotator+7);
			degree=parseFloat(r);
			degree-=angle;
			var addrotate='rotate('+degree+','+cx+','+cy+')';
			if(newTransform=='')
				newTransform+=addrotate;
			else
				newTransform+=','+addrotate;
		}
		else
		{
			degree=(-1)*angle;
			var addrotate='rotate('+degree+','+cx+','+cy+')';
			newTransform=transform+','+addrotate;
		}
	}
	shape.setAttribute('transform',newTransform);
}

function zoomMinus()
{
	var shape=window.selectedItem;

	if(shape.nodeName=='ellipse')
	{
		var rx=shape.getAttribute('rx');
		var ry=shape.getAttribute('ry');
		shape.setAttribute('rx',rx/1.1);
		shape.setAttribute('ry',ry/1.1);		
	}
	else if(shape.nodeName=='circle')
	{
		var r=shape.getAttribute('r');
		shape.setAttribute('r',r/1.1);
	}
	else if(shape.nodeName=='rect')
	{
		var height=shape.getAttribute('height');
		var width=shape.getAttribute('width');
		shape.setAttribute('height',height/1.1);
		shape.setAttribute('width',width/1.1);
	}

	else if(shape.nodeName=='line')
	{
		var x1=parseFloat(shape.getAttribute('x1'));
		var x2=parseFloat(shape.getAttribute('x2'));
		var y1=parseFloat(shape.getAttribute('y1'));
		var y2=parseFloat(shape.getAttribute('y2'));

		var Yfactor=(y2-y1);
		var Xfactor=(x2-x1);
		
		var x3=x2-0.1*Xfactor;
		var y3=y2-0.1*Yfactor;
		if(x3<=x1 || y3<=y1)
		{	
			//to preserve the integrity of the line...it now reduces to a dot
			x3=x1;
			y3=y1;
		}
		shape.setAttribute('x2',x3);
		shape.setAttribute('y2',y3);
	}

	else if(shape.nodeName=='text')
	{
		if(shape.getAttribute('transform')==null)
			shape.setAttribute('transform','matrix(0.909090909,0,0,0.909090909,0,0)');
		else
		{
			var index=shape.getAttribute('transform').indexOf('rotate');
			var addRotator='';
			if(index>=0)
			{
				addRotator=shape.getAttribute('transform').substring(index);
			}
			var matrix=shape.getAttribute('transform').substring(6);
			var matrix_elements=matrix.split(',');
			matrix_elements[0]=matrix_elements[0].substring(1);
			matrix_elements[5]=matrix_elements[5].substring(0,matrix_elements[5].indexOf(')'));
			var sx,sy;
			sx=parseFloat(matrix_elements[0])/1.1;
			sy=parseFloat(matrix_elements[3])/1.1;
			matrix='matrix('+sx+','+matrix_elements[1]+','+matrix_elements[2]+','+sy+','+matrix_elements[4]+','+matrix_elements[5]+')';
			if(addRotator.length>0)
			{
				matrix+=','+addRotator;	
			}
			shape.setAttribute('transform',matrix);			
		}
	}
	
	else if(shape.nodeName=='image')
	{
		var height=parseFloat(shape.getAttribute('height'));
		var width=parseFloat(shape.getAttribute('width'));
		height/=1.1;
		width/=1.1;
		height+='px';
		width+='px';
		shape.setAttribute('height',height);
		shape.setAttribute('width',width);
	}	
}

function zoomPlus()
{
	var shape=window.selectedItem;

	if(shape.nodeName=='ellipse')
	{
		var rx=shape.getAttribute('rx');
		var ry=shape.getAttribute('ry');
		shape.setAttribute('rx',rx*1.1);
		shape.setAttribute('ry',ry*1.1);		
	}
	else if(shape.nodeName=='circle')
	{
		var r=shape.getAttribute('r');
		shape.setAttribute('r',r*1.1);
	}
	else if(shape.nodeName=='rect')
	{
		var height=shape.getAttribute('height');
		var width=shape.getAttribute('width');
		shape.setAttribute('height',height*1.1);
		shape.setAttribute('width',width*1.1);
	}

	else if(shape.nodeName=='line')
	{
		var x1=parseFloat(shape.getAttribute('x1'));
		var x2=parseFloat(shape.getAttribute('x2'));
		var y1=parseFloat(shape.getAttribute('y1'));
		var y2=parseFloat(shape.getAttribute('y2'));

		var Yfactor=(y2-y1);
		var Xfactor=(x2-x1);
		
		var x3=x2+0.1*Xfactor;
		var y3=y2+0.1*Yfactor;
		shape.setAttribute('x2',x3);
		shape.setAttribute('y2',y3);
	}

	else if(shape.nodeName=='text')
	{
		if(shape.getAttribute('transform')==null)
			shape.setAttribute('transform','matrix(1.1,0,0,1.1,0,0)');
		else
		{
			var index=shape.getAttribute('transform').indexOf('rotate');
			var addRotator='';
			if(index>=0)
			{
				addRotator=shape.getAttribute('transform').substring(index);
			}
			var matrix=shape.getAttribute('transform').substring(6);
			var matrix_elements=matrix.split(',');
			matrix_elements[0]=matrix_elements[0].substring(1);
			matrix_elements[5]=matrix_elements[5].substring(0,matrix_elements[5].indexOf(')'));
			var sx,sy;
			sx=parseFloat(matrix_elements[0])*1.1;
			sy=parseFloat(matrix_elements[3])*1.1;
			matrix='matrix('+sx+','+matrix_elements[1]+','+matrix_elements[2]+','+sy+','+matrix_elements[4]+','+matrix_elements[5]+')';
			if(addRotator.length>0)
			{
				matrix+=','+addRotator;	
			}
			shape.setAttribute('transform',matrix);			
		}	
	}
	
	else if(shape.nodeName=='image')
	{
		var height=parseFloat(shape.getAttribute('height'));
		var width=parseFloat(shape.getAttribute('width'));
		height*=1.1;
		width*=1.1;
		height+='px';
		width+='px';
		shape.setAttribute('height',height);
		shape.setAttribute('width',width);
	}	
}

function random(range) {
	return Math.floor(Math.random() * range);
}

function drawImage(imagePath,width,height)
{
	var svg = $('#viewport').svg('get');	
    var item = svg.image(100, 100, width+'px', height+'px', imagePath,{onclick: 'select(evt.target,evt)',stroke:'black'});
	count++;
	var id='item'+count;
	item.setAttribute('item',id);
	item.setAttribute('class','shape');
	select(item);
	window.element.push(item)
    return false;
};

function showTextPopup()
{
	$('#textDiv').dialog('open');
	$('#colorPicker').hide();
}

function showImagePopup()
{
	$('#popUpDiv').dialog('open');
}

function replaceAll(txt, replace, with_this) 
{
  	return txt.replace(new RegExp(replace, 'g'),with_this);
}

</script>
<script type='text/javascript'>
var frameItem = new Array();
function removeByValue(arr, val) 
{
    for(var i=0; i<arr.length; i++) 
    {
        if(arr[i] == val) 
        {
            arr.splice(i, 1);
            break;
        }
    }
}
function groupElements()
{
	var svg = $('#viewport').svg('get');
	var group = svg.group();
	group.setAttribute('class','frame');
	for(var i=0;i<frameItem.length;i++)
	{
		group.appendChild(window.frameItem[i]);
		removeByValue(window.element,window.frameItem[i]);
	}
	window.element.push(group);
	count++;
	var id='item'+count;
	group.setAttribute('item',id);
	//alert(window.element.length);
}
function ungroupElements()
{
	var subroot = window.selectedItem.parentNode;
	var root = subroot.parentNode;
	var child = subroot.firstChild;
	while(child!=null)
	{
		if(child.nodeType==1)
		{
			root.appendChild(child);
			window.element.push(child);
			child = subroot.firstChild;
		}
		else
			child = child.nextSibling;
	}
	removeByValue(window.element,subroot);
	subroot.parentNode.removeChild(subroot);
}
function deselectChoice(event)
{
	var node=event.target;
	if(node.nodeName=='svg' || node.getAttribute('id')=='background')
	{
		deselect();
		for(var i=0;i<window.frameItem.length;i++)
		{
			window.selectedItem=window.frameItem[i];
			deselect();
		}
		//alert(window.frameItem.length);
		window.frameItem = null;
		window.selectedItem=null;
		window.frameItem = new Array();
	}
}

function deselect()
{
	$('#colorPicker').hide();
	if(selectedItem!=undefined)
	{
		selectedItem.setAttribute('stroke-opacity','1.0');
		selectedItem.removeAttribute('stroke-dasharray');
		document.getElementById('font-family').setAttribute('disabled','true');
		document.getElementById('edit_text').setAttribute('disabled','true');
		document.getElementById('back').setAttribute('disabled','true');
		document.getElementById('front').setAttribute('disabled','true');	
		if(selectedItem.nodeName == 'text')
			selectedItem.setAttribute('stroke-width','0.1');
	}
    if (selectedItem == null || selectedItem.nodeName!='rect')
    {
       return;
    }
    resizingLeft = false;
    resizingRight = false;
    resizingTop = false;
    resizingBottom = false;
    var transMatrix = selectedItem.getCTM();
    var rectX = selectedItem.getAttribute('x');
}

function select(shape,evt)
{
	while(shape.nodeName=='tspan')
	{
		shape=shape.parentNode;
	}
	if(evt && evt.shiftKey)
	{
		if(window.frameItem.length==0)
		{
			deselect();
			window.selectedItem=null;
		}
		frameItem.push(shape);
	}
	else
	{
		deselect();
	}
	window.selectedItem=shape;
	document.getElementById('back').removeAttribute('disabled');
	document.getElementById('front').removeAttribute('disabled');		
	if(shape.nodeName!='image')
	{
		shape.setAttribute('stroke-opacity','0.5');
		shape.setAttribute('stroke-dasharray','5,3,2');
		document.getElementById('font-family').setAttribute('disabled','true');		
		document.getElementById('edit_text').setAttribute('disabled','true');
		$('#colorPicker').show();
	}
	if(shape.nodeName=='text')
	{
		shape.setAttribute('stroke-opacity','0.5');
		shape.setAttribute('stroke-width','1');
		shape.setAttribute('stroke-dasharray','5,3,2');
		document.getElementById('font-family').removeAttribute('disabled');
		document.getElementById('edit_text').removeAttribute('disabled');
		$('#colorPicker').show();
	}
	if(shape.nodeName=='rect' && evt!=null)
	{
		var transMatrix = shape.getCTM();
		clientPoint= root.createSVGPoint();
		clientPoint.x = evt.clientX;
		clientPoint.y = evt.clientY;
		
		var tx=0,ty=0;
		var matrix=selectedItem.getAttribute('transform');
		if(matrix!=null)
		{
			matrix=matrix.substring(7);
			var matrix_elements=matrix.split(',');
			matrix_elements[5]=matrix_elements[5].substring(0,matrix_elements[5].indexOf(')'));
			tx=parseFloat(matrix_elements[4]);
			ty=parseFloat(matrix_elements[5]);			
		}
		var Tx=0,Ty=0,Sx=1,Sy=1;
	    matrix=document.getElementById('viewport').getAttribute('transform');
		if(matrix!=null)
		{
			matrix=matrix.substring(7);
			var matrix_elements=matrix.split(',');
			matrix_elements[5]=matrix_elements[5].substring(0,matrix_elements[5].indexOf(')'));
			Tx=parseFloat(matrix_elements[4]);
			Ty=parseFloat(matrix_elements[5]);			
			Sx=parseFloat(matrix_elements[0]);
			Sy=parseFloat(matrix_elements[3]);
		}
		//window.alert(Sx+' '+Sy+' '+Tx+' '+Ty);
		rectX = parseFloat(shape.getAttribute('x'))+tx;
		rectY = parseFloat(shape.getAttribute('y'))+ty;

		rectX*=Sx;
		rectX+=Tx;
		rectY*=Sy;
		rectY+=Ty;
		
		rectWidth = parseFloat(shape.getAttribute('width'))*Sx;
		rectHeight = parseFloat(shape.getAttribute('height'))*Sy;
		
		if ((clientPoint.x - rectX) < 10)
		{
		   resizingLeft = true;				//Never coming up properly
		}
		else if (((rectX + rectWidth) - clientPoint.x) < 10)
		{
		   resizingRight = true;
		}
		else if ((clientPoint.y - rectY) < 10)
		{
		  resizingTop = true;				//Never coming up properly

		}
		else if (((rectY + rectHeight)- clientPoint.y) < 10)
		{
		  resizingBottom = true;
		}
	}
	
}
</script>
<script type='text/javascript'>

function resize(evt)
{
	if(selectedItem==null || selectedItem.nodeName!='rect')
		return;
	if (resizingLeft)
    {
        deltaX = (clientPoint.x - evt.clientX);
        selectedItem.setAttribute('width',rectWidth + deltaX);	
        selectedItem.setAttribute('x',rectX - deltaX);
     }
     else if (resizingRight)
     {
        deltaX = (clientPoint.x - evt.clientX);
        if(rectWidth-deltaX>0)
        	selectedItem.setAttribute('width',rectWidth - deltaX);	
     }
     else if (resizingTop)
     {
        deltaY = (clientPoint.y - evt.clientY);
        selectedItem.setAttribute('height',rectHeight + deltaY);	
        selectedItem.setAttribute('y',rectY - deltaY);
     }
     else if (resizingBottom)
     {
        deltaY = (clientPoint.y - evt.clientY);
        if(rectHeight-deltaY>0)
        	selectedItem.setAttribute('height',rectHeight - deltaY);	
     }	
}
function storeIntoCache(text,index)
{
	//textCache[index]=text;
	var root = document.getElementById('viewport');
	var cache = document.createElementNS(svgNS,'textCache');
	cache.setAttribute('index',index);
	cache.setAttribute('content',text);
	root.appendChild(cache);
}

function getFromCache(index)
{
	var root = document.getElementById('viewport');
	var itemSelected=root.firstChild;
	while(itemSelected!=null)
	{
		if(itemSelected.nodeType==1 && itemSelected.nodeName=='textCache' && itemSelected.getAttribute('index')==index)
		{
			return itemSelected.getAttribute('content');
		}
		itemSelected = itemSelected.nextSibling;
	}
	//return textCache[index]
}

function editText(shape)
{
	textMatrix=shape.getAttribute('transform');
	font_size=parseInt(shape.getAttribute('font-size'));
	font_color=shape.getAttribute('fill');
	editingText=shape;
	parent=shape.parentNode;
	editing=true;
	showTextPopup();
	var index=parseInt(shape.getAttribute('id').substring(4));		//gets the numerical portion of the id
	var htmlText=getFromCache(index);
	$('#wysiwyg').wysiwyg('setContent', htmlText);
}

function constructText()
{
	drawText($('#wysiwyg').val());
	$('#wysiwyg').wysiwyg('setContent','<p>Insert Text Here </p>');
}

function convertToSVG(htmlContent)
{
	storeIntoCache(htmlContent,textId);		//any alternative?
	var style='';
	if(document.getElementById('font-family').getAttribute('disabled')==null)
	{
		var index=document.getElementById('font-family').selectedIndex;
		var fonts=document.getElementById('font-family');
		style=fonts[index].value;		
	}

	if(editing==true)
	{
		if(font_size==undefined || font_size==null)
		{
			font_size=12;
		}
		if(font_color==undefined || font_color==null)
		{
			font_color='#000000';
		}
		if(textMatrix==undefined || textMatrix==null)
		{
			textMatrix='matrix(1,0,0,1,0,0)';
		}
		var t='text'+textId;
		htmlContent=\"<text x='40' y='40' stroke='black' stroke-width='0.1' font-size=''+font_size+'' fill=''+font_color+'' transform=''+textMatrix+'' onclick='select(evt.target,evt)' font-family=''+style+''>\"+htmlContent;
		editing=false;
		window.selectedElement=null;
	}

	else
	{
		var t='text'+textId;
		htmlContent=\"<text x='40' y='40' stroke='black' stroke-width='0.1' font-size='12' fill='#000000' onclick='select(evt.target,evt)' font-family=''+style+''>\"+htmlContent;
    }
       	htmlContent = replaceAll(htmlContent,'<b>',\"<tspan style='font-weight:bold'>\");
       	htmlContent = replaceAll(htmlContent,'<i>',\"<tspan style='font-style:italic'>\");
       	htmlContent = replaceAll(htmlContent,'<u>',\"<tspan style='text-decoration:underline'>\");
       	htmlContent = replaceAll(htmlContent,'<strike>',\"<tspan style='text-decoration:line-through'>\");
       	htmlContent = replaceAll(htmlContent,'<ul>','');
       	htmlContent = replaceAll(htmlContent,'<li>',\"<tspan x='40' dy='25'>&#160;&#160;&#160;&#160;&#8226;&#160;&#160;&#160;&#160;\");
       	htmlContent = replaceAll(htmlContent,'<p>',\"<tspan x='40' dy='25'>\");
		htmlContent = replaceAll(htmlContent,'<div>',\"<tspan x='40' dy='25'>\");

       	htmlContent = replaceAll(htmlContent,\"<p style=\'text-align: left;\'>','<tspan x='40' dy='25' style='text-anchor: start'>\");
       	htmlContent = replaceAll(htmlContent,\"<p style=\'text-align: right;\'>','<tspan x='40' dy='25' style='text-anchor: end'>\");
       	htmlContent = replaceAll(htmlContent,\"<p style=\'text-align: center;\'>','<tspan x='40' dy='25' style='text-anchor: middle'>\");
       	       	       	
       	htmlContent = replaceAll(htmlContent,'&nbsp','&#160');
       	htmlContent = replaceAll(htmlContent,'<br>',\"<tspan x='40' dy='25'>\");
       	htmlContent = replaceAll(htmlContent,'</div>','</tspan>');
       	htmlContent = replaceAll(htmlContent,'</p>','</tspan>');
       	htmlContent = replaceAll(htmlContent,'</li>','</tspan>');
       	htmlContent = replaceAll(htmlContent,'</ul>','');
       	htmlContent = replaceAll(htmlContent,'</strike>','</tspan>');
       	htmlContent = replaceAll(htmlContent,'</u>','</tspan>');
       	htmlContent = replaceAll(htmlContent,'</i>','</tspan>');
       	htmlContent = replaceAll(htmlContent,'</b>','</tspan>');
       	htmlContent+='</text>'
       	return htmlContent;
}

function drawText(htmlContent)
{
	var isPush=true;
	if(editing==true)
	{
		isPush=false;
		editingText.parentNode.removeChild(editingText);
		editingText=null;
	}
	textId++;
	storeIntoCache(htmlContent,textId);		//any alternative?
	var svgText=convertToSVG(htmlContent);
	var svg = $('#viewport').svg('get');
	svg.add(document.getElementById('viewport'),svgText);
	var item = document.getElementById('viewport').lastChild;
	count++;
	var id='item'+count;
	item.setAttribute('id','text'+textId);
	item.setAttribute('item',id);
	item.setAttribute('class','shape');
	select(item);
	if(isPush)
		window.element.push(item);
}

function setFont(font_name)
{
	var shape=selectedItem;
	if(shape.nodeName=='text')
	{
		shape.setAttribute('font-family', font_name);
	}
}

function setColor(colour)



{
	var shape=selectedItem;
	if(shape!=undefined)
	{
		shape.setAttribute('fill',colour);
	}
}
function setTileSize(tileSize)
{
	var pattern = document.getElementById('rulerPattern');
	var tile = document.getElementById('tile');
	pattern.setAttribute('width',tileSize);
	pattern.setAttribute('height',tileSize);
	tile.setAttribute('width',tileSize);
	tile.setAttribute('height',tileSize);
}
function removeElementByItem(arrayName,elementId)
{
	for(var i=0; i<arrayName.length;i++ )
	{ 
		if(arrayName[i].getAttribute('item')==elementId)
			arrayName.splice(i,1); 
	} 
}

function sendToBack()
{
	var element = window.selectedItem;
	if (element.previousSibling)
	{
			var bg = document.getElementById('background');
			element.parentNode.insertBefore(element,bg.nextSibling);
	}
}		
function sendToFront()
{
		var element = window.selectedItem;
		element.parentNode.appendChild(element);
}

function ajaxFileUpload()
{
	
//	$('#uploadFile').submit();
	var xmlhttp;
	xmlhttp=new XMLHttpRequest();		//We forget IE...

	var url='image.php';
	var im=document.getElementById('image');
	var oData = new FormData(document.forms.namedItem('uploadFile'));  
	xmlhttp.open('POST',url,false);
	xmlhttp.onreadystatechange = function() {
		if(xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			filePath=xmlhttp.responseText;	
		}
	}
	xmlhttp.send(oData);

	if(filePath=='__Big Error__')
	{
		window.alert('Big Error');
		return false;
	}
	
	else if(filePath=='__Error__')
	{
		window.alert('Some error');
		return false;
	}
	else if(filePath=='__Invalid file__')
	{
		window.alert('Invalid image file');
		return false;
	}
	else
	{
		var file_info=filePath.split(',');
		var height=file_info[2];
		var width=file_info[1];
		var name=file_info[0];
		if(height=='' || width=='')
		{
			height=width=100;
		}
		drawImage(name,width,height);
	}
}

function fixImage(svgContent)
{
	var newSVG=replaceAll(svgContent,'href','xlink:href');
//	window.alert(newSVG);
	return newSVG;
}
function exportSVG()
{
	var fileName=document.getElementById('fileName').value;
	if(fileName=='index')
	{
		//window.alert('Cannot name the file as index.html');
		return;
	}
	if(fileName.length==0)
		fileName='export';



	for(var i=0;i<window.element.length;i++)
		window.element[i].setAttribute('fno',i+1);
	var xml = $('#viewport').svg('get').toSVG();
	$('#svgexport').html(xml.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'));
	xml = replaceAll(xml,'&nbsp','whitespace');
	xml = replaceAll(xml,'&amp;','amp');
	xml = replaceAll(xml,'&lt;','lt');
	xml = replaceAll(xml,'&gt;','gt');
	xml=fixImage(xml);
	exportToFile(xml,fileName);	
}

function savePresentation()
{
	var text= $('#viewport').svg('get').toSVG();
	text = replaceAll(text,'&nbsp','whitespace');
	text = replaceAll(text,'&amp;','amp');
	text = replaceAll(text,'&lt;','lt');
	text = replaceAll(text,'&gt;','gt');
	text = fixImage(text);
	var xmlhttp=new XMLHttpRequest();
	var url='save.php';
	var params='contents='+text+'&filename=".$myFile."';
	xmlhttp.open('POST',url,false);
	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlhttp.setRequestHeader('Content-length', params.length);
	xmlhttp.setRequestHeader('Connection', 'close');
	xmlhttp.onreadystatechange=function()
	{
		if(xmlhttp.status==200 && xmlhttp.readyState==4)	
		{
			if(xmlhttp.responseText=='')
			{
				$('#saved').dialog('open');
			}
		}
	}
	xmlhttp.send(params);
}

function handleExport(event)
{
	var evt = window.event || event;
	if(evt.keyCode==13)
	{
		exportSVG();
		$('#export-text').dialog('close');
	}
}
</script>
<script type='text/javascript'>
//========================NEW FUNCTIONS ADDED=================
function getElementByItem(itemName)
{
	var root = document.getElementById('viewport');
	var itemSelected=root.firstChild;
	while(itemSelected!=null)
	{
		if(itemSelected.nodeType==1 && itemSelected.getAttribute('item')==itemName)
			return itemSelected;
		itemSelected = itemSelected.nextSibling;
	}
	return null;
}
function selectSpot(hotSpot,event)
{
	select(hotSpot,event);
	$('#colorPicker').hide();
	document.getElementById('spotTitle').value = hotSpot.title;
	document.getElementById('spotContent').value = hotSpot.content;
	$('#hotSpotText').dialog('open');
	hotSpot.setAttribute('title',hotSpot.title);
	hotSpot.setAttribute('content',hotSpot.content);
	hotSpot.setAttribute('class','hotSpot');
}
function addMap()
{
	var svg = $('#viewport').svg('get');
	var spot = svg.rect(150, 150, 175, 125,{onmousemove: 'resize(evt)',onclick: 'selectSpot(event.target,event)',fill: 'lightblue', stroke: 'black',opacity:'0.5','stroke-width':'2'});
	selectSpot(spot);
}

//============================================================
</script>
</head>
<body>

<div id='svgbasics' tabindex='0' onclick='this.focus()'>
<svg xmlns='http://www.w3.org/2000/svg' version='1.1' id='svgcanvas' xmlns:xlink='http://www.w3.org/1999/xlink' onclick='deselectChoice(event)'><defs>
            <pattern 
            	id = 'rulerPattern' 
            	patternUnits='userSpaceOnUse' 
            	x='0' 
            	y='0' 
            	width='75' 
            	height='75'>
				<rect 
					id='tile' 
					x='0' 
					y='0' 
					height='75' 
					width='75' 
					stroke='lightblue' 
					stroke-width='1'
					fill='none'/>
            </pattern> 
        </defs>
				<g 
			id='viewport' 
			onload='storeTransform();zoomNormal()' 
			onclick='zoomTo(event.target);event.stopPropagation();'>
		</g>
<script xlink:href='js/panzoom.js' type='text/javascript'></script>
</svg>
</div>
<script type='text/javascript'>
document.getElementById('svgbasics').addEventListener('keydown', handleKey,false);
</script>
<!-- LEFT -->
<div id='westtoolbar'>
	<button id = 'clear' class='west' title='Clear'><img src='images/clear.png'/></button>
	<button id = 'line' class='west' title='Line'><img src='images/line.png'/></button>
	<button id = 'rect' class='west' title='Rectangle'><img src='images/rect.png'/></button>
	<button id = 'circle' class='west' title='Circle'><img src='images/circle.png'/></button>
	<button id = 'ellipse' class='west' title='Ellipse'><img src='images/ellipse.png'/></button>
	<button id = 'svgtext' class='west' title='Text' onclick='showTextPopup()'><img src='images/text.png'/></button>
	<button id = 'svgimage' class='west' title='Image' onclick='showImagePopup()'><img src='images/image.png'/></button>
	<button id = 'map' class='west' title='Hot Spot' onclick='addMap()' ><img src='images/map.png'/></button>
</div>
<!--      -->
<!-- RIGHT -->
<div id='northtoolbar'>
	<span id='logo'>Canscape</span>
	<button id = 'edit_text' class='north' title='Edit text' disabled='true'><img src='images/edit.png'/></button>
	<select id='font-family' title='Select Font' disabled='true' onchange='setFont(this.options[selectedIndex].value)'>
	  	<option value='serif'>Serif</option>
	   	<option value='sans-serif'>Sans-Serif</option>
	   	<option value='cursive'>Cursive</option>
	   	<option value='fantasy'>Fantasy</option>
	   	<option value='monospace'>Monospace</option>
    </select>
    <select id='tileSize' title='Set Tile Dimension' onchange='setTileSize(this.options[selectedIndex].value)' size=3>

		<option value = '15px'>15px</option>
		<option value = '30px'>30px</option>
		<option value = '45px'>45px</option>
		<option value = '60px'>60px</option>
		<option value = '75px' selected = 'true'>75px</option>
		<option value = '90px'>90px</option>
		<option value = '105px'>105px</option>
		<option value = '120px'>120px</option>
		<option value = '135px'>135px</option>
    </select>
<!--	<button id='addTextToShape' class='north' title='Add Text to Shape' disabled='true' ><img src='images/font.png'/></button> -->
	<button id='save' class='north' title='Save Presentation'><img src='images/save.png' /></button>
    <button id = 'plus' class='north' title='Enlarge'><img src='images/increase.png'/></button>
	<button id = 'minus' class='north' title='Reduce'><img src='images/decrease.png'/></button>
	<button id = 'rLeft' class='north' title='Rotate Anti-clockwise'><img src='images/lrotate.png'/></button>
	<button id = 'rRight' class='north' title='Rotate Clockwise'><img src='images/rrotate.png'/></button>
	<button id = 'back' class='north' title='Send To Back' disabled='true' ><img src='images/back.png'/></button>
	<button id = 'front' class='north' title='Bring To Front'><img src='images/front.png'/></button>
	<button id = 'group' class='north' title='Group Elements To Frame' onclick='groupElements();'><img src='images/group.png'/></button>
	<button id = 'ungroup' class='north' title='Ungroup Elements From Frame' onclick='ungroupElements()'><img src='images/ungroup.png'/></button>
	<button id = 'ppath' class='north' title='Set Navigation Path'><img src='images/path.png'/></button>
	<button id = 'export' class='north' title='Export Presentation'><img src='images/export.png'/></button>
	<button id = 'delete' class='north' title='Delete'><img src='images/delete.png'/></button>
</div>

	<input type='image' id='left' src='images/left.png' name='image'>
	<input type='image' id='up' src='images/up.png' name='image'>
	<input type='image' id='down' src='images/down.png' name='image'>
	<input type='image' id='right' src='images/right.png' name='image'>
	<input type='image' id='zoomin' src='images/zoomin.png' name='image'>
	<input type='image' id='zoomout' src='images/zoomout.png' name='image'>
<!--       -->
<!-- Hidden Div to choose the image -->

<div id='popUpDiv' style='display:none' title='Insert Image'>
<br />
  <fieldset>
  	<form id='uploadFile' name='uploadFile' action='image.php' enctype='multipart/form-data' method='post'>
	    <input type='file' name='image' id='image'><br />
    </form>
  </fieldset>
</div>
<!--                     -->

<!-- Hidden div to insert the text -->
    <div id='textDiv' title='Insert Text' style='display:none'>
        <textarea name='wysiwyg' id='wysiwyg' rows='13' cols='90' style='width:700px' align='center'></textarea>
    </div>
<!--			-->

<!-- Hidden div to show save message -->
    <div id='saved' title='File Save' style='display:none'>
		Presentation has been saved.
    </div>
<!--			-->

<!-- Hidden div to export the file contents -->
	<div id='export-text' title='Export as HTML' style='display:none'>
    	<p>Enter the file name to export. The default file name is 'export.html'</p>
        <input id='fileName' name='fileName' type='text' value='' size='45' onKeyPress='handleExport(evt)'/>
     </div>
<!--			-->

<div id='svgexport' style='display:none'></div>

<div id='colorPicker' style='display:none'></div>
<div id='path' title='Set Presentation Path' style='display:none'></div>
<input type='hidden' id='color' name='color' value='#123456' />		<!-- Storing the color// any alternative? -->
<!-- Hidden div to clear the canvas -->
	<div id = 'clear-confirm' title = 'Clean canvas?'>
		All objects on the canvas will be permanently cleared and cannot be undone. Do you wish to continue?
	</div>
	</div>
<!-- 				-->
<!-- -->
	<div id='hotSpotText' title='Add Hot Spot' style='display:none'>
		Title:<br>
		<input id='spotTitle' name='spotTitle' type='text' size='45' value=''/>
		Description:<br>
		<textarea id='spotContent' name='spotContent' rows='10' cols='45' style='resize:none'></textarea>
	</div>
<!-- -->
<script type='text/javascript' src='js/ui.js'></script>
</body>
</html>
";
$file_content=str_replace("id=\"t\"","id=t",$file_content);
$fh = fopen($myFile, 'w') or die("Can't save file");
fwrite($fh, $file_contents);
fclose($fh);
?>
