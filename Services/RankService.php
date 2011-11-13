<?php

/**
 * Abstract RankService helper.
 * @author Petr Stuchlik (stuchl4n3k.net)
 */
abstract class RankService {
	
	/**
	 * Loads remote content using sockets.
	 */
	public function getRemoteContents($url, $timeout = 10) {
		$result = "";
		$url = parse_url($url);
		
		if ($fs = @fsockopen ($url['host'], 80)) {
			if ( function_exists("socket_set_timeout") ) {
				socket_set_timeout($fs, $timeout, 0);
			} else if ( function_exists("stream_set_timeout") ) {
				stream_set_timeout($fs, $timeout, 0);
			}
			$http_get_cmd = "GET ".$url['path']."?".$url['query']." HTTP/1.0\r\n".
							"Host: ".$url['host']."\r\n".
							"Connection: Close\r\n\r\n";
			fwrite ($fs, $http_get_cmd);
			while (!feof($fs)) {
				$result .= @fread($fs, 40960);
			}
			fclose($fs);
			
			if (strpos($result, "404 Not Found"))  return FALSE;
			else {
				list($headers, $body) = preg_split("/\r\n\r\n/s", $result, 2); // separate headers
				return $body;
			}
		} else {
			return FALSE;
		}
	}
}
