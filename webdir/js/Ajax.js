function LoadPage(page,usediv) {
         // Set up request varible
         try {xmlhttp = window.XMLHttpRequest?new XMLHttpRequest(): new ActiveXObject("Microsoft.XMLHTTP");}  catch (e) { alert("Error: Could not load page.");}
         //Show page is loading
         document.getElementById(usediv).innerHTML = 'Loading...';
         //send data
         xmlhttp.onreadystatechange = function(){
                 //Check page is completed and there were no problems.
                 if ((xmlhttp.readyState == 4) && (xmlhttp.status == 200)) {
                        //Write data returned to page
                        document.getElementById(usediv).innerHTML = xmlhttp.responseText;						
						tooltip();
						screenshotPreview();
						filterFunction();
						modalWindow();
						pageNav();
						standardistaTableSorting.makeSortable(document.getElementById('sortDataTable'));
                 }
         }
		 		 
         xmlhttp.open("GET", page);
         xmlhttp.send(null);
		 
}

function LoadPOST(page,usediv, param) {
         // Set up request varible
         try {xmlhttp = window.XMLHttpRequest?new XMLHttpRequest(): new ActiveXObject("Microsoft.XMLHTTP");}  catch (e) { alert("Error: Could not load page.");}
         //Show page is loading
         document.getElementById(usediv).innerHTML = 'Loading...';
         //send data
		 xmlhttp.onreadystatechange = function(){
                 //Check page is completed and there were no problems.
                 if ((xmlhttp.readyState == 4) && (xmlhttp.status == 200)) {
                        //Write data returned to page
                        document.getElementById(usediv).innerHTML = xmlhttp.responseText;
                 }
         }
         xmlhttp.open("POST", page);
		 xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      	 xmlhttp.setRequestHeader("Content-length", param.length);
		 xmlhttp.setRequestHeader("Connection", "close");
         xmlhttp.send(param);
}



/********************************************************************Modal*****************************/

/* Simple AJAX Code-Kit (SACK) v1.6.1 */
/* ©2005 Gregory Wild-Smith */
/* www.twilightuniverse.com */
/* Software licenced under a modified X11 licence,
   see documentation or authors website for more details */

function sack(file) {
	this.xmlhttp = null;

	this.resetData = function() {
		this.method = "POST";
  		this.queryStringSeparator = "?";
		this.argumentSeparator = "&";
		this.URLString = "";
		this.encodeURIString = true;
  		this.execute = false;
  		this.element = null;
		this.elementObj = null;
		this.requestFile = file;
		this.vars = new Object();
		this.responseStatus = new Array(2);
  	};

	this.resetFunctions = function() {
  		this.onLoading = function() { };
  		this.onLoaded = function() { };
  		this.onInteractive = function() { };
  		this.onCompletion = function() { };
  		this.onError = function() { };
		this.onFail = function() { };
	};

	this.reset = function() {
		this.resetFunctions();
		this.resetData();
	};

	this.createAJAX = function() {
		try {
			this.xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e1) {
			try {
				this.xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e2) {
				this.xmlhttp = null;
			}
		}

		if (! this.xmlhttp) {
			if (typeof XMLHttpRequest != "undefined") {
				this.xmlhttp = new XMLHttpRequest();
			} else {
				this.failed = true;
			}
		}
	};

	this.setVar = function(name, value){
		this.vars[name] = Array(value, false);
	};

	this.encVar = function(name, value, returnvars) {
		if (true == returnvars) {
			return Array(encodeURIComponent(name), encodeURIComponent(value));
		} else {
			this.vars[encodeURIComponent(name)] = Array(encodeURIComponent(value), true);
		}
	}

	this.processURLString = function(string, encode) {
		encoded = encodeURIComponent(this.argumentSeparator);
		regexp = new RegExp(this.argumentSeparator + "|" + encoded);
		varArray = string.split(regexp);
		for (i = 0; i < varArray.length; i++){
			urlVars = varArray[i].split("=");
			if (true == encode){
				this.encVar(urlVars[0], urlVars[1]);
			} else {
				this.setVar(urlVars[0], urlVars[1]);
			}
		}
	}

	this.createURLString = function(urlstring) {
		if (this.encodeURIString && this.URLString.length) {
			this.processURLString(this.URLString, true);
		}

		if (urlstring) {
			if (this.URLString.length) {
				this.URLString += this.argumentSeparator + urlstring;
			} else {
				this.URLString = urlstring;
			}
		}

		// prevents caching of URLString
		this.setVar("rndval", new Date().getTime());

		urlstringtemp = new Array();
		for (key in this.vars) {
			if (false == this.vars[key][1] && true == this.encodeURIString) {
				encoded = this.encVar(key, this.vars[key][0], true);
				delete this.vars[key];
				this.vars[encoded[0]] = Array(encoded[1], true);
				key = encoded[0];
			}

			urlstringtemp[urlstringtemp.length] = key + "=" + this.vars[key][0];
		}
		if (urlstring){
			this.URLString += this.argumentSeparator + urlstringtemp.join(this.argumentSeparator);
		} else {
			this.URLString += urlstringtemp.join(this.argumentSeparator);
		}
	}

	this.runResponse = function() {
		eval(this.response);
	}

	this.runAJAX = function(urlstring) {
		if (this.failed) {
			this.onFail();
		} else {
			this.createURLString(urlstring);
			if (this.element) {
				this.elementObj = document.getElementById(this.element);
			}
			if (this.xmlhttp) {
				var self = this;
				if (this.method == "GET") {
					totalurlstring = this.requestFile + this.queryStringSeparator + this.URLString;
					this.xmlhttp.open(this.method, totalurlstring, true);
				} else {
					this.xmlhttp.open(this.method, this.requestFile, true);
					try {
						this.xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
					} catch (e) { }
				}

				this.xmlhttp.onreadystatechange = function() {
					switch (self.xmlhttp.readyState) {
						case 1:
							self.onLoading();
							break;
						case 2:
							self.onLoaded();
							break;

						case 3:
							self.onInteractive();
							break;
						case 4:
							self.response = self.xmlhttp.responseText;
							self.responseXML = self.xmlhttp.responseXML;
							self.responseStatus[0] = self.xmlhttp.status;
							self.responseStatus[1] = self.xmlhttp.statusText;

							if (self.execute) {
								self.runResponse();
							}

							if (self.elementObj) {
								elemNodeName = self.elementObj.nodeName;
								elemNodeName.toLowerCase();
								if (elemNodeName == "input"
								|| elemNodeName == "select"
								|| elemNodeName == "option"
								|| elemNodeName == "textarea") {
									self.elementObj.value = self.response;
								} else {
									self.elementObj.innerHTML = self.response;
								}
							}
							if (self.responseStatus[0] == "200") {
								self.onCompletion();
							} else {
								self.onError();
							}

							self.URLString = "";
							break;
					}
				};

				this.xmlhttp.send(this.URLString);
			}
		}
	};

	this.reset();
	this.createAJAX();
}

/******************TRYJAX***************************/

this.modalWindow = (function() {	

	//select all the a tag with name equal to modal
	$('a[name=modal]').click(function(e) {
		//Cancel the link behavior
		e.preventDefault();
		
		//Get the A tag
		var id = $(this).attr('href');
	
		//Get the screen height and width
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();
	
		//Set heigth and width to mask to fill up the whole screen
		$('#mask').css({'width':maskWidth,'height':maskHeight});
		
		//transition effect		
		$('#mask').fadeIn(500);	
		$('#mask').fadeTo('slow',0.8);	
	
		//Get the window height and width
		var winH = $(window).height();
		var winW = $(window).width();
              
		//Set the popup window to center
		$(id).css('top',  winH/2-$(id).height()/2);
		$(id).css('left', winW/2-$(id).width()/2);
	
		//transition effect
		$(id).fadeIn(1000); 
	
	});
	
	//if close button is clicked
	$('.window .close').click(function (e) {
		//Cancel the link behavior
		e.preventDefault();
		
		$('#mask').hide();
		$('.window').hide();
	});
	
});


/************************Tooltip**************************/

this.tooltip = function(){	
	/* CONFIG */		
		txOffset = 0;
		tyOffset = 10;
		var top;
		var winH = $(window).height();
		var winW = $(window).width();	
		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result		
	/* END CONFIG */		
	$("a.tooltip").hover(function(e){										  
		this.t = this.title;
		this.title = "";									  
		$("body").append("<p id='tooltip'>"+ this.t +"</p>");
		((e.screenY - txOffset) <= 150) ? top = e.pageY+10 : top = e.pageY - txOffset;
		$("#tooltip")
			.css("top",(e.pageY - txOffset) + "px")
			.css("left",(e.pageX + tyOffset) + "px")
			.fadeIn("fast");		
    },
	function(){
		this.title = this.t;		
		$("#tooltip").remove();
    });	
	$("a.tooltip").mousemove(function(e){
		((e.screenY - txOffset) <= 150) ? top = e.pageY+10 : top = e.pageY - txOffset;
		$("#tooltip")
			.css("top",(top) + "px")
			.css("left",(e.pageX + tyOffset) + "px");
	});
	$("a.tooltip").click(function(e){
		this.title = this.t;		
		$("#tooltip").remove();
	});	
};

this.screenshotPreview = function(){	
	/* CONFIG */
	
	//Changed from 350 for the bigger graphs
	xOffset = 400;
	yOffset = 30;
	var top;
	var winH = $(window).height();
	var winW = $(window).width();
		
		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result
		
	/* END CONFIG */
	$("a.screenshot").hover(function(e){
		this.t = this.title;
		this.title = "";	
		var c = (this.t != "") ? "<br/>" + this.t : "";
		$("body").append("<p id='screenshot'><img src='"+ this.rel +"' alt='url preview' />"+ c +"</p>");	
		((e.screenY - xOffset) <= 150) ? top = e.pageY+10 : top = e.pageY - xOffset;
		$("#screenshot")
			.css("top",(top) + "px")
			.css("left",(e.pageX) + "px")
			.fadeIn("fast");						
    },
	function(){
		this.title = this.t;	
		$("#screenshot").remove();
    });	
	$("a.screenshot").mousemove(function(e){
		((e.screenY - xOffset) <= 150) ? top = e.pageY+10 : top = e.pageY - xOffset;
		$("#screenshot")
			.css("top",(top) + "px")
			.css("left",(e.pageX) + "px");
	});		
	
	/***********TR TRIAL******************/
	$("tr.screenshot").hover(function(e){
		this.t = this.title;
		this.title = "";	
		var c = (this.t != "") ? "<br/>" + this.t : "";
		$("body").append("<p id='screenshot'><img src='"+ this.id +"' alt='url preview' />"+ c +"</p>");
		$("#screenshot")
			.css("top", (e.pageY + yOffset)+ "px")
			.css("left", (0)+"px")
			.fadeIn("fast");						
    },
	
	function(){
		this.title = this.t;	
		$("#screenshot").remove();
    });
};

/******************************************Filtering****************************/

this.filterFunction = function() {
	
	$('tbody tr').hover(function(){
	  $(this).find('th').addClass('hovered');
	}, function(){
	  $(this).find('th').removeClass('hovered');
	});
	
		//default each row to visible
		$('tbody tr').addClass('visible');
	
		//overrides CSS display:none property
		//so only users w/ JS will see the
		//filter box
		$('#search').show();
	
		$('#filter').keyup(function(event) {
		//if esc is pressed or nothing is entered
    	if (event.keyCode == 27 || $(this).val() == '') {
			//if esc is pressed we want to clear the value of search box
			$(this).val('');
			
			//we want each row to be visible because if nothing
			//is entered then all rows are matched.
		$('tbody tr').removeClass('visible').show().addClass('visible');
    	}

		//if there is text, lets filter
		else {
    		filter('tbody tr', $(this).val());
    	}
		
		//NEED TO FIX THE ROW COLORS
	});
	
};


//used to apply alternating row styles
function zebraRows(selector, className)
{
	$(selector).removeClass(className)
							.addClass(className);
}

//filter results based on query
function filter(selector, query) {
	query	=	$.trim(query); //trim white space
  // query = query.replace(/ /gi, '|'); //add OR for regex
  $(selector).each(function() {
	 	if($(this).text().search(new RegExp(query, "i")) < 0)
		{
			$(this).hide().removeClass('visible');
		}
		else {
			$(this).show().addClass('visible');
		}
  });
}

/********************************************************************Page Navigation*********************************/

this.pageNav = function(){
	
	//how much items per page to show
	var show_per_page = 5; 
	//getting the amount of elements inside content div
	var number_of_items = $('#content').children().size();
	//calculate the number of pages we are going to have
	var number_of_pages = Math.ceil(number_of_items/show_per_page);
	
	//set the value of our hidden input fields
	$('#current_page').val(0);
	$('#show_per_page').val(show_per_page);
	
	//now when we got all we need for the navigation let's make it '
	
	/* 
	what are we going to have in the navigation?
		- link to previous page
		- links to specific pages
		- link to next page
	*/
	var navigation_html = '<a class="previous_link" href="javascript:previous();">Prev</a>';
	var current_link = 0;
	while(number_of_pages > current_link){
		navigation_html += '<a class="page_link" href="javascript:go_to_page(' + current_link +')" longdesc="' + current_link +'">'+ (current_link + 1) +'</a>';
		current_link++;
	}
	navigation_html += '<a class="next_link" href="javascript:next();">Next</a>';
	
	$('#page_navigation').html(navigation_html);
	
	//add active_page class to the first page link
	$('#page_navigation .page_link:first').addClass('active_page');
	
	//hide all the elements inside content div
	$('#content').children().css('display', 'none');
	
	//and show the first n (show_per_page) elements
	$('#content').children().slice(0, show_per_page).css('display', 'block');
	
};

function previous(){
	
	new_page = parseInt($('#current_page').val()) - 1;
	//if there is an item before the current active link run the function
	if($('.active_page').prev('.page_link').length==true){
		go_to_page(new_page);
	}
	
}

function next(){
	new_page = parseInt($('#current_page').val()) + 1;
	//if there is an item after the current active link run the function
	if($('.active_page').next('.page_link').length==true){
		go_to_page(new_page);
	}
	
}
function go_to_page(page_num){
	//get the number of items shown per page
	var show_per_page = parseInt($('#show_per_page').val());
	
	//get the element number where to start the slice from
	start_from = page_num * show_per_page;
	
	//get the element number where to end the slice
	end_on = start_from + show_per_page;
	
	//hide all children elements of content div, get specific items and show them
	$('#content').children().css('display', 'none').slice(start_from, end_on).css('display', 'block');
	
	/*get the page link that has longdesc attribute of the current page and add active_page class to it
	and remove that class from previously active page link*/
	$('.page_link[longdesc=' + page_num +']').addClass('active_page').siblings('.active_page').removeClass('active_page');
	
	//update the current page input field
	$('#current_page').val(page_num);
}

// starting the script on page load
$(document).ready(function(){
	tooltip();
	screenshotPreview();
	filterFunction();
	modalWindow();
	pageNav();
});
