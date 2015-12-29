/*++ from bonsai.js ++ urlObj  +++++++++++++++++++++++++++++++++++++++++*/
function urlObj(url) {
   var urlBaseAndParameters;

   urlBaseAndParameters = url.split("?"); 
   this.urlBase = urlBaseAndParameters[0];
   this.urlParameters = urlBaseAndParameters[1].split(/[;&]/);

   this.getUrlBase = urlObjGetUrlBase;
}

/*++ from bonsai.js ++ urlObjGetUrlBase  +++++++++++++++++++++++++++++++*/

function urlObjGetUrlBase() {
   return this.urlBase;
}


// example with minimum dimensions
var myCropper;

var StartEpoch = 0;
var EndEpoch = 0;



function changeRRDImage(coords,dimensions){

    var SelectLeft = Math.min(coords.x1,coords.x2);

    var SelectRight = Math.max(coords.x1,coords.x2);

    if (SelectLeft == SelectRight)
         return; // abort if nothing is selected.

    var RRDLeft  = 67;        // difference between left border of RRD image and content
    var RRDRight = 26;        // difference between right border of RRD image and content
    var RRDImgWidth  = $('zoom').getDimensions().width;       // Width of the Smokeping RRD Graphik
    var RRDImgUsable = RRDImgWidth - RRDRight - RRDLeft;  
    var form = $('range_form');   
    
    if (StartEpoch == 0)
        StartEpoch = +$F('epoch_start');
   
    if (EndEpoch  == 0)
        EndEpoch = +$F('epoch_end');

    var DivEpoch = EndEpoch - StartEpoch; 
	
	var Type = $F('type');
	
	if ($F('type').indexOf("aggr") <= -1 && $F('type').indexOf("inter") <= -1){
		var Rrdfile = $F('rrdfile');
	}
	else if($F('type').indexOf("aggr") > -1)
	{
		var AggrID = $F('rrdfile');
	}
	else if($F('type').indexOf("inter") > -1)
	{
		var interID = $F('rrdfile');
	}
	
	var Width = $F('width');
	var Height = $F('height');
    // construct Image URL
    var myURLObj = new urlObj(document.URL); 

    // var myURL = myURLObj.getUrlBase(); 
    // var myURL = 'http://nms.bc.net/cmdb/rrdgraph.php';
	var myURL = 'rrdgraph.php';

    // Generate Selected Range in Unix Timestamps
    var LeftFactor = 1;
    var RightFactor = 1;

    if (SelectLeft < RRDLeft)
        LeftFactor = 10;        

    StartEpoch = Math.floor(StartEpoch + (SelectLeft  - RRDLeft) * DivEpoch / RRDImgUsable * LeftFactor );

    if (SelectRight > RRDImgWidth - RRDRight)
        RightFactor = 10;

    EndEpoch  =  Math.ceil(EndEpoch + (SelectRight - (RRDImgWidth - RRDRight) ) * DivEpoch / RRDImgUsable * RightFactor);

	if ($F('type').indexOf("aggr") <= -1 && $F('type').indexOf("inter") <= -1){
		$('zoom').src = myURL + "?zoom&from=" + StartEpoch + '&to=' + EndEpoch + '&file=' + Rrdfile  + '&type=' + Type + '&width=' + Width +'&height=' + Height;
	}
	else if($F('type').indexOf("aggr") > -1)
	{
		$('zoom').src = myURL + "?zoom&from=" + StartEpoch + '&to=' + EndEpoch + '&aggr_id=' + AggrID + '&title=' + AggrID + '&type=' + Type + '&width=' + Width +'&height=' + Height;
	}
	else if ($F('type').indexOf("inter") > -1)
	{
		//graph.php?file=service_id_297&titel=BC%20Only%20oran%20for%20vpn1.kamtx1/
		$('zoom').src = myURL + "?zoom&from=" + StartEpoch + '&to=' + EndEpoch + '&file=' + interID + '&width=' + Width +'&height=' + Height;	
	}

    myCropper.setParams();

};

Event.observe( 
           window, 
           'load', 
           function() { 
               if ( $('zoom') != null ){
                 myCropper = new Cropper.Img( 
                                'zoom', 
                                        { 
                                                minHeight: $('zoom').getDimensions().height,
                                                maxHeight: $('zoom').getDimensions().height,
                                                onEndCrop: changeRRDImage
                                        } 
                                ) 
                   }
                }
           );

