<?php

/**
 * Class for retrieving Alexa.com Rank
 * @author Petr Stuchlik (stuchl4n3k.net)
 */
class AlexaService extends RankService {
	
	const ADDRESS = 'http://xml.alexa.com/data?cli=10&url=';
	
	public function getTrafficRank($domain) {
		$res = $this->getRemoteContents(self::ADDRESS.$domain);
		if ($res === FALSE) return -1;
		$matches = array();
		if (preg_match('/POPULARITY URL="(.*?)" TEXT="([0-9]+)"/is', $res, $matches)) {
        	return $matches[2];
		} else {
			return 0;
		}
	}
	
}
