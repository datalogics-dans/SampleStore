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
// This script implements Details page
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
// Function: CreateItemDetails
//


	PRINT_DEBUG( "<b>".$book->nodeName."</b><br />" );

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

		case XML_ITEM_METADATA : 
			
			PRINT_DEBUG( "<b>".$item->nodeName.":</b> ".$item->nodeValue); 

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
	// if tag <permissions /> is missed we assume that all are allowed
	if (!$permDisplay)
		$permDisplay = '<div class="copyPermissionsValueLine"> '. TXT_PERM_UNLIM . ".</div>";
	if (!$permExcerpt)
		$permExcerpt = '<div class="copyPermissionsValueLine"> '. TXT_PERM_UNLIM . ".</div>";
	if (!$permPrint)
		$permPrint = '<div class="copyPermissionsValueLine"> '. TXT_PERM_UNLIM . ".</div>";

	PRINT_DEBUG(TXT_PERM_DISPLAY.": ".$permDisplay."<br />");
	PRINT_DEBUG(TXT_PERM_EXCERPT.": ".$permExcerpt."<br />");
	PRINT_DEBUG(TXT_PERM_PRINT.": ".$permPrint."<br />");

	// create main table
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
		<div class = \"defaultPermissionsTitle\">Current Permissions:</div>
			<table>
			<tr>
			<div class = \"displayPermissions\">
				<td valign=\"top\" width=\"100\">
				<div class = \"displayPermissionsHeader\">".TXT_PERM_DISPLAY.":</div>
				</td>
				<td>
				<div class = \"displayPermissionsValue\">
					$permDisplay
				</div>
				</td>
			</div>
			</tr>
			<tr>
			<div class = \"copyPermissions\">
				<td valign=\"top\" width=\"100\">
				<div class = \"copyPermissionsHeader\">".TXT_PERM_EXCERPT.":</div>
				</td>
				<td>
				<div class = \"copyPermissionsValue\">
					$permExcerpt
				</div>
				</td>
			</div>
			</tr>
			<tr>
			<div class = \"printPermissions\">
				<td valign=\"top\" width=\"100\">
				<div class = \"printPermissionsHeader\">".TXT_PERM_PRINT.":</div>
				</td>
				<td>
				<div class = \"printPermissionsValue\">
					$permPrint
				</div>
				</td>
			</div>
			</tr>
			</table>
		</div>
	
		<form method=\"get\" action=\"./thankyou.php\">
		<div class=\"newPermissionsSection\">
			<div class = \"newPermissionsSectionHeader\">Restrict Permissions Further<i>(Optional)</i>:</div>
			<div class = \"newDisplayPermissions\">
				<div class = \"newDisplayPermissionsTitle\">
					<input type=\"checkbox\" name=\"".GET_DISPLAY_ON."\" value=\"".GET_ON."\">".TXT_PERM_DISPLAY.":
				</div>
				<div class = \"newDisplayPermissionsValue\">
					<input type=\"radio\" checked=\"true\" name=\"".GET_DISPLAY_RADIO."\" value=\"".GET_DISPLAY_REL."\"><b>Relative:</b> Expires <input type=\"text\" name=\"".GET_DISPLAY_REL_VAL."\" value=\"$dspRel\" size=\"7\" /> Seconds after purchase.<br/>
					<input type=\"radio\" name=\"".GET_DISPLAY_RADIO."\" value=\"".GET_DISPLAY_ABS."\"><b>Absolute:</b> Expires at <input type=\"text\" name=\"".GET_DISPLAY_ABS_VAL."\" value=\"$dspAbs\" size=\"30\">
				</div>
			</div>
			<div class = \"newCopyPermissions\">
				<input type=\"checkbox\" name=\"".GET_COPY_ON."\" value=\"".GET_ON."\"><b>".TXT_PERM_EXCERPT.":</b> <input type=\"text\" name=\"".GET_COPY_INIT."\" value=\"$cpyInit\" size=\"4\">selections every <input type=\"text\" name=\"".GET_COPY_INC."\" value=\"$cpyIncr\" size=\"4\">seconds
			</div>
			<div class = \"newPrintPermissions\">
				<input type=\"checkbox\" name=\"".GET_PRINT_ON."\" value=\"".GET_ON."\"><b>".TXT_PERM_PRINT.":</b> <input type=\"text\" name=\"".GET_PRINT_INIT."\" value=\"$prnInit\" size=\"4\">pages every <input type=\"text\" name=\"".GET_PRINT_INC."\" value=\"$prnIncr\" size=\"4\">seconds
			</div>
		</div>
		<div class=\"bookOrderSection\">
			<input title=\"Purchase the book\" type=\"submit\" name=\"submit\" value=\"".TXT_BTN_PURCHASE."\" >
			<input title=\"Borrow the book\" type=\"submit\" value=\"".TXT_BTN_LOAN."\">
			<input type=\"hidden\" name=\"id\" value=\"$itemResource\">
			<input type=\"hidden\" name=\"resid\" value=\"$itemResourceItem\">
			<input type=\"hidden\" name=\"".SEARCH_SIMPLE_TEXT_KEY."\" value=\"$search_simple_text\">
		</div>

		<div class=\"passhashSection\" >
			<div class = \"passhashCheckboxLabel\">
			<table border=\"0\"><tr>
			<td><input type=\"checkbox\" name=".GET_PASSHASH_ON." value=\"".GET_ON."\" id=".GET_PASSHASH_ON."></td><td><b>".TXT_PASSHASH_LABEL."</b></td><td><div id=\"usePasshashIDLabel\"></div></td>
			</tr></table>
			</div>		
		</div>

		</form>
        

		<div class=\"bookDetailsSection\">
			<div class = \"bookFormat\">
				<div class = \"bookFormatHeader\">Format: </div>
				<div class = \"bookFormatValue\">$format</div>
			</div>
			<div class = \"bookPublisher\">
				<div class = \"bookPublisherHeader\">Publisher: </div>
				<div class = \"bookPublisherValue\">$publisher</div>
			</div>
			<div class = \"bookIDSection\">
	        		<div class = \"bookIDHeader\">ID:</div>
				<div class = \"bookIDValue\">$itemResource</div>
			</div>
		</div>
	    </td>
	  </tr>
	</table>";

	PRINT_DEBUG( "<p>Done.</p>");


////////////////////////////////////////////////////////////////////////////////////////////////////
// 
// HTML code below is Book details page template and it use the variables, calculated above
//

// Include HTML Header template
require_once('./header.php');

?>


<!-- Book details table -->
<?php echo $bookTable; ?>
<!-- /Book details table -->


<?php 
// Include HTML Footer template
require_once('./footer.php'); 
?>

</body>
</html>