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
// This script implements Common functions for all pages
//
// Last updated: July 27, 2008
//
// Note:  This example requires Microsoft's MSXML 4.0 or higher to be installed.  Available at:
//        http://www.microsoft.com/downloads/details.aspx?familyid=3144b72b-b4f2-46da-b4b6-c5d7485f2b42&displaylang=en
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

define('GET_PASSHASH_ON',"passhash");

// Pase permissions block
// INPUT: $nodeRoot - root node of book's permissions
// RETURN: globals  $permDisplay, $permExcerpt, $permPrint;
function parsePermissions($nodeRoot)
{
global $permDisplay, $permExcerpt, $permPrint;

	$permLineDiv = '<div class="copyPermissionsValueLine">'; 
	$endDiv = '.</div>';
	$permDisplay = "";
	$permExcerpt = "";
	$permPrint   = "";

	if ($nodeRoot->hasChildNodes())
	{
		$nodeList=$nodeRoot->childNodes;

		$permValue = "";

		foreach ($nodeList as $node)
		{
			$permValue = parseSubPermissions($node, $node->nodeName);
			
			if( $permValue != "" )
			{
				$permValue =  $permLineDiv . TEXT_PERM_ALLOWED . $permValue . $endDiv ;
				switch($node->nodeName)
				{
					case XML_DISPLAY : 
						$permDisplay .= $permValue;
						break;
						
					case XML_EXCERPT:
						$permExcerpt .= $permValue;
						break;
					
					case XML_PRINT:
						$permPrint .= $permValue;
				}
			}
		}
	}

	if (!$permDisplay) // if <display> section is missed inside <permissions> then it is denied;
		$permDisplay = $permLineDiv . TXT_PERM_DENIED . $endDiv;
		
	if (!$permExcerpt) // if <excerpt> section is missed inside <permissions> then it is denied;
		$permExcerpt = $permLineDiv . TXT_PERM_DENIED . $endDiv;

	if (!$permPrint) // if <print> section is missed inside <permissions> then it is denied;
		$permPrint = $permLineDiv . TXT_PERM_DENIED . $endDiv;
}

// Parse single permision node (content of the root level persmissions nodes)
// INPUT: $nodeRoot
// RETURN: parsed permission output
function parseSubPermissions($nodeRoot, $permType)
{
global $dspAbs, $dspRel, $cpyInit, $prnInit, $prnIncr; // Override perimissions
	
	$result = "";
	
	if ($nodeRoot->hasChildNodes())
	{
		$nodeList=$nodeRoot->childNodes;
		
		//device and deviceType are melded into a single statement 
		$deviceFound = false;
		$deviceTypeFound = false;
		$deviceTypeValue = "";
		
		$deviceText = "";
		
		// until and duration are melded into a single statement
		$untilFound = false;
		$untilValue = "";
		$durationFound = false;
		$durationValue = "";
		$expirationText = "";
		
		$countFound = false;
		$countText = "";
		
		$resolutionFound = false;
		$resolutionText = "";
		
		foreach ($nodeList as $node)
		{
			switch($node->nodeName)
			{
				case XML_DEVICE: 
					if( $deviceFound )
						ERROR_ECHO( TXT_DUP_PERM_ERROR_START . $node->nodeName . TXT_DUP_PERM_ERROR_END);

					if( $node->nodeValue )
						ERROR_ECHO( TXT_INVALID_DEVICE_ERROR . $node->nodeValue );
					else
 						$deviceFound = true;
					break;
					
				case XML_DEVICETYPE:
					if( $deviceTypeFound )
						ERROR_ECHO( TXT_DUP_PERM_ERROR_START . $node->nodeName . TXT_DUP_PERM_ERROR_END);

					$deviceTypeFound = true;
					$deviceTypeValue = $node->nodeValue;
					break;
					
				case XML_UNTIL:
					if( $deviceTypeFound )
						ERROR_ECHO( TXT_DUP_PERM_ERROR_START . $node->nodeName . TXT_DUP_PERM_ERROR_END);

					$untilFound = true;
					$untilValue = $node->nodeValue;
					break;

				case XML_DURATION:
					if( $durationFound )
						ERROR_ECHO( TXT_DUP_PERM_ERROR_START . $node->nodeName . TXT_DUP_PERM_ERROR_END);

					$durationFound = true;
					$durationValue = $node->nodeValue;
					break;
					
				case XML_COUNT:
					if( $countFound )
						ERROR_ECHO( TXT_DUP_PERM_ERROR_START . $node->nodeName . TXT_DUP_PERM_ERROR_END);
						
					$countFound = true;
					$countText = parseCountNode($node, $permType); 
					break;
					
				case XML_MAX_RESOLUTION:
					if( $resolutionFound )
						ERROR_ECHO( TXT_DUP_PERM_ERROR_START . $node->nodeName . TXT_DUP_PERM_ERROR_END);
						
					$resolutionFound = true;
					if( $permType == XML_PRINT )
					{
						if( $node->nodeValue )
							$resolutionText = TXT_PERM_MAXRES_START . $node->nodeValue . TXT_PERM_MAXRES_END;
					}
					else
						ERROR_ECHO( $node->nodeName . TXT_INVALID_PERM_COMBO_MID . $permType . TXT_INVALID_PERM_COMBO_END );				

					break;
					
				case XML_LOAN: 
					ERROR_ECHO("Tag \"LOAN\" is not implemented");
					break;
			}
		}

		$result = $countText;
		
		$result = addPhraseText( $result, $resolutionText );

		// meld device and deviceType into a single statement
		if( $deviceFound && $deviceTypeFound ) {
			// ex: "on a single standalone device" 
			$deviceText = TXT_PERM_DEVICE_START . TXT_PERM_DEVICE_SINGLE . $deviceTypeValue . " " . TXT_PERM_DEVICE_END;
		} else if( $deviceFound )  {
			// ex: "on a single device" 
			$deviceText = TXT_PERM_DEVICE_START . TXT_PERM_DEVICE_SINGLE . TXT_PERM_DEVICE_END;
		} else if( $deviceTypeFound ) {
			// ex: "on a standalone device" 
			$deviceText = TXT_PERM_DEVICE_START . $deviceTypeValue . " " . TXT_PERM_DEVICE_END;
		}
		
		$result = addPhraseText( $result, $deviceText );

		// meld until and duration into a single statement
		if( $untilFound && $durationFound ) {
			$expirationText = TXT_PERM_UNTIL . $untilValue . TXT_PERM_EXPIRATION_JOIN . $durationValue .  TXT_PERM_DURATION_END;
		} else if ( $untilFound ) {
			$expirationText = TXT_PERM_UNTIL . $untilValue;
		} else if ( $durationFound ) {
			$expirationText = TXT_PERM_UNTIL . $durationValue .  TXT_PERM_DURATION_END;
		}
		
		$result = addPhraseText( $result, $expirationText );
	}
	else
	{
		// empty element - permission is allowed unconditionally
		switch( $permType )
		{
			case XML_DISPLAY: 
					$result = TXT_PERM_UNLIM_DISPLAY;
					break;
				
			case XML_EXCERPT: 
			case XML_PRINT:
					$result = TXT_PERM_UNLIM_COPYPRINT;
					break;
		}
	}
	
	return $result;
}

// Parse permissions node "count" content
// INPUT: two strings
// RETURN: if both strings are non empty, then the two strings concatenated with a comma between them  
//         otherwise return the non empty of the two strings
//         or the empty string if both are empty
function addPhraseText($firstPhrase, $secondPhrase)
{
	if($secondPhrase == "")
		return $firstPhrase;
	
	if($firstPhrase == "")
		return $secondPhrase;
		
	return $firstPhrase . ", " . $secondPhrase;
}

// Parse permissions node "count" content
// INPUT: Count node;
// RETURN: parsed permission output
function parseCountNode($node, $permType)
{
global $dspAbs, $dspRel, $cpyInit, $cpyIncr, $prnInit, $prnIncr; // Override perimissions

	$result = "";
	$permSingle = "";
	$permPlural = "";
	
	switch ($permType)
	{
		case XML_EXCERPT:
			$permSingle = TEXT_PERM_EXCERPT_SINGLE;
			$permPlural = TEXT_PERM_EXCERPT_PLURAL;
			break;
			
		case XML_PRINT:
			$permSingle = TEXT_PERM_PRINT_SINGLE;
			$permPlural = TEXT_PERM_PRINT_PLURAL;
			break;
	}


	if ($node->hasAttribute(XML_COUNT_INITIAL))
	{
		$countInitial = 1;
		$countInitial = $node->getAttribute(XML_COUNT_INITIAL);
		if( $countInitial == 1 )
			$result =  TEXT_PERM_COUNT_ONE . $permSingle;
		else
			$result =  $countInitial . " " . $permPlural;
	}
	
	
	if ($node->hasAttribute(XML_COUNT_INCREMENT_INTERVAL))
	{
		if( $result == "" )
			$result = TEXT_PERM_COUNT_ONE; 
		else
			$result .= TEXT_PERM_COUNT_ADDITIONAL;
			
		$result .=  $permSingle  . TEXT_PERM_COUNT_ACCRUED;
			
		$result .= $node->getAttribute(XML_COUNT_INCREMENT_INTERVAL) . TEXT_PERM_COUNT_TIME;
		
		// "max" may not be legally present without "increment" 
		// so this doesn't have to be parsed seperately
		if ($node->hasAttribute(XML_COUNT_MAX))
			$result .= TEXT_PERM_COUNT_MAX . $node->getAttribute(XML_COUNT_MAX) . " " . $permPlural;
	}
		
	return $result;
}

// If search string ($search_simple_text) is not empty 
// perform search for the specified search string, 
// within the text, specified by $search_text.
// Return true if if search string was found
// or there were no search specified (show all books)

function trySearch($search_text)
{
	global $search_simple_text, $search_kind, $search_substring_keywords;
	// Check if simple search specified;
	switch ($search_kind)
	{
		case SEARCH_NO_SEARCH:
			return true;
		case SEARCH_SIMPLE_REGEX:
			return (preg_match($search_simple_text,$search_text) > 0);
		case SEARCH_SIMPLE_SUBSTRING:
			foreach ($search_substring_keywords as $keyword)
				if (stristr($search_text,$keyword)!=false)
					return true;
 			return false;
		default:
			ERROR_DIE("Search type not implemented");
	}
	return false;	
}

// Validate search expression provided by user.
// Try to determinate, if it is valid regex or not.
// If it is not regex - we will do substring search.
// In case of substring search split search string to separate words (separators are space, comma or semicolon) 
// and perform substring search for every word.
// User can use "" to search for the entire phrase or to make search case sensetive.

function getSearchKind($search_text)
{
	global $search_substring_keywords;

	if (!$search_text)
		return SEARCH_NO_SEARCH;

	if (@preg_match($search_text, '')!==FALSE)
	{
		return SEARCH_SIMPLE_REGEX;
	}
	else
	{
		// split search string to separate words adn search for every word;
		$search_substring_keywords = preg_split("/[\s,;]+/", $search_text);
 		return SEARCH_SIMPLE_SUBSTRING;
	}
}

// In real life, random generation might not be used. 
// you might want to use unique database IDs for every transaction.
function get_random_digits()
{
	$r1 = mt_rand(); 
	$iDot = strpos($r1,".");
	return substr($r1,$iDot);

}

function get_uniqueID()
{
	//"0.04737610087934552"
	//"0.3395505120678609"

	$strOut = "";
	
	$r1 = get_random_digits();

	// TRUNCATE TO THE FIELD SIZE IF NEEDED
	$PRECISION = 30;
	while (strlen($r1) < $PRECISION){
		$r1 = $r1.get_random_digits();
	}

	$r1 = "ACS5-".$r1;
 
	$iLen = strlen($r1);
	if ($iLen > $PRECISION) 
		$r1 = substr($r1,0,$PRECISION);
		
	$strOut = $r1;

	if (!$strOut)
	  ERROR_DIE("get_uniqueID() failed");

	return ($strOut);
}


// return named param from GET request if exists;
function getParam($param_name)
{
	if(array_key_exists($param_name,$_GET) )
		return $_GET[$param_name];
	return "";
};

function ERROR_DIE($ErrMsg)
{
  Die ("<br /><b>ERROR:</b> ".$ErrMsg."<br />");
};

function ERROR_ECHO($ErrMsg)
{
  Echo ("<br /><b>ERROR:</b> ".$ErrMsg);
};

// convert given number of seconds into XX hours, XXmin, XXsec representation
function Sec2Time($minsec)
{
	$s = $minsec % 60;
	$minsec = ($minsec-$s)/60;
	$result = $s."sec";
	if ($minsec > 0)
	{
		$m = $minsec % 60;
		$result = $m."min ".$result;
		$minsec = ($minsec-$m)/60;
		if ($minsec > 0)
		{
			$result = $minsec."hr ".$result;
		}
			
	}
	return $result;	
}

function encode_htmlentities($line)
{
	return htmlentities($line, ENT_QUOTES, 'UTF-8');
}

function curPageURL() {
$_SERVER['FULL_URL'] = 'http';
$script_name = '';
if(isset($_SERVER['REQUEST_URI'])) {
    $script_name = $_SERVER['REQUEST_URI'];
} else {
    $script_name = $_SERVER['PHP_SELF'];
    if($_SERVER['QUERY_STRING']>' ') {
        $script_name .=  '?'.$_SERVER['QUERY_STRING'];
    }
}
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {
    $_SERVER['FULL_URL'] .=  's';
}
$_SERVER['FULL_URL'] .=  '://';
if($_SERVER['SERVER_PORT']!='80')  {
    $_SERVER['FULL_URL'] .=
    $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].$script_name;
} else {
    $_SERVER['FULL_URL'] .=  $_SERVER['HTTP_HOST'].$script_name;
}

 return $_SERVER['FULL_URL'];
}

function pkcs5_pad ($text, $blocksize)
{
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
}

function AES_128_CBC_encode($data_to_encrypt, $key, $iv) {
	$mcryptModule = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
	if (mcrypt_generic_init($mcryptModule, $key, $iv) != -1)
	{
		$padData=pkcs5_pad($data_to_encrypt,16);
		$encrypted = mcrypt_generic($mcryptModule,$padData );
		mcrypt_generic_deinit($mcryptModule);
		return 	$encrypted ;
	}
	die ("AES 128 CBC failed");
}

function getPasshash( $userName, $userPass ) { 
	////////////////////
	// passhash caluclation
	//    *  Step 1 - Generate Hash from UTF8-encoded username and password
	//          o A. Lowercase ASCII-7 characters in username - add 0x20 to any bytes between 0x41 (A) through 0x5A (Z). Note: in UTF8 any valid ASCII-7 byte always represents the corresponding single ASCII-7 character.
	//          o B. Strip spaces from the username - remove any bytes that are 0x20.
	//          o C. Concatenate username and password with a NULL character separating them and a NULL character terminating the byte stream. Note: an easy implementation of this is to use two SHA1 Update calls (one for username, one for password) with the length of the string including the terminator (string length + 1).
	//          o D. SHA1 hash the byte stream to create the initial plain text.
	//    * Step 2 - AES Encrypt Hash
	//          o A. Compute IV using username: use the first 16 bytes of the SHA1 hash of the result of 1.B (including null termination)
	//          o B. Compute key using password: use the first 16 bytes of the SHA1 hash of the password (including null termination)
	//          o C. Pad Hash: pad result of step 1.D using PKCS5 padding (12 bytes of 0x0C) to get to 32 bytes in length.
	//          o D. AES encrypt: 2 blocks (32 bytes) in CBC mode using the IV from 2.A, the key from 2B, and padded plaintext from 2.C. This will produce 32 bytes of cipher text.
	//    * Step 3 - SHA1 the cipher text
	//          o Compute the SHA1 hash of the result of 2.D
	////////////////////
	// 1A, 1B
	$userName=str_replace(" ","",strtolower($userName));
	// echo("Hello ".$userName." ".$userPass."<br />"); //debug
	//1C
	$userNamePass=$userName.chr(0).$userPass.chr(0);
	//1D
	$userSha1=sha1($userNamePass,true);
	// echo("user sha1=".$userSha1."<br />"); //debug
	
	//2A
	$passhashIV=substr(sha1($userName.chr(0),true),0,16);
	//2B
	$passhashKey=substr(sha1($userPass.chr(0),true),0,16);
	//2C, 2D (pkcs#5 padding is done inside AES_128_CBC_encode 
	// AES encryption
	$encrypted=AES_128_CBC_encode($userSha1, $passhashKey, $passhashIV);
	// printf("Encrypted:%s<br />",bin2hex($encrypted)); //debug

	// step 3
	$passhash=sha1($encrypted, true);
	// printf("Passhash:%s<br />",bin2hex($passhash)); //debug
	return $passhash;
}

?>
