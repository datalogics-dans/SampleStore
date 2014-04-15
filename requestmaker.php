<?php 	

/*  To use this, set the sharedsecret $secret variable and the $requestURL below.
      You can also change the request and the URL to try some other requests. */
	  
	function signNode($xmlDoc,$xmlNodeToBeSigned,$secretKey)
	{
		$ADEPT_NS = "http://ns.adobe.com/adept";
		require_once 'XMLSigningSerializer.php';
		$serializer = new XMLSigningSerializer(false);   	//true sets verbose debug
		$signingSerialization = $serializer->serialize($xmlNodeToBeSigned);
		$hmacData = base64_encode(hash_hmac('sha1',$signingSerialization,$secretKey,true));
		$hmacNode = $xmlDoc->createElementNS($ADEPT_NS, "hmac", $hmacData);
		$xmlNodeToBeSigned->appendChild($hmacNode); 		//insert <hmac> node
		return $hmacData;
	}

	function sendRequest($requestData){
		$nonce = base64_encode(mt_rand(20000000,30000000));   	//unique nonce
		$expiration = date("c", time() + 3600);   		//expires in one hour
		
		// We'll remove the closing </request>, then add in the nonce and expiration and add the closing element back
		$moreData = "<nonce>".$nonce."</nonce>"."<expiration>".$expiration."</expiration></request>";
		$requestData = str_replace("</request>", $moreData, $requestData);
		
		// setup the xml document with the data we created
		$xmlObjectPackage = new DOMDocument('1.0', 'UTF-8');
		$xmlObjectPackage->formatOutput = true;
		$xmlObjectPackage->loadXML($requestData);
		
		
		
		///////////////////////
		// ENTER YOUR ADMIN CONSOLE PASSWORD HERE ****************
		///////////////////////				
		$secret = base64_encode(pack('H*', sha1("dans1")));
		
		///////////////////////
		// ENTER THE URL OF YOUR ADMIN SERVICE HERE **************
		///////////////////////
		$requestURL = "http://dans-win7.dlogics.com:8080/admin/";
		
		

		$requestURL = $requestURL."QueryResourceItems";		
		$serverPassword	= base64_decode($secret);

		// sign the xml
		$hmacData = signNode($xmlObjectPackage, $xmlObjectPackage->documentElement, $serverPassword);
		
		$xmlRequest = $xmlObjectPackage->saveXML();
		
		// set up the request
		$ch = curl_init($requestURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/vnd.adobe.adept+xml'));
		
		// send the request and receive the response
		$result = curl_exec($ch);
		
		// check for errors
		if (curl_errno($ch))
		{
				$result = 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
			}
		else
		{
				$returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
				switch($returnCode){
						case 200:
						break;
					default:
						$result = 'HTTP ERROR -> ' . $returnCode;
						break;
				}
			}
		
		// save the response as an xml file; you'll need to create the XML directory or rework this
		$responseDOM = new DOMDocument('1.0', 'UTF-8');
		$responseDOM->formatOutput = true;
		$responseDOM->loadXML($result);
		$responseDOM->save("C:\acs4\out.xml"); //save XML DOM to file
		
		// save the request as well (you can see the reordering of elements and the HMAC here)
		$responseDOM = new DOMDocument('1.0', 'UTF-8');
		$responseDOM->formatOutput = true;
		$responseDOM->loadXML($xmlRequest);
		$responseDOM->save("C:\acs4\in.xml"); //save XML DOM to file
		
		curl_close($ch);
		
		$result = str_replace("<?xml version=\"1.0\"?>","",$result);
		$result = str_replace("response","resourceItemList",$result);
		return $result;
	}
?>