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
// ACS5 Store Sample Code - Common functions
//
// Description:  
// The HTML based store consist of 3 pages: Catalog, Details, Thank You .
//
// This script implements common xml initialization for all pages
//
// Last updated: July 27, 2008
//
// Note:  This example requires Microsoft's MSXML 4.0 or higher to be installed.  Available at:
//        http://www.microsoft.com/downloads/details.aspx?familyid=3144b72b-b4f2-46da-b4b6-c5d7485f2b42&displaylang=en
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////
// Both of these requires are new and were not needed in the original code
///////////////////////////
require_once('./requestmaker.php');
require_once('./config.php');

////////////////////////////////////////////////////////////////////////////////////////////////////
//
// * * * Open & parse xml file * * * 
//

	////////////////////////////
	// Here's the original code that is currently not being used ************************
	// Uncomment this and find the CHANGE THIS to restore original functionality
	///////////////////////////
	// $fp = fopen(CATALOG_XML_FILE,"r");    // open the xml file
	// if (!$fp) ERROR_DIE("Error opening xml file \"".CATALOG_XML_FILE."\"");

    	// $xml = fread($fp, filesize(CATALOG_XML_FILE)); // read in the size of the file into the variable xml
    	// fclose($fp);                           // close the stream
	// if (!$xml) ERROR_DIE("Error reading xml file \"".CATALOG_XML_FILE."\"");

	
	
	
	////////////////////////////
	// Here's the new code. This will get the distributor information from config.xml ***
	// Comment this section and find the CHANGE THIS to restore original functionality
	////////////////////////////	
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
	$distid = $distributor->item(0)->nodeValue;
	$distributor = $distributor->length > 0 ? $distributor->item(0)->nodeValue : ERROR_DIE("Invalid store configuration: distributor info not found."); PRINT_DEBUG( "Distibutor: \"".$distributor."\"" );

	// This is the partial request that will be sent to the distributor whose ID we found above
	$requestData = "<request auth=\"builtin\" xmlns=\"http://ns.adobe.com/adept\">".
					"<distributor>".$distid."</distributor>".
					"</request>";
	
	// This function will fill out the rest of the request information, send it, and return the response
	$requestResult = sendRequest($requestData);
	
	////////////////////////////
	// From here on, it's original code with one minor change **************************************
	////////////////////////////
	
	
    
	
	$dom = new DOMDocument("1.0","UTF-8");
	if (!$dom)
  		ERROR_DIE("Can't create XML Dom Document");

	// CHANGE THIS to use $xml instead of $requestResult to restore original functionality
	if( !$dom->LoadXML($requestResult) )
		ERROR_DIE ("Failed to parse xml:<br />".$requestResult);

	// find root element
	$domRoot = $dom->documentElement;
	
	if (!$domRoot || ($domRoot->nodeName != XML_ITEMS))
		ERROR_DIE("Invalid catalog file \"".CATALOG_XML_FILE."\"");	

	$xpath=new DomXPath($dom);
	if (!$xpath)
		ERROR_DIE("Failed to create xpath");


	if (!$xpath->registerNamespace(AD_NS,XML_NS_ADOBE))
		ERROR_DIE("Failed to register namespace: \"".XML_NS_ADOBE."\"");


?>