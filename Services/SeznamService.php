<?php

/**
 * Class for retrieving Seznam.cz SRank
 * @author Petr Stuchlik (stuchl4n3k.net)
 *
 * Based on original service by http://xrank.cz/
 *
 */
class SeznamService extends RankService {
	
	public function getSRank($domain) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?><methodCall><methodName>getRank</methodName><params><param><value><string>0</string></value></param><param><value><string>'.htmlspecialchars($domain).'</string></value></param><param><value><i4>0</i4></value></param></params></methodCall>';
		$request  = "POST /RPC2 HTTP/1.1\r\n";
		$request .= "Host: srank.seznam.cz\r\n";
		$request .= "Content-Type: text/xml\r\n";
		$request .= "Content-Length: ".strlen($xml) . "\r\n";
		$request .= "Connection: Close\r\n\r\n";
		$request .= $xml;
		$errNo = $errStr = '';
		$socket = fsockopen('srank.seznam.cz', 80, $errNo, $errStr, 10);
		if ($socket === FALSE) {
			return -1;
		}
		fwrite($socket, $request);
		$response = '';
		while (!feof($socket)) {
			$response .= fgets($socket, 1024);
		}
		$response = preg_replace('/^(.+\r\n)+\r\n/', '', $response);
		$doc = new DOMDocument;
		if (!$doc->loadXml($response)) {
			return -1;
		}
		$xpath = new DOMXPath($doc);
		$result = $xpath->evaluate('string(//member[name = "rank"]/value)');
		if (!is_numeric($result)) {
			return -1;
		}
		$rank = round((int)$result * 100 / 255 / 10);
		return $rank;
	}
}
