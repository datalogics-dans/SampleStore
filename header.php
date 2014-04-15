<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ADOBE SYSTEMS INCORPORATED
//  Copyright 2008 Adobe Systems Incorporated
//  All Rights Reserved.
// 
// NOTICE:  Adobe permits you to use, modify, and distribute this file in accordance with the 
// terms of the Adobe license agreement accompanying it.  If you have received this file from a 
// source other than Adobe, then your use, modification, or distribution of it requires the prior 
// written permission of Adobe.
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ACS5 Store Sample Code - Header HTML Template;
//
// Description:  
// The HTML based store consist of 3 pages: Catalog, Details, Thank You .
//
// This script implements Header Template for all pages
//
// Last updated: July 27, 2008
//
// Note:  This example requires Microsoft's MSXML 4.0 or higher to be installed.  Available at:
//        http://www.microsoft.com/downloads/details.aspx?familyid=3144b72b-b4f2-46da-b4b6-c5d7485f2b42&displaylang=en
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////
//
// * * * Header * * *
//

require_once('./functions.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- no cache headers -->
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="-1" />
<meta http-equiv="Cache-Control" content="no-cache" />
<!-- end no cache headers -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />



<title>DevACS-Win Sample Store <?php if ($bookTitle)echo "- ".$bookTitle; ?> </title>

<link href="css/book.css" rel="stylesheet" type="text/css" />
<link href="css/mediaqueries.css" rel="stylesheet" type="text/css" />
<script src="jquery-1.11.0.min.js"></script>



<!-- Add fancyBox -->
<link rel="stylesheet" href="fancybox/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
<script type="text/javascript" src="fancybox/source/jquery.fancybox.pack.js?v=2.1.5"></script>

<!-- Optionally add helpers - button, thumbnail and/or media -->
<link rel="stylesheet" href="fancybox/source/helpers/jquery.fancybox-buttons.css?v=1.0.5" type="text/css" media="screen" />
<script type="text/javascript" src="fancybox/source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
<script type="text/javascript" src="fancybox/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>

<link rel="stylesheet" href="fancybox/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" type="text/css" media="screen" />
<script type="text/javascript" src="fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>


</head>

<script type="text/javascript">
<!--

function setCookie(c_name,value,expiredays)
{
  var exdate=new Date()
  exdate.setDate(exdate.getDate()+expiredays)
  document.cookie=c_name+ "=" +escape(value)+
  ((expiredays==null) ? "" : "; expires="+exdate.toGMTString())
}

function getCookie(c_name)
{
  if (document.cookie.length>0)
  { 
    c_start=document.cookie.indexOf(c_name + "=")
    if (c_start!=-1)
    { 
    c_start=c_start + c_name.length+1 
    c_end=document.cookie.indexOf(";",c_start)
      if (c_end==-1) c_end=document.cookie.length
       return unescape(document.cookie.substring(c_start,c_end))
    } 
  }
  return ""
}


function makeTxt(id,txt){
	var obj = document.getElementById(id);
	if (obj) {
		obj.firstChild?obj.firstChild.data=txt:obj.appendChild(document.createTextNode(txt))
	}
}

function setEnabled(id,isDisabled){
	var obj = document.getElementById(id);
	if (obj) {
		obj.disabled=isDisabled;
	}
}

function checkLogged()
{
var loginID=getCookie("AdobeDigitalEditionsPhpStoreAccountID");
var loginName=getCookie("AdobeDigitalEditionsPhpStoreUsername")
var loginPass=getCookie("AdobeDigitalEditionsPhpStorePassword")

	if (loginID && loginName && loginPass )
	{
		makeTxt("loggedStatusLabel","(logged as "+loginID+")");
		makeTxt("usePasshashIDLabel",loginID);
		setEnabled("<?php echo(GET_PASSHASH_ON) ?>",false);
		
	} else {
		makeTxt("loggedStatusLabel","(not logged)");
		makeTxt("usePasshashIDLabel","(not logged)")
		setEnabled("<?php echo(GET_PASSHASH_ON) ?>",true);
	}
}


$(function() {
    $('.book_div').hover(
    function() {
		$(this).find('img:nth(0)').css( "height", "170px" );
		$(this).find('img:nth(0)').css( "width", "120px" );
		$(this).find('img:nth(0)').css( "box-shadow", "2px 5px 20px #035" );
    }, function() {
		$(this).find('img:nth(0)').css( "height", "150px" );
		$(this).find('img:nth(0)').css( "width", "100px" );
		$(this).find('img:nth(0)').css( "box-shadow", "none" );
    });
});


$(document).ready(function() {
	$(".various").fancybox({
		padding		: 0,
		maxWidth	: 500,
		maxHeight	: 500,
		fitToView	: true,
		width		: 500,
		height		: 500,
		autoSize	: false,
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none',
		scrolling	: 'auto',
		dataType : 'html',
		headers  : { 'X-fancyBox': true }
	});
});

function inputFocus(i){
    if(i.value==i.defaultValue){ i.value=""; i.style.color="#000"; }
}
function inputBlur(i){
    if(i.value==""){ i.value=i.defaultValue; i.style.color="#888"; }
}

//-->
</script>

<body onload="checkLogged()">

<div id="header_div">
	<img src="images/book_icon.jpg" alt="Datalogics book Store" border="0" width="44" height="40" class="left"/>
    <div class="left">
	Welcome to Datalogics Book Club
	</div>
    <div class="headerButtonsSection">
		<!-- Search -->
		<div class="simpleSearch">
		<form method="get" action="catalog.php" id="searchForm">
		<input title="Type your search" id="ed_simple_search" type="text" name="<?php echo SEARCH_SIMPLE_TEXT_KEY ?>" style="color:#888;" value="Search" size="25" onfocus="inputFocus(this)" onblur="inputBlur(this)"/>
		<input title="Start the search" type="submit" value="Go!" id="btnSearchSubmit" />
		</form>
		</div>
		<!-- /Search -->
	</div>
</div>

<div class="clear"></div>

<!--
-- old table --
<table border="0" cellpadding="0" cellspacing="0" ><tr><td>

	<div class="adobeImage">
	<a href="catalog.php">
	<img src="images/adobe-hq.gif" alt="Adobe" border="0" width="105" height="128">
	</a>
	</div>
</td><td>
	<div class="headerTitle">
	Sample PHP Bookstore (ACS5)
	</div>
    -->

	

		<!-- Login --
		<div class="loginButtonHeader">
			<form method="get" action="login.php" id="headerLogin">
				<input title="Login" type="submit" value="Login page" id="btnLoginSubmit" onClick="setCookie('href', location.href);" />
			</form>
		</div>
		<div class="loggedInfoHeader" id="loggedStatusLabel"></div>

		-- /login --
	<div>
</td></tr></table>
-- end of old table --
-->

