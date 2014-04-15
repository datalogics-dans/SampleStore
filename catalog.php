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
// ACS5 Store Sample Code - Catalog Page;
//
// Description:  
// The HTML based store consist of 3 pages: Catalog, Details, Thank You .
//
// This script implements Catalog page
//
// Last updated: June 27, 2008
//
// Note:  This example requires Microsoft's MSXML 4.0 or higher to be installed.  Available at:
//        http://www.microsoft.com/downloads/details.aspx?familyid=3144b72b-b4f2-46da-b4b6-c5d7485f2b42&displaylang=en
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// include config and definitions
require_once('./config.php');

////////////////////////////////////////////////////////////////////////////////////////////////////
//
// * * * Open & parse store xml file * * *
//

// Load XML file, create DOM, init xpath
require_once('./xml_init.php');

	// Load store config XML and parse what we need
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
	$dateval=time();
	$action="enterorder";
	$transaction=get_uniqueID();
	
	$linkArgs = array($linkURL, $dateval, $action, $transaction, $ordersource, $sharedSecret);

	$divcounter = 0;
	$booksList = $xpath->query(AD_NS.":".XML_RESOURCE_ITEM_INFO);

	if ($booksList->length==0)
		ERROR_DIE("Catalog is empty");
	
	$grid_count = 3;
	$catalogBody = "<ul class='list'>";
	// create html for every book
	for ($i=0;$i<$booksList->length;$i++){
		createCatalogItem($booksList->item($i),$linkArgs);
	}
	$catalogBody .= "</ul>";
	PRINT_DEBUG( "Done.");
	
function truncate($input, $maxWords, $maxChars)
{
    $words = preg_split('/\s+/', $input);
    $words = array_slice($words, 0, $maxWords);
    $words = array_reverse($words);

    $chars = 0;
    $truncated = array();

    while(count($words) > 0)
    {
        $fragment = trim(array_pop($words));
        $chars += strlen($fragment);
        if($chars > $maxChars) break;
        $truncated[] = $fragment;
    }
    $result = implode($truncated, ' ');
    return $result . ($input == $result ? '' : '...');
}

////////////////////////////////////////////////////////////////////////////////////////////////////
// Function createCatalogItem
// Input: $book // Single book item XML node;
// Globals: $search_simple_text: simple search' text;
// Output: output is stored in global variable $catalogBody;
// Purpose: create a piece of catalog html code for one book, specified by $book;
//          add single book html to catalogBody;

function createCatalogItem($book, $linkArgs)
{
global 	$show_up_link, $search_simple_text;
global $botmBody, $divcounter, $catalogBody;


	PRINT_DEBUG( "" );
	PRINT_DEBUG( "<b>".$book->nodeName.":</b>" );

	if ($book->hasChildNodes())
		$itemsList=$book->childNodes;
	else
		return;

	// showItem define where current book comply search creteria;
	$showItem = false;
	
	foreach ($itemsList as $item){	
		switch($item->nodeName)
		{
			case XML_ITEM_RESOURCE : $itemResource = $item->nodeValue; PRINT_DEBUG( "<b>".$item->nodeName.":</b> ".$item->nodeValue ); 
				// check if ID comply search criteria (if any search specified);
				$showItem=$showItem || trySearch($itemResource);
				break;

			case XML_ITEM_RESOURCE_ITEM : $itemResourceItem = $item->nodeValue; PRINT_DEBUG( "<b>".$item->nodeName.":</b> ".$item->nodeValue ); 
				break;

			case XML_ITEM_METADATA : $itemMetadata = $item->nodeValue; PRINT_DEBUG( "<b>".$item->nodeName.":</b> ".$item->nodeValue); 

				$titleElem=$item->getElementsByTagNameNS(DC_NAME_SPACE, XML_ITEM_TITLE); 
				$title = $titleElem->length > 0 ? $titleElem->item(0)->nodeValue : ""; PRINT_DEBUG( "Title: \"".$title."\"" );
				// check if TITLE comply search criteria (if any search specified);
				$showItem=$showItem || trySearch($title);
				$title = encode_htmlentities($title);

				$creatorElem=$item->getElementsByTagNameNS(DC_NAME_SPACE, XML_ITEM_CREATOR); 
				$creator = $creatorElem->length > 0 ? $creatorElem->item(0)->nodeValue : ""; PRINT_DEBUG( "Creator :\"".$creator."\"" );
				// check if CRETOR comply search criteria (if any search specified);
				$showItem=$showItem || trySearch($creator);
				$$creator = encode_htmlentities($creator);

				$formatElem=$item->getElementsByTagNameNS(DC_NAME_SPACE, XML_ITEM_FORMAT); 
				$format = $formatElem->length > 0 ? $formatElem->item(0)->nodeValue : ""; PRINT_DEBUG( "Format :\"".$format."\"" );
				$format = encode_htmlentities($format);
				

				$descriptionElem=$item->getElementsByTagNameNS(DC_NAME_SPACE, XML_ITEM_DESCRIPTION); 
				$description = $descriptionElem->length > 0 ? $descriptionElem->item(0)->nodeValue : ""; PRINT_DEBUG( "Description :\"".$description."\"" );
				// check if DESCRIPTION comply search criteria (if any search specified);
				$showItem=$showItem || trySearch($description);
				$description = encode_htmlentities($description);

				$publisherElem=$item->getElementsByTagNameNS(DC_NAME_SPACE, XML_ITEM_PUBLISHER); 
				$publisher = $publisherElem->length > 0 ? $publisherElem->item(0)->nodeValue : ""; PRINT_DEBUG( "Publisher :\"".$publisher."\"" );
				$publisher = encode_htmlentities($publisher);

				$itemThumbnailURLElem=$item->getElementsByTagName(XML_ITEM_THUMBNAIL_URL); 
				$itemThumbnailURL = $itemThumbnailURLElem->length > 0 ? $itemThumbnailURLElem->item(0)->nodeValue : ""; PRINT_DEBUG( "itemThumbnailURL :\"".$itemThumbnailURL."\"" );
				if (!$itemThumbnailURL)
					$itemThumbnailURL=URL_NO_IMAGE;

				break;

		}
	}
	
	///////////////////////
	// ENTER YOUR BOOK OF THE MONTH RESOURCE ID HERE ****************
	///////////////////////
	if (strcmp($itemResource,"urn:uuid:9d92485f-2c98-444c-9ff2-dae8c497d114") == 0)
		$botm = true; // This is our book of the month
	
	if (!$showItem and !$botm)
		return; // Item does not comply search criteria;
	
	if (strpos(strtoupper($title),"NOT FOR PUBLIC") !== FALSE)
			return; // don't display these books
	
	$upLink = (!$show_up_link = !$show_up_link) ? UP_LINK : "";
	
	$description2 = truncate($description, 20, 80);

	$transaction=get_uniqueID();
	$bookDownloadURL = 
			"action=".urlencode($linkArgs[2]).
			"&ordersource=".urlencode($linkArgs[4]).
			"&orderid=".urlencode($transaction).
			"&resid=".urlencode($itemResource).
			$rights.
			"&dateval=".urlencode($linkArgs[1]).
			"&gblver=4";
			
	$bookDownloadURL = $linkArgs[0]."?".$bookDownloadURL."&auth=".hash_hmac("sha1", $bookDownloadURL, $linkArgs[5] ); 

	if ($botm == false){
		$divcounter++;
		$catalogBody .= "
		<li>
			<a class=\"various\" href=\"#bookdiv$divcounter\" name=\"$itemResource\">
			<div class=\"book_div\">
				
				<img src=\"$itemThumbnailURL\" alt=\"".$title."\"  width=\"100\" height=\"150\" border=\"0\" hspace=\"10\" />

			</div>
		</a>

		</li>

		<div id=\"bookdiv$divcounter\" class=\"book_detail\" style=\"display:none\">
			
			<div class=\"left\">
				<div style=\"width: 200px; height: 275px;\">
				<img src=\"$itemThumbnailURL\" alt=\"".$title."\" border=\"0\" width=\"200\" height=\"275\" />
				</div>
				<div class=\"detailsLink\"><a class=\"readingListLink\" href=\"$bookDownloadURL\" title=\"See book's details/purchase the book\">Read now</a>
				</div>
			</div>
			<div class=\"right\">
				<div class=\"content\">
					<p class=\"bookTitle\">$title</p>
					<div class=\"divider\"></div>
					<p class=\"bookAuthor\">$creator</p>
					<p class=\"bookDescription\">$description</p>
				</div>
			</div>

		</div>
		";
	}
	else{
		$botmBody .= "
		<div id=\"banner_div\" class=\"left\">
			<div class=\"banner_description\">
				BOOK OF THE MONTH
			</div>
			<div class=\"banner_divider caps\"></div>
			<div class=\"banner_image left\">
				<div style=\"width: 125px; height: 190px;\">
				<img src=\"$itemThumbnailURL\" alt=\"".$title."\" border=\"\" width=\"125\" height=\"190\" />
				</div>
				<div class=\"bannerLink\"><a class=\"readingListLink\" href=$bookDownloadURL title=\"See book's details/purchase the book\">Read Now</a></div>
			</div>
			<div class=\"banner_text right\">
				<p class=\"booktitle\">$title</p>
				<p class=\"banner_author\">$creator</p>".
				///////////////////////
				// ENTER YOUR BOOK OF THE MONTH TRUNCATED DESCRIPTION HERE ****************
				///////////////////////
				"<p>Snow Crash is Neal Stephenson's third novel, published in 1992. Like many of Stephenson's other novels it covers history, linguistics, anthropology, archaeology, religion, computer science, politics, cryptography, memetics and philosophy. Stephenson explained the title of the novel in his 1999 essay In the Beginning... was the Command Line as his term...</p><a class=\"various\" href=\"#bookdiv00\">More</a></p>
			</div>
		</div>


		<!-- banner popup -->
		<div id=\"bookdiv00\" class=\"book_detail\" style=\"display:none\">
			
			<div class=\"left\">
				<div style=\"width: 200px; height: 275px;\">
				<img src=\"$itemThumbnailURL\" alt=\"".$title."\" border=\"0\" width=\"200\" height=\"275\" />
				</div>
				<div class=\"detailsLink\"><a class=\"readingListLink\" href=\"$bookDownloadURL\" title=\"See book's details/purchase the book\">Read now</a>
				
				
				</div>
			</div>
			<div class=\"right\">
				<div class=\"content\">
					<p class=\"bookTitle\">$title</p>
					<p class=\"bookTag\"></p>
					<div class=\"divider\"></div>
					<p class=\"bookAuthor\">$creator</p>
					<p class=\"bookDescription\">$description</p>
				</div>
			</div>

		</div>


		<div style=\"clear: right;\"></div>";
	}

}

//TODO:
//limit word count on description above
//MOVE THIS:
//<div class=\"up\">
//		$upLink		
//	</div>
	
	
////////////////////////////////////////////////////////////////////////////////////////////////////
// 
// HTML code below is Catalog page template and it use the variables, calculated above
//

// Include HTML Header template
require_once('./header.php');

echo "<div id=\"container_div\">";

	$transaction=get_uniqueID();
	$bookDownloadURL = 
		"action=".urlencode($linkArgs[2]).
		"&ordersource=".urlencode($linkArgs[4]).
		"&orderid=".urlencode($transaction).
		"&resid=".urlencode("urn:uuid:04caa649-e517-4b2b-92cc-0f144a9dd5b8").
		$rights.
		"&dateval=".urlencode($dateval).
		"&gblver=4";	

	$bookDownloadURL = $linkURL."?".$bookDownloadURL."&auth=".hash_hmac("sha1", $bookDownloadURL, $sharedSecret ); 

echo $botmBody;
// echo "
// <div id=\"banner_div\" class=\"left\">
	// <div class=\"banner_description\">
    	// BOOK OF THE MONTH
    // </div>
    // <div class=\"banner_divider caps\"></div>
    // <div class=\"banner_image left\">
		// <div style=\"width: 125px; height: 190px;\">
    	// <img src=\"images/botm.jpg\" alt=\"The Memoirs of Sherlock Holmes\" border=\"\" width=\"125\" height=\"190\" />
        // </div>
        // <div class=\"bannerLink\"><a class=\"readingListLink\" href=$bookDownloadURL title=\"See book's details/purchase the book\">Read Now</a></div>
    // </div>
    // <div class=\"banner_text right\">
		// <p class=\"booktitle\">The Memoirs of Sherlock Holmes</p>
    	// <p class=\"banner_author\">Sir Arthur Conan Doyle</p>
    	// <p>Boasting some of Sherlock Holmes's finest adventures, this classic 1894 collection was originally written in serial form. Eleven of the most popular tales of the immortal".
			// "sleuth include \"Silver Blaze,\" concerning the \"curious incident of the dog in the night-time\"; \"The Greek Interpreter,\" starring Holmes's even more formidable brother, Mycroft; and \"The Final ".
			// "Problem,\" the detective's notorious confrontation with arch-criminal Moriarty at the Reichenbach Falls.</p><a class=\"various\" href=\"#bookdiv00\">More</a></p>
	// </div>
// </div>


// <!-- banner popup -->
// <div id=\"bookdiv00\" class=\"book_detail\" style=\"display:none\">
	
	// <div class=\"left\">
    	// <div style=\"width: 200px; height: 275px;\">
    	// <img src=\"images/botm.jpg\" alt=\"Ocean at the End of the Lane\" border=\"0\" width=\"200\" height=\"275\" />
        // </div>
        // <div class=\"detailsLink\"><a class=\"readingListLink\" href=\"$bookDownloadURL\" title=\"See book's details/purchase the book\">Read now</a>
		
		
		// </div>
    // </div>
    // <div class=\"right\">
		// <div class=\"content\">
			// <p class=\"bookTitle\">The Memoirs of Sherlock Holmes</p>
			// <p class=\"bookTag\"></p>
			// <div class=\"divider\"></div>
    		// <p class=\"bookAuthor\">Sir Arthur Conan Doyle</p>
    		// <p class=\"bookDescription\">Boasting some of Sherlock Holmes's finest adventures, this classic 1894 collection was originally written in serial form. Eleven of the most popular tales of the immortal".
			// "sleuth include \"Silver Blaze,\" concerning the \"curious incident of the dog in the night-time\"; \"The Greek Interpreter,\" starring Holmes's even more formidable brother, Mycroft; and \"The Final ".
			// "Problem,\" the detective's notorious confrontation with arch-criminal Moriarty at the Reichenbach Falls.</p>
		// </div>
	// </div>

// </div>


// <div style=\"clear: right;\"></div>"

echo $catalogBody;

echo "</div>";

require_once('./footer.php');
?>

</body>
</html>
