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
// ACS5 Store Sample Code - Configuration and constants;
//
// Description:  
// The HTML based store consist of 3 pages: Catalog, Details, Thank You .
//
// This script implements Configuration and constants for all pages
//
// Last updated: July 27, 2008
//
// Note:  This example requires Microsoft's MSXML 4.0 or higher to be installed.  Available at:
//        http://www.microsoft.com/downloads/details.aspx?familyid=3144b72b-b4f2-46da-b4b6-c5d7485f2b42&displaylang=en
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////
//
// * * * Global settings * * *
//
error_reporting(E_ALL);

// This file contains catalog of store's items
define('CATALOG_XML_FILE', "books.xml");

// Store configuraton file
define('CFG_XML_FILE', "config.xml"); // This file contains store's configuration

// path to no-image substitution
// no image picture will be shown only if book's xml is missing thumbnail URL tag (see XML_ITEM_THUMBNAIL_URL);
// if XML_ITEM_THUMBNAIL_URL is present, but points to unexisting file, then regular browser's "image not found" substitution will be shown.
define('URL_NO_IMAGE', "images/noimage.gif");

////////////////////////////////////////////////////////////////////////////////////////////////////
//
// * * * Global initialization * * *
//

// include common functions
require_once('./functions.php');

// Catalog XML keys
define('XML_ITEMS', "resourceItemList"); // root catalog element;
define('XML_NS_ADOBE', "http://ns.adobe.com/adept"); // Adobe namespace;
define('AD_NS', "ad");
define('XML_RESOURCE_ITEM_INFO', "resourceItemInfo"); // item key;
$xml_BookIDPath = AD_NS.":".XML_RESOURCE_ITEM_INFO."/".AD_NS.":resource";//

define('DC_NAME_SPACE', "http://purl.org/dc/elements/1.1/");

define('XML_ITEM_RESOURCE', "resource"); // resource ID
define('XML_ITEM_RESOURCE_ITEM', "resourceItem"); // resource item ID
define('XML_ITEM_METADATA', "metadata");
define('XML_ITEM_TITLE', "title"); // item title
define('XML_ITEM_CREATOR', "creator"); // item creator
define('XML_ITEM_FORMAT', "format"); // item format
define('XML_ITEM_DESCRIPTION', "description"); // item description
define('XML_ITEM_PUBLISHER', "publisher"); // item publisher
define('XML_ITEM_THUMBNAIL_URL', "thumbnailURL"); // link to the item's thumbnail
define('XML_ITEM_LICENSE_TOKEN', "licenseToken"); // item license (Permissions);

// XML Permissions keys
define('XML_PERMISSIONS', "permissions"); // Permissions root node
define('XML_DISPLAY', "display"); // Display Permissions node
define('XML_EXCERPT', "excerpt"); // Excerpt permissions node
define('XML_PRINT', "print"); // Print permissions node
define('XML_DEVICE', "device"); // A permission is only usable on a device with the given device id 
define('XML_DEVICETYPE', 'deviceType'); //A permission is only usable on a class of machine that matches deviceType
define('XML_UNTIL', "until"); // Expiration date for the permission
define('XML_DURATION', 'duration'); // Relative expiration for the permission in seconds
define('XML_MAX_RESOLUTION', "maxResolution"); // maximum resolution (in dpi) for bitmap printing 
define('XML_COUNT', "count"); // number of times an excerpt can be made or pages to be printed; see below for attribute descriptions
define('XML_COUNT_INITIAL', "initial"); // initial allowance for the count, given when document is fulfilled (on one device only)
define('XML_COUNT_MAX', "max"); // time in seconds, every that number of seconds a new unit of permission is granted (optional)  
define('XML_COUNT_INCREMENT_INTERVAL', "incrementInterval"); // maximal count that can be accumulated, count is not incremented beyond that number (optional) 
define('XML_LOAN', "loan"); // a loan with the given id must not have been returned  


// search 
//$search_simple_text_key="search_simple_text";
//$search_simple_text=getParam($search_simple_text_key);
define('SEARCH_SIMPLE_TEXT_KEY',"search");
$search_simple_text=getParam(SEARCH_SIMPLE_TEXT_KEY);

$search_substring_keywords=""; // for substring search we split words separated by space, comma or semicolon into array of separate words

// search kind constants
define('SEARCH_NO_SEARCH',0);
define('SEARCH_SIMPLE_REGEX',1);
define('SEARCH_SIMPLE_SUBSTRING',2);

$search_kind=getSearchKind($search_simple_text);

// catalog table body; global;
$catalogBody = "";

// catalog page "go to top" navigation
define('UP_LINK', "<a float=\"right\" href=\"#top\"title=\"Go UP\">^</a>");

// book details html body; global;
$bookTable = "";
$bookTitle = "";

// Configuration
define('CFG_DISTRIBUTOR_INFO', "distributorInfo");
define('CFG_DISTRIBUTOR', "distributor");
define('CFG_ORDERSOURCE', "ordersource");
define('CFG_SHARED_SECRET', "sharedSecret");
define('CFG_LINK_URL',"linkURL");
define('CFG_OPERATOR_URL', "operatorURL");

// GBLink params
define('KEY_ACT_PURCHASE', "enterorder");
define('KEY_ACT_LOAN', "enterloan");

// Permissions
$permDisplay = ""; // Display permissions
$permExcerpt = ""; // Excerption permissions
$permPrint   = ""; // Printing permissions
// Override permissions default values.
$dspRel = "60"; // Display relative 60 min by default;
$dspAbs = "9 Sep 2020 10:11:12 -0700"; // Absolute expiration date;
$cpyInit = "5"; // initial number of copy permissions
$cpyIncr = "60"; // increment in seconds of copy permissions
$prnInit = "6"; // initial number of print permissions
$prnIncr = "600"; // increment in seconds of print permissions
// permissions get request keys
define('GET_ON', "ON"); // "ON" parameter value
define('GET_OFF', "OFF"); // "OFF" parameter value
// override display permissions GET keys
define('GET_DISPLAY_ON', "dsp"); // if override display permissions are ON
define('GET_DISPLAY_RADIO', "dsp_type"); // Radio group "Display permissions relative or absolute"
define('GET_DISPLAY_REL', "urt"); // Display permissions relative
define('GET_DISPLAY_REL_VAL', "urt_tx"); // Display permissions relative
define('GET_DISPLAY_ABS', "uat"); // Display permissions absolute
define('GET_DISPLAY_ABS_VAL', "uat_tx"); // Display permissions absolute
//override copy permissions
define('GET_COPY_ON', "cpy"); // if override copy permissions are ON
define('GET_COPY_INIT', "cpy_nn"); // Initial number of allowed copies
define('GET_COPY_INC', "cpy_dd"); // Allowed copies increment period
//override print permissions
define('GET_PRINT_ON', "prn"); // if override print permissions are ON
define('GET_PRINT_INIT', "prn_nn"); // Initial number of allowed printed copies
define('GET_PRINT_INC', "prn_dd"); // Allowed prints copies increment period
// GBLink constant
define('DISPL_PERM_LOAN_ABS',"lat"); //$lat#tttt$  where 'tttt' (Expiration date) is the number of seconds since midnight, January 1, 1970 UTC. The Loan will expire at this Exact UTC time.
define('DISPL_PERM_LOAN_REL',"lrt"); //$lrt#tttt$  Where 'tttt' is the number of seconds of ownership. The loan will expire 'tttt' seconds after the license is created.
define('COPY_PERM',"cpy"); // $cpy#nnn#tttt$ is the Copy permission, Copy 'nnn' selections to clipboard every 'tttt' seconds, where 'nnn' is the event count (number of clipboard operations) and 'tttt' is the number of seconds for the interval.
define('PRINT_PERM',"prn"); // $prn#nnn#tttt$ Print 'nnn' pages every 'tttt' seconds where 'nnn' is the event count (number of pages permitted to print) and 'tttt' is the number of seconds for the interval.
define('DISPL_PERM_PURCH_ABS',"uat"); //$uat#tttt$  where 'tttt' (Expiration date) is the number of seconds since midnight, January 1, 1970 UTC. The rights will expire at this Exact UTC time.
define('DISPL_PERM_PURCH_REL',"urt"); //$urt#tttt$  Where 'tttt' is the number of seconds of ownership. The rights will expire 'tttt' seconds after the license is created.
define('DISPL_PERM_EVER',"ever"); //$prn#200#ever$ Print 200 pages ever (over the lifetime of ownership) 
// Passhash constant
//define('GET_PASSHASH_ON',"passhash");
define('GET_PASSHASH_NAME_ON',"passhashName");
define('GET_PASSHASH_PASSWORD_ON',"passhashPassword");




// Text constant
define('TXT_BTN_PURCHASE', "Purchase");
define('TXT_BTN_LOAN', "Borrow");
define('TXT_PERM_DISPLAY', "Display");
define('TXT_PERM_EXCERPT', "Copy");
define('TXT_PERM_PRINT', "Print");
define('TXT_PERM_UNLIM', "unlimited");
define('TXT_PERM_UNLIM_DISPLAY', "on any device");
define('TXT_PERM_UNLIM_COPYPRINT', "without restrictions");
define('TXT_PERM_DENIED', "not allowed");
define('TXT_PERM_MAXRES_START', 'at a maximum resolution of ');
define('TXT_PERM_MAXRES_END', ' dpi');
define('TXT_PERM_DEVICE_START', 'on a ');
define('TXT_PERM_DEVICE_SINGLE', 'single ');
define('TXT_PERM_DEVICE_END', 'device');
define('TXT_PERM_UNTIL', 'until ');
define('TXT_PERM_EXPIRATION_JOIN', ' or ');
define('TXT_PERM_DURATION_END', ' seconds after fulfillment');
define('TEXT_PERM_EXCERPT_SINGLE','copy');
define('TEXT_PERM_EXCERPT_PLURAL','copies');
define('TEXT_PERM_PRINT_SINGLE','page');
define('TEXT_PERM_PRINT_PLURAL','pages');
define('TEXT_PERM_COUNT_ONE','1 ');
define('TEXT_PERM_COUNT_ACCRUED', ' accrued every ');
define('TEXT_PERM_COUNT_ADDITIONAL', ', with an additional ');
define('TEXT_PERM_COUNT_TIME', ' seconds');
define('TEXT_PERM_COUNT_MAX', ' capped at ');
define('TEXT_PERM_ALLOWED', 'allowed ');
define('TXT_EXPIRES',"Expires");
define('TXT_PERM_NONE', "No further restrictions");

define('TXT_DUP_PERM_ERROR_START', 'Duplicate ' );
define('TXT_DUP_PERM_ERROR_END', ' element found. Using last one found for permissions.' );
define('TXT_INVALID_PERM_COMBO_MID', ' element is not allowend in a ');
define('TXT_INVALID_PERM_COMBO_END', ' permission.');
define('TXT_INVALID_DEVICE_ERROR', 'Device element had a value - ' );

define('TXT_PASSHASH_LABEL',"Use passhash for: ");

// To enable/disable debug printing, go to functions.php and 
// uncomment/comment print line in the function PRINT_DEBUG
function PRINT_DEBUG($DebugMsg)
{
// comment / uncomment to enable/disable debug info
//  Echo ("<br /><b>DEBUG:</b> ".$DebugMsg);
};

PRINT_DEBUG(SEARCH_SIMPLE_TEXT_KEY.": \"".$search_simple_text."\"");

?>
