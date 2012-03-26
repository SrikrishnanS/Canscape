$(function() 
{
    $('#wysiwyg').wysiwyg("setContent","<p>Insert Text Here </p>");
	$('#wysiwyg').wysiwyg({
		controls: 
		{
			insertImage : { visible : false},
			createLink :  { visible : false},
			indent :  { visible : false},
			outdent :  { visible : false},
			insertOrderedList : { visible : false},
			superscript : { visible : false},
			subscript : { visible : false},				
			insertTable : { visible : false},
			code :{ visible : false},
			insertHorizontalRule: {visible : false},
			increaseFontSize : {visible : false},
			decreaseFontSize : { visible : false}
		}
	});
});
$('#textDiv').dialog(
{
	autoOpen: false,
	width: 790,
	height: 400,
	resizable : false,
	buttons: 
	{
		"Insert": function() 
		{ 
			$(this).dialog("close");
			constructText();
		}, 
		"Cancel": function()
		{ 
			editing = false;
			$(this).dialog("close");
		} 
	}
});

$('#saved').dialog(
{
	autoOpen: false,
	width: 400,
	height: 200,
	resizable: false,
	buttons:
	{
		"OK": function()
		{
				$(this).dialog('close');
		}
	}
});

$('#popUpDiv').dialog({
	autoOpen: false,
	width: 500,
	height: 250,
	resizable : false,
	buttons: 
	{
		"Insert" : function() 
		{ 
			$(this).dialog("close");
			ajaxFileUpload();
		},
		"Cancel": function()
		{ 
			$(this).dialog("close");
		} 
	}
});
var loaded=false;
var selectedElement=null;
function swapArrayElements(array_object, index_a, index_b)
{
    var temp = array_object[index_a];
    array_object[index_a] = array_object[index_b];
    array_object[index_b] = temp;
}
function highlight(element) 
{
	var idSelect = element.getAttribute('link');
	var itemSelect = getElementByItem(idSelect);
	select(itemSelect);
	if(window.selectedElement!=null)
		lowlight(window.selectedElement);
	element.style.backgroundColor = "black";
	element.style.color="white";
	window.selectedElement=element;
	return;
}
function lowlight(element) 
{
	element.style.backgroundColor = "white";
	element.style.color="black";
	return;
}
function loadPathContents()
{
	var root = document.getElementById("path");
	var index=0;
	while(root.firstChild!=null)
	{
		root.removeChild(root.firstChild);
	}
	while(index < window.element.length)
	{
		var node = document.createElement("p");
		node.setAttribute("onclick","highlight(this)");
		node.setAttribute("style","cursor:pointer;");
		node.setAttribute("link",window.element[index].getAttribute('item'));
		root.appendChild(node);
		var itemSelect = window.element[index];
		var text = document.createTextNode(itemSelect.nodeName+" [ "+itemSelect.getAttribute('item')+" ]");
		node.appendChild(text);
		index++;
	}
	window.loaded = true;
	$('#path').dialog('open');
}
$(function() 
{
	$('#path').dialog(
	{
		autoOpen: false,
		width: 525,
		height: 300,
		resizable : false,
		buttons: 
		{
			"Move Up" : function() 
			{ 
				var node = window.selectedElement;
				if(node!=null && node.previousSibling!=null)
				{
					node.parentNode.insertBefore(node,node.previousSibling);
					var idSelect = node.getAttribute('link');
					var itemSelect = getElementByItem(idSelect);
					var index = window.element.indexOf(itemSelect);
					swapArrayElements(window.element,index,index-1);
				}
			},
			"Move Down" : function() 
			{ 
				var node = window.selectedElement;
				if(node!=null && node.nextSibling!=null)
				{
					node.parentNode.insertBefore(node.nextSibling,node);
					var idSelect = node.getAttribute('link');
					var itemSelect = getElementByItem(idSelect);
					var index = window.element.indexOf(itemSelect);
					swapArrayElements(window.element,index,index+1);
				}
			},
			"Done": function() 
			{
				if(window.selectedElement!=null)
					lowlight(window.selectedElement);
				$(this).dialog("close");
			} 
		}
	});
});
$("#clear-confirm").dialog(
{
	autoOpen : false,
	resizable :false,
	draggable : false,
	buttons: 
	{
		"Yes" : function() 
		{
			$('#viewport').svg('get').clear();
			window.element.length=0;
			$(this).dialog("close");
			$("#colorPicker").hide();
			drawBackground();
		},
		Cancel : function() 
		{
			$(this).dialog("close");
		}
	}
});
$("#export-text").dialog(
{
	autoOpen : false,
	resizable :false,
	draggable : false,
	width: 525,
	height: 250,
	buttons: 
	{
		"OK" : function() 
		{
			exportSVG();
			$(this).dialog("close");
		},
		Cancel : function() 
		{
			$(this).dialog("close");
		}
	}
});
$("#hotSpotText").dialog(
{
	autoOpen : false,
	resizable :false,
	width: 525,
	buttons: 
	{
		"Done" : function() 
		{
			window.selectedItem.title=document.getElementById("spotTitle").value;
			window.selectedItem.content=document.getElementById("spotContent").value;
			selectSpot(window.selectedItem,null);
			deselect();
			window.selectedItem=null;
			$(this).dialog("close");
		},
		Cancel : function() 
		{
			$(this).dialog("close");
		}
	}
});
