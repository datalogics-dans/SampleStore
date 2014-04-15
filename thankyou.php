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
// ACS5 Store Sample Code - Book Details Page;
//
// Description:  
// The HTML based store consist of 3 pages: Catalog, Details, Thank You .
//
// This script implements Thank You page
//
// Last updated: July 27, 2008
//
// Note:  This example requires Microsoft's MSXML 4.0 or higher to be installed.  Available at:
//        http://www.microsoft.com/downloads/details.aspx?familyid=3144b72b-b4f2-46da-b4b6-c5d7485f2b42&displaylang=en
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// include config and definitions
require_once('./config.php');


// Check if we have valid request
if (!$bookID=getParam("id"))
	ERROR_DIE("Missed book's ID argumnet");


////////////////////////////////////////////////////////////////////////////////////////////////////
//
// * * * Open & parse store xml file * * *
//

// Load XML file, create DOM, init xpath
require_once('./xml_init.php');


	$bookIDFullPath	= $xml_BookIDPath."[.='".$bookID."']";
	$booksList = $xpath->query($bookIDFullPath);

	if ($booksList->length == 0)
		ERROR_DIE("Specified book ".$bookID." not found<br />XPATH=".$bookIDFullPath);

	if ($booksList->length > 1)
		ERROR_DIE("Duplicated book ID. Found ".$booksList->length." entries for the ID=\"".$bookIDFullPath."\"");

	$book=$booksList->item(0)->parentNode;


////////////////////////////////////////////////////////////////////////////////////////////////////
//
// * * * Open & parse Configuration xml file * * *
//

	// Read Store Configuration
	$fp = fopen(CFG_XML_FILE,"r");    // open the xml file
	if (!$fp) ERROR_DIE("Error opening config xml file \"".CFG_XML_FILE."\"");

    	$xml = fread($fp, filesize(CFG_XML_FILE)); // read in the size of the file into the variable xml
    	fclose($fp);                           // close the stream
	if (!$xml) ERROR_DIE("Error reading xml file \"".CFG_XML_FILE."\"");
    

	$cfg = new DOMDocument("1.0","UTF-8");
	if (!$cfg)
  		ERROR_DIE("Can't create Configuration XML Document");

	if( !$cfg->LoadXML($xml) )
		ERROR_DIE ("Failed to parse configuration xml:<br />".$xml);


	// find configuration root element
	$cfgRoot = $cfg->documentElement;
	
	if (!$cfgRoot || ($cfgRoot->nodeName != CFG_DISTRIBUTOR_INFO))
		ERROR_DIE("Invalid configuration file \"".CONFIG_XML_FILE."\"");	

	PRINT_DEBUG("<b>CONFIGURATION INFO:</b><br />");

	$distributor = $cfgRoot->getElementsByTagName(CFG_DISTRIBUTOR); 
	$distributor = $distributor->length > 0 ? $distributor->item(0)->nodeValue : ERROR_DIE("Invalid store configuration: distributor info not found."); PRINT_DEBUG( "Distibutor: \"".$distributor."\"" );

	$ordersource = $cfgRoot->getElementsByTagName(CFG_ORDERSOURCE); 
	$ordersource = $ordersource->length > 0 ? $ordersource->item(0)->nodeValue : ERROR_DIE("Invalid store configuration: ordersource info not found."); PRINT_DEBUG( "Ordersource: \"".$ordersource."\"" );

	$sharedSecret = $cfgRoot->getElementsByTagName(CFG_SHARED_SECRET); 
	$sharedSecret = $sharedSecret->length > 0 ? $sharedSecret->item(0)->nodeValue : ERROR_DIE("Invalid store configuration: sharedSecret info not found."); PRINT_DEBUG( "SharedSecret: \"".$sharedSecret."\"" );
	$sharedSecret = base64_decode($sharedSecret);PRINT_DEBUG( "SharedSecret base64 decoded: \"".$sharedSecret."\"" );

	$linkURL = $cfgRoot->getElementsByTagName(CFG_LINK_URL); 
	$linkURL = $linkURL->length > 0 ? $linkURL->item(0)->nodeValue : ERROR_DIE("Invalid store configuration: linkURL info not found."); PRINT_DEBUG( "linkURL: \"".$linkURL."\"" );

	$operatorURL = $cfgRoot->getElementsByTagName(CFG_OPERATOR_URL); 
	$operatorURL = $operatorURL->length > 0 ? $operatorURL->item(0)->nodeValue : ERROR_DIE("Invalid store configuration: operatorURL info not found."); PRINT_DEBUG( "operatorURL: \"".$operatorURL."\"" );

	$action=getParam("submit") == TXT_BTN_PURCHASE  ? KEY_ACT_PURCHASE : KEY_ACT_LOAN; PRINT_DEBUG("Action: \"".$action."\"");
	$transaction=get_uniqueID(); PRINT_DEBUG("Transaction: \"".$transaction."\"");
	$bookResID=getParam("resid"); PRINT_DEBUG("Resource ID: \"".$bookResID."\"");
	$dateval=time(); PRINT_DEBUG("Dateval: \"".$dateval."\"");
	$gbauthdate=gmdate('r', $dateval); PRINT_DEBUG("Gbauthdate: \"".$gbauthdate."\"");

	

////////////////////////////////////////////////////////////////////////////////////////////////////
//
// Function: CreateOrderDetails
//

	PRINT_DEBUG( "<b>".$book->nodeName.":</b>" );

	$itemResource = "";
	$creator = "";
	$format = "";
	$description = "";
	$publisher = "";
	$itemThumbnailURL = "";

	if ($book->hasChildNodes())
		$itemsList=$book->childNodes;
	else
		return;

	foreach ($itemsList as $item)
	switch($item->nodeName)
	{
		case XML_ITEM_RESOURCE : $itemResource = $item->nodeValue; PRINT_DEBUG( "<b>".$item->nodeName.":</b> ".$item->nodeValue ); 
			break;

		case XML_ITEM_RESOURCE_ITEM : $itemResourceItem = $item->nodeValue; PRINT_DEBUG( "<b>".$item->nodeName.":</b> ".$item->nodeValue ); 
			break;

		case XML_ITEM_METADATA : $itemMetadata = $item->nodeValue; PRINT_DEBUG( "<b>".$item->nodeName.":</b> ".$item->nodeValue); 

			$titleElem=$item->getElementsByTagNameNS(DC_NAME_SPACE, XML_ITEM_TITLE); 
			$bookTitle = $titleElem->length > 0 ? $titleElem->item(0)->nodeValue : ""; PRINT_DEBUG( "Title: \"".$bookTitle."\"" );
			$bookTitle = encode_htmlentities($bookTitle);

			$creatorElem=$item->getElementsByTagNameNS(DC_NAME_SPACE, XML_ITEM_CREATOR); 
			$creator = $creatorElem->length > 0 ? $creatorElem->item(0)->nodeValue : ""; PRINT_DEBUG( "Creator :\"".$creator."\"" );
			$creator = encode_htmlentities($creator);

			$formatElem=$item->getElementsByTagNameNS(DC_NAME_SPACE, XML_ITEM_FORMAT); 
			$format = $formatElem->length > 0 ? $formatElem->item(0)->nodeValue : ""; PRINT_DEBUG( "Format :\"".$format."\"" );
			$format = encode_htmlentities($format);

			$descriptionElem=$item->getElementsByTagNameNS(DC_NAME_SPACE, XML_ITEM_DESCRIPTION); 
			$description = $descriptionElem->length > 0 ? $descriptionElem->item(0)->nodeValue : ""; PRINT_DEBUG( "Description :\"".$description."\"" );
			$description = encode_htmlentities($description);

			$publisherElem=$item->getElementsByTagNameNS(DC_NAME_SPACE, XML_ITEM_PUBLISHER); 
			$publisher = $publisherElem->length > 0 ? $publisherElem->item(0)->nodeValue : ""; PRINT_DEBUG( "Publisher :\"".$publisher."\"" );
			$publisher = encode_htmlentities($publisher);

			$itemThumbnailURLElem=$item->getElementsByTagName(XML_ITEM_THUMBNAIL_URL); 
			$itemThumbnailURL = $itemThumbnailURLElem->length > 0 ? $itemThumbnailURLElem->item(0)->nodeValue : ""; PRINT_DEBUG( "itemThumbnailURL :\"".$itemThumbnailURL."\"" );
			if (!$itemThumbnailURL)
				$itemThumbnailURL=URL_NO_IMAGE;

			break;

		case XML_ITEM_LICENSE_TOKEN: 
			$permissionsRoot=$item->getElementsByTagNameNS(XML_NS_ADOBE, XML_PERMISSIONS);
			if ($permissionsRoot->length > 0 )
				parsePermissions($permissionsRoot->item(0));
			break;
	}
	//read permission parameters from GET request

	// dont send store defulat permissions to the server;
	$dspPerm=""; 
	$dspPermEncoded=""; 
	$permDisplay="";

	$prnPerm="";
	$prnPermEncoded="";
	$permPrint="";
	
	$cpyPerm="";
	$cpyPermEncoded="";
	$permExcerpt="";
	

	// if use passhash is enabled
	$pashash_enabled=getParam(GET_PASSHASH_ON);
	if ($pashash_enabled)
	{
		// Transaction ID format with passhash enabled:
		// ACS5-{25 random characters}-{passhas encoded data}
		//
		// How to create passhash encoded data:
		// 1. With a randomly generated 30 byte transaction id ("ACS5="+25 random);
		// 2. compute SHA1 hash of transaction id and use first 16 bytes as an IV
	        // 3. compute the plaintext as the concatenation of:
		//	3a. The passhash message identifier - one byte of 1
		//	3b. 16 bytes of passhash
		//	3c. up to 46 bytes of account id.
		// 4. compute the ciphertext by encrypting the plaintext use AES-128CBC (P5 padding) with the previously generated IV and the distributors shared secret as the key
		// 5. Append to the transaction id a "-" seperator, and then append base64url encoded ciphertext

		// 1.
		$randomTransactionID = substr($transaction,5);
		// 2.
		// sha1 function second parameter comment:
		// "true" - produce binary format with a length of 20
		//  with "false"  returned value is a 40-character hexadecimal number. 
		$transactionSha1=sha1($randomTransactionID, true); 
		// echo("passhash enabled.<br />Transaction Id=".$transaction."<br />sha1=".$transactionSha1."<br />"); // debug
		//printf("<br /><b>IV: %s<br />",bin2hex(substr($transactionSha1, 0, 16)));
		//printf("<br /><b>sharedSecret: %s<br />",bin2hex($sharedSecret));


		// 3.
		// compute passhash
		$passhash=getPasshash($_COOKIE["AdobeDigitalEditionsPhpStoreUsername"], $_COOKIE["AdobeDigitalEditionsPhpStorePassword"]);
		// printf("Passhash:%s<br />",bin2hex($passhash)); //debug
		// 3a.
		$rndPasshash=chr(1);
		// 3b
		$rndPasshash=$rndPasshash.substr($passhash,0,16);
		//printf("<br /><b>Passhash: %s<br />",bin2hex(substr($passhash,0,16)));
		// 3c
		$accountID=$_COOKIE["AdobeDigitalEditionsPhpStoreAccountID"];
		$plaintext=$rndPasshash.$accountID;
		// 4
		//printf("<br /><b>PLain text before padding: %s<br />",bin2hex($plaintext));
		$ciphertext=AES_128_CBC_encode($plaintext, substr($sharedSecret,0,16), substr($transactionSha1, 0, 16));
	 	// printf("<br /><b>Cipher text: %s<br />",bin2hex($ciphertext)); //debug
		// 5
		$transaction = $transaction."-".bin2hex($ciphertext);
		// printf("<br /><b>Transaction ID: %s<br />",bin2hex($transaction)); //debug
		//printf("<br /><b>Transaction ID: %s<br />",$transaction);
		

	}

	// if override DISPLAY permissions enabled
	if (getParam(GET_DISPLAY_ON))
	{
		if (GET_DISPLAY_REL==getParam(GET_DISPLAY_RADIO))
		{ // $lrt#tttt$  Where 'tttt' is the number of seconds of ownership. The loan will expire 'tttt' seconds after the license is created. This will be converted into an absolute time when the License Token is created.
			$permDisplay= TXT_EXPIRES.": ".getParam(GET_DISPLAY_REL_VAL)." seconds after purchase";
			switch ($action){
				case KEY_ACT_LOAN: $dspPermEncoded="$".urlencode(DISPL_PERM_LOAN_REL."#".getParam(GET_DISPLAY_REL_VAL))."$";
						break;
				case KEY_ACT_PURCHASE: $dspPermEncoded="$".urlencode(DISPL_PERM_PURCH_REL."#".getParam(GET_DISPLAY_REL_VAL))."$";
						break;
				default:
					ERROR_DIE("Unknown action type");
			}
		}
		else
		{ // $lat#tttt$ where 'tttt' (Expiration date) is the number of seconds since midnight, January 1, 1970 UTC. The Loan will expire at this Exact UTC time.
			$absTime=strtotime(getParam(GET_DISPLAY_ABS_VAL));
			$permDisplay= TXT_EXPIRES.": ".date("r",$absTime);
			switch ($action){
				case KEY_ACT_LOAN: $dspPermEncoded="$".urlencode(DISPL_PERM_LOAN_ABS."#".$absTime)."$";
						break;
                                case KEY_ACT_PURCHASE:  $dspPermEncoded="$".urlencode(DISPL_PERM_PURCH_ABS."#".$absTime)."$";
						break;
				default:
					ERROR_DIE("Unknown action type");
			}
		}		 		
	}
	else
		$permDisplay = TXT_PERM_NONE;  // No further persmissions;

	// if override COPY permissions enabled
	if (getParam(GET_COPY_ON))
	{ // cpy#nnn#tttt$ is the Copy permission, Copy 'nnn' selections to clipboard every 'tttt' seconds, where 'nnn' is the event count (number of clipboard operations) and 'tttt' is the number of seconds for the interval.
		$initValue= getParam(GET_COPY_INIT) ? getParam(GET_COPY_INIT) : 0; // 0 === DENIED;
		$incrStr= getParam(GET_COPY_INC) ? "#".getParam(GET_COPY_INC) : "#".DISPL_PERM_EVER; // "" === no replenish, initial value only;
		$cpyPerm = "$".COPY_PERM."#".$initValue.$incrStr."$";
		$cpyPermEncoded = "$".urlencode(COPY_PERM."#".$initValue.$incrStr)."$";
	}


	// parse prepared actual COPY permissions and see what we are going to send to the server
	// it could be prepared above or in functions.php, if default permissions are used
	$cpyPermArr = explode("$",$cpyPerm);

	if (count($cpyPermArr)>1)
	{ // we have some permissions
		$cpyPermArr = explode("#",$cpyPermArr[1]);

		if ($cpyPermArr[1]==0)
			$permExcerpt = TXT_PERM_DENIED;
		else
		{
			if (count($cpyPermArr)<3 OR $cpyPermArr[2]==DISPL_PERM_EVER)
				$permExcerpt= $cpyPermArr[1]." pages ever";
			else
				$permExcerpt= $cpyPermArr[1]." pages every  ".$cpyPermArr[2]." seconds";
		}
	}
	else // no permissions - no futher restritions
		$permExcerpt = TXT_PERM_NONE; // this line is commented, show empty line if we aren't enforcing this permission;

		
	// if override PRINT permissions enabled
	if (getParam(GET_PRINT_ON))
	{ // $prn#nnn#tttt$ Print 'nnn' pages every 'tttt' seconds where 'nnn' is the event count (number of pages permitted to print) and 'tttt' is the number of seconds for the interval.
		$initValue= getParam(GET_PRINT_INIT) ? getParam(GET_PRINT_INIT) : 0; // 0 === DENIED;
		$incrStr= getParam(GET_PRINT_INC) ? "#".getParam(GET_PRINT_INC) : "#".DISPL_PERM_EVER; // "" === no replenish, initial value only;
		$prnPerm = "$".PRINT_PERM."#".$initValue.$incrStr."$";
		$prnPermEncoded = "$".urlencode(PRINT_PERM."#".$initValue.$incrStr)."$";
	}

	// parse prepared actual PRINT permissions and see what we are going to send to the server
	// it could be prepared above or in functions.php, if default permissions are used
	$prnPermArr = explode("$",$prnPerm);

	if (count($prnPermArr)>1)
	{ // we have some permissions
		$prnPermArr = explode("#",$prnPermArr[1]);

		if ($prnPermArr[1]==0)
			$permPrint = TXT_PERM_DENIED;
		else
		{
			if (count($cpyPermArr)<3 OR $cpyPermArr[2]==DISPL_PERM_EVER)
				$permPrint= $prnPermArr[1]." pages ever";
			else
				$permPrint= $prnPermArr[1]." pages every  ".$prnPermArr[2]." seconds";
		}
	}
	else // no permissions - no futher restritions
		$permPrint = TXT_PERM_NONE;   // this line is commented, show empty line if we aren't enforcing this permission;
	
	
	$rights= $dspPermEncoded.$cpyPermEncoded.$prnPermEncoded;
			 		
	// create main table
	/*
	$bookTable="<table width=\"100%\">
	  <tr>
	    <td valign=\"top\" width=\"100px\">
	            <img src=\"$itemThumbnailURL\" alt=\"$bookTitle\"  width=\"100\" height=\"150\" border=\"0\" hspace=\"10\" />
	    </td>
	    <td width=\"*\">
		<div class = \"basicInformation\">
			<div class = \"bookTitle\">$bookTitle</div>
	    	    	<div class = \"bookAuthor\">By: $creator</div>
			<div class = \"bookDescription\">$description</div>
	        </div>
		<div class=\"defaultPermissionsSection\">
		<div class = \"defaultPermissionsTitle\">Restrict Permissions Further:</div>
			<div class = \"displayPermissions\">
				<div class = \"displayPermissionsHeader\">".TXT_PERM_DISPLAY.":</div>
				<div class = \"displayPermissionsValue\">$permDisplay</div>
			</div>
			<div class = \"copyPermissions\">
				<div class = \"copyPermissionsHeader\">".TXT_PERM_EXCERPT.":</div>
				<div class = \"copyPermissionsValue\">$permExcerpt</div>
			</div>
			<div class = \"printPermissions\">
				<div class = \"printPermissionsHeader\">".TXT_PERM_PRINT.":</div>
				<div class = \"printPermissionsValue\">$permPrint</div>
			</div>
		</div>
	

        
	    </td>
	  </tr>
	</table>";
*/
////////////////////////////////////////////////////////////////////////////////////////////////////
// 
// Create downalod URL
//

	// rights is optional paramter. Omit it if there are no permissions to send (no further restriction);
	$rights= $rights ? "&rights=".$rights : "";

	$bookDownlodURL = 
				"action=".urlencode($action).
				"&ordersource=".urlencode($ordersource).
				"&orderid=".urlencode($transaction).
				"&resid=".urlencode($bookID).
				$rights.
//				"&gbauthdate=".urlencode($gbauthdate).
				"&dateval=".urlencode($dateval).
				"&gblver=4";


	PRINT_DEBUG("before signature bookDowanlodURL: \"".$bookDownlodURL."\"");	

	// Digitaly sign the request
	$bookDownlodURL = $linkURL."?".$bookDownlodURL."&auth=".hash_hmac("sha1", $bookDownlodURL, $sharedSecret ); 

	PRINT_DEBUG("whole bookDowanlodURL: \"".$bookDownlodURL."\"");

////////////////////////////////////////////////////////////////////////////////////////////////////
// 
// HTML code below is Book details page template and it use the variables, calculated above
//

// Include HTML Header template
//require_once('./header.php');

?>


<!--div class="orderHeader">
You have Ordered:
</div-->

<!-- Order details table -->
<?php //echo $bookTable; ?>
<!-- /Order details table -->

<!-- div class = "downloadSection">
	      Click <a title="Download the book" target="_blank" href="<?php //echo $bookDownlodURL ?>">download</a> to start reading your file.
</div -->

<?php 
// Include HTML Footer template
//require_once('./footer.php'); 
?>

</body>
</html>
