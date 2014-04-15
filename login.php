<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ADOBE SYSTEMS INCORPORATED
//  Copyright 2010 Adobe Systems Incorporated
//  All Rights Reserved.
// 
// NOTICE:  Adobe permits you to use, modify, and distribute this file in accordance with the 
// terms of the Adobe license agreement accompanying it.  If you have received this file from a 
// source other than Adobe, then your use, modification, or distribution of it requires the prior 
// written permission of Adobe.
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ACS5 Store Sample Code - Login Page;
//
// Description:  
// The HTML based store consist of 3 pages: Catalog, Details, Thank You .
//
// This script implements Login page. 
// Login page that asks for accountID, username & password and stores them as a plaintext cookie
//
// Last updated: July 19, 2010
//
// Note:  This example requires Microsoft's MSXML 4.0 or higher to be installed.  Available at:
//        http://www.microsoft.com/downloads/details.aspx?familyid=3144b72b-b4f2-46da-b4b6-c5d7485f2b42&displaylang=en
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////
// 
// HTML code below is Book details page template and it use the variables, calculated above
//

// Include HTML Header template
require_once('./header.php');

?>


<script type="text/javascript">
<!-- 

function setLoginCookies(form){
	setCookie("AdobeDigitalEditionsPhpStoreAccountID",form.loginIdEditor.value,999)
	setCookie("AdobeDigitalEditionsPhpStoreUsername",form.loginNameEditor.value,999)
	setCookie("AdobeDigitalEditionsPhpStorePassword",form.loginPassEditor.value,999)
	checkLogged()
	window.location = getCookie("href")
}

function logOut(form){
	setCookie("AdobeDigitalEditionsPhpStoreAccountID",'',0)
	form.loginIdEditor.value=''
	setCookie("AdobeDigitalEditionsPhpStoreUsername",'',0)
	form.loginNameEditor.value=''
	setCookie("AdobeDigitalEditionsPhpStorePassword",'',0)
	form.loginPassEditor.value=''
	checkLogged()
	window.location = getCookie("href")
}

-->
</script>

<div class="loginSection">
	<form method="none" action="login.php" id="loginForm"> 

	<div class="loginSectionHeader">
		Please enter your credentials:
	</div>

	<div class="loginIdSection">
		<div class="loginIdHeader">
			Account&nbsp;ID:
		</div>
		<div class="loginIdEditor">
			<input type="text"" name="loginIdEditor" value="<?php echo $_COOKIE["AdobeDigitalEditionsPhpStoreAccountID"]; ?>" size="41" />
		</div>
	</div>

	<div class="loginNameSection">
		<div class="loginNameHeader">
			User&nbsp;name:
		</div>
		<div class="loginNameEditor">
			<input type="text"" name="loginNameEditor" value="<?php echo $_COOKIE["AdobeDigitalEditionsPhpStoreUsername"]; ?>" size="41" />
		</div>
	</div>

	<div class="loginPassSection">
		<div class="loginPassHeader">
			Password:
		</div>
		<div class="loginPassEditor">
			<input type="password"" name="loginPassEditor" value="<?php echo $_COOKIE["AdobeDigitalEditionsPhpStorePassword"]; ?>" size="41" />
		</div>
	</div>

	<div class="loginButtonsSection">
		<div class="loginResetButton">
			<input title="Reset" type="reset" value="Reset" id="btnLoginFormReset"/>
		</div>
		<div class="loginButton">
			<input title="Login" type="button" value="Login" id="btnLoginFormSubmit" onClick="setLoginCookies(form)" />
		</div>
		<div class="logoutButton">
			<input title="Logout" type="button" value="Logout" id="btnLogout" onClick="logOut(form)" />
		</div>
	</div>


	</form>
</div>

<?php 
// Include HTML Footer template
require_once('./footer.php'); 
?>

</body>
</html>
