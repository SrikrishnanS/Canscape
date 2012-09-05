"use strict";

var enablePan = 1; // 1 or 0: enable or disable panning (default enabled)
var enableZoom = 1; // 1 or 0: enable or disable zooming (default enabled)
var enableDrag = 1; // 1 or 0: enable or disable dragging (default disabled)
var zoomScale = 0.2; // Zoom sensitivity

var currentZoomLevel=1;
var root=document.getElementById("svgcanvas");

var state = 'none', svgRoot = null, stateTarget, stateOrigin, stateTf;

setupHandlers(root);

/**
 * Register handlers
 */
function setupHandlers(root){
	setAttributes(root, {
		"onmouseup" : "handleMouseUp(evt)",
		"onmousedown" : "handleMouseDown(evt)",
		"onmousemove" : "handleMouseMove(evt)",
		//"onmouseout" : "handleMouseUp(evt)", // Decomment this to stop the pan functionality when dragging out of the SVG element
	});

	if(navigator.userAgent.toLowerCase().indexOf('webkit') >= 0)
		root.addEventListener('mousewheel', handleMouseWheel, false); // Chrome/Safari
	else
		root.addEventListener('DOMMouseScroll', handleMouseWheel, false); // Others
}

/**
 * Retrieves the root element for SVG manipulation. The element is then cached into the svgRoot global variable.
 */
function getRoot(root) {
	if(svgRoot == null) {
		var r = root.getElementById("viewport") ? root.getElementById("viewport") : root.documentElement, t = r;

		while(t != root) {
			if(t.getAttribute("viewBox")) {
				setCTM(r, t.getCTM());

				t.removeAttribute("viewBox");
			}

			t = t.parentNode;
		}

		svgRoot = r;
	}

	return svgRoot;
}

/**
 * Instance an SVGPoint object with given event coordinates.
 */
function getEventPoint(evt) {
	var p = root.createSVGPoint();

	p.x = evt.clientX;
	p.y = evt.clientY;
	return p;
}

/**
 * Sets the current transform matrix of an element.
 */
function setCTM(element, matrix) {
	var s = "matrix(" + matrix.a + "," + matrix.b + "," + matrix.c + "," + matrix.d + "," + matrix.e + "," + matrix.f + ")";

	element.setAttribute("transform", s);
}

/**
 * Dumps a matrix to a string (useful for debug).
 */
function dumpMatrix(matrix) {
	var s = "[ " + matrix.a + ", " + matrix.c + ", " + matrix.e + "\n  " + matrix.b + ", " + matrix.d + ", " + matrix.f + "\n  0, 0, 1 ]";

	return s;
}

/**
 * Sets attributes of an element.
 */
function setAttributes(element, attributes){
	for (var i in attributes)
		element.setAttributeNS(null, i, attributes[i]);
}

/**
 * Handle mouse wheel event.
 */
function handleMouseWheel(evt) {
	if(!enableZoom)
		return;

	if(evt.preventDefault)
		evt.preventDefault();

	evt.returnValue = false;

	var svgDoc = evt.target.ownerDocument;
	var delta;

	if(evt.wheelDelta)
		delta = evt.wheelDelta / 360; // Chrome/Safari
	else
		delta = evt.detail / -9; // Mozilla
	var z = Math.pow(1 + zoomScale, delta);
	
	var g = getRoot(svgDoc);
	
	var p = getEventPoint(evt);

	p = p.matrixTransform(g.getCTM().inverse());

	// Compute new scale matrix in current mouse position
	var k = root.createSVGMatrix().translate(p.x, p.y).scale(z).translate(-p.x, -p.y);
    setCTM(g, g.getCTM().multiply(k));

	if(typeof(stateTf) == "undefined")
		stateTf = g.getCTM().inverse();

	stateTf = stateTf.multiply(k.inverse());

	adjustTileDimension();
	
}

/**
 * Handle mouse move event.
 */
function handleMouseMove(evt) 
{
	if(evt.preventDefault)
		evt.preventDefault();

	evt.returnValue = false;
	

	var svgDoc = evt.target.ownerDocument;
	var g = getRoot(svgDoc);

	if(state == 'pan' && enablePan) {
		// Pan mode
		var p = getEventPoint(evt).matrixTransform(stateTf);

		setCTM(g, stateTf.inverse().translate(p.x - stateOrigin.x, p.y - stateOrigin.y));
	} else if(state == 'drag' && enableDrag) {
		// Drag mode
		var p = getEventPoint(evt).matrixTransform(g.getCTM().inverse());
		setCTM(stateTarget, root.createSVGMatrix().translate(p.x - stateOrigin.x, p.y - stateOrigin.y).multiply(g.getCTM().inverse()).multiply(stateTarget.getCTM()));
		stateOrigin = p;
	}
}

/**
 * Handle click event.
 */
function handleMouseDown(evt) {

	if(evt.preventDefault)
		evt.preventDefault();

	evt.returnValue = false;

	var svgDoc = evt.target.ownerDocument;

	var g = getRoot(svgDoc);

	if(
		evt.target.getAttribute('id') == "background" 
		|| !enableDrag // Pan anyway when drag is disabled and the user clicked on an element 
	) {
		// Pan mode
		state = 'pan';

		stateTf = g.getCTM().inverse();

		stateOrigin = getEventPoint(evt).matrixTransform(stateTf);
	} else {
		// Drag mode
		state = 'drag';
		var eventTarget=evt.target;
		//Workaround for the tspan
		if(eventTarget.nodeName!="tspan")
			stateTarget = eventTarget;
		
		else
		{
			while(eventTarget.nodeName=="tspan")
			{
				eventTarget=eventTarget.parentNode;
			}
			stateTarget = eventTarget;
		}
		
		if(eventTarget.parentNode.getAttribute('class')=='frame')
		{
			eventTarget=eventTarget.parentNode;
			stateTarget = eventTarget;
		}
		
		stateTf = g.getCTM().inverse();

		stateOrigin = getEventPoint(evt).matrixTransform(stateTf);
	}
}

/**
 * Handle mouse button release event.
 */
function handleMouseUp(evt) {
	if(evt.preventDefault)
		evt.preventDefault();

	evt.returnValue = false;

	var svgDoc = evt.target.ownerDocument;

	if(state == 'pan' || state == 'drag') {
		// Quit pan mode
		state = '';
	}
}

/**
*  Key Events for panning
*/

function handleKeyLeft()
{
	var g=document.getElementById("viewport");
	setCTM(g,g.getCTM().translate(100,0));
}

function handleKeyRight()
{
	var g=document.getElementById("viewport");
	setCTM(g,g.getCTM().translate(-100,0));
}

function handleKeyUp()
{
	var g=document.getElementById("viewport");
	setCTM(g,g.getCTM().translate(0,100));
}

function handleKeyDown()
{
	var g=document.getElementById("viewport");
	setCTM(g,g.getCTM().translate(0,-100));
}

function handleZoomPlus(evt)
{
	var z = Math.pow(1 + zoomScale, 0.35);
	
	var g = document.getElementById("viewport");
	
	var p = getEventPoint(evt);
	p.x/=2;
	p.y/=2;
	p = p.matrixTransform(g.getCTM().inverse());

	// Compute new scale matrix in current mouse position
	var k = root.createSVGMatrix().translate(p.x, p.y).scale(z).translate(-p.x, -p.y);
        setCTM(g, g.getCTM().multiply(k));

	if(typeof(stateTf) == "undefined")
		stateTf = g.getCTM().inverse();

	stateTf = stateTf.multiply(k.inverse());
	adjustTileDimension();
}
function handleZoomMinus(evt)
{
	var z = Math.pow(1 + zoomScale, -0.35);
	
	var g = document.getElementById("viewport");
	
	var p = getEventPoint(evt);
	p.x/=2;
	p.y/=2;
	p = p.matrixTransform(g.getCTM().inverse());

	// Compute new scale matrix in current mouse position
	var k = root.createSVGMatrix().translate(p.x, p.y).scale(z).translate(-p.x, -p.y);
        setCTM(g, g.getCTM().multiply(k));

	if(typeof(stateTf) == "undefined")
		stateTf = g.getCTM().inverse();

	stateTf = stateTf.multiply(k.inverse());
	adjustTileDimension();
}
function handleKey(event)
{
	var evt = window.event || event;
	if(evt.keyCode==37)		//Pan left
	{
		handleKeyLeft();
	}
	else if(evt.keyCode==38) 	//Pan up
	{
		handleKeyUp();
	}
	else if(evt.keyCode==39)	//Pan Right
	{
		handleKeyRight();
	}
	else if(evt.keyCode==40)	//Pan Down
	{
		handleKeyDown();
	}
	else if (evt.keyCode==69)	//For editing the text, on pressing the letter "e"
	{
		var shape=window.selectedItem;
		if(shape.id.indexOf("text")>=0)
		{
			editText(shape);		
		}	
	}
	else if(evt.keyCode==27)
	{
		deselect();	
	}
	else if(evt.keyCode==46)
	{	
		deleteNode();	
	}
}
/*
 * Compute the scaling parameters to normalize the stroke width of the tiles
 */
function adjustTileDimension()
{	
	var svg = document.getElementById('viewport');
	var matrix=svg.getAttribute('transform').substring(7);
	var matrix_elements=matrix.split(',');
	var scale = parseFloat(matrix_elements[0]);
	document.getElementById('tile').setAttribute('stroke-width',1/scale);
}
