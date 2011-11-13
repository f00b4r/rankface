<?php

/**
 * Class for retrieving Google ranks.
 * @author Petr Stuchlik (stuchl4n3k.net)
 *
 * PageRang algorithm based on:
 *      http://www.hm2k.com/projects/pagerank
 *      PageRank Lookup v1.1 by HM2K (update: 31/01/07)
 *      based on an alogoritham found here: http://pagerank.gamesaga.net/
 *
 */
class GoogleService extends RankService {

    const PR_ADDRESS = 'http://toolbarqueries.google.com/tbr?client=navclient-auto&ch=%%CHECKSUM%%&features=Rank&q=info:%%DOMAIN%%';
	const CACHE_ADDRESS = 'http://webcache.googleusercontent.com/search?strip=1&hl=en&q=cache:';
    const UA_STRING = 'Opera/9.63 (X11; Linux i686; U; en) Presto/2.1.1';

	/**
	 * Converts the given string to number.
	 * @see GoogleService::hashUrl()
	 */
    private function strToNum($Str, $Check, $Magic) {
        $Int32Unit = 4294967296;  // 2^32
        $length = strlen($Str);
        for ($i = 0; $i < $length; $i++) {
            $Check *= $Magic;
            // If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31),
            // the result of converting to integer is undefined
            // refer to http://www.php.net/manual/en/language.types.integer.php
            if ($Check >= $Int32Unit) {
                $Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
                //if the check less than -2^31
                $Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
            }
            $Check += ord($Str{$i});
        }
        return $Check;
    }

    /**
     * Genearates a hash for the given url.
	 * @param string A string to be hashed
	 * @return string Hashed stirng
     */
    private function hashUrl($string) {
        $Check1 = $this->strToNum($string, 0x1505, 0x21);
        $Check2 = $this->strToNum($string, 0, 0x1003F);

        $Check1 >>= 2;
        $Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
        $Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
        $Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);

        $T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
        $T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );

        return ($T1 | $T2);
    }

    /**
     * Genearates a checksum for the hashed string.
	 * @param string A hashed stirng
	 * @return string Checksum for the given string
     */
    private function checkHash($hash) {
        $CheckByte = 0;
        $Flag = 0;

        $HashStr = sprintf('%u', $hash) ;
        $length = strlen($HashStr);

        for ($i = $length - 1;  $i >= 0;  $i --) {
            $Re = $HashStr{$i};
            if (1 === ($Flag % 2)) {
                $Re += $Re;
                $Re = (int)($Re / 10) + ($Re % 10);
            }
            $CheckByte += $Re;
            $Flag ++;
        }

        $CheckByte %= 10;
        if (0 !== $CheckByte) {
            $CheckByte = 10 - $CheckByte;
            if (1 === ($Flag % 2) ) {
                if (1 === ($CheckByte % 2)) {
                    $CheckByte += 9;
                }
                $CheckByte >>= 1;
            }
        }

        return '7'.$CheckByte.$HashStr;
    }

    /**
     * @return string the pagerank checksum hash
     */
    private function getCheckSumForUrl($url) {
        return $this->checkHash($this->hashUrl($url));
    }

    /**
     * @return the pagerank figure
     */
    public function getPageRank($domain) {
        $checksum = $this->getCheckSumForUrl($domain);
		$address = str_replace('%%CHECKSUM%%', $checksum, self::PR_ADDRESS);
		$address = str_replace('%%DOMAIN%%', $domain, $address);
		$res = $this->getRemoteContents($address);
		if ($res === FALSE) return -1;
		$pos = strpos($res, "Rank_");
		if ($pos === FALSE) return 0;
		$res = trim(substr($res, $pos + 9));
		$res = str_replace("\n", '', $res);
       	return $res;
    }
	
	public function getCacheRank($domain) {
		$cache_rank_scale = array(-PHP_INT_MAX, 1, 2, 3, 5, 8, 13, 21, 33, 54, PHP_INT_MAX);	// Fibonacci-style scale
		
		$res = $this->getRemoteContents(self::CACHE_ADDRESS.$domain);
		if ($res === FALSE) return 0;
		$matches = array();
		$date = FALSE;
		if (preg_match('/It is a snapshot of the page as it appeared on ([^.]+)./is', $res, $matches)) {
			$date = strtotime($matches[1]);
		}
		if ($date === FALSE) return 0;
		$age = (int)(time() - $date) / 86400;
		$rank = 10;
		for ($i=1; $i<sizeof($cache_rank_scale)-1; $i++) {
			if ($age > $cache_rank_scale[$i-1] && $age <= $cache_rank_scale[$i]) {
				$rank = $i;
			}
		}
		$rank = sizeof($cache_rank_scale) - $rank; // invert it (more is better)
		return $rank;
	}
}
