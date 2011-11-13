<?php

/**
 * Universal SEO/Ranks facade class.
 * @author Petr Stuchlik (stuchl4n3k.net)
 * @version 0.1
 */
class RankFace {
	
	private $googleService;
	private $seznamService;
	private $alexaService;
	private $yahooService;
	
	public function __construct() {
		$this->googleService = new GoogleService();
		$this->seznamService = new SeznamService();
		$this->alexaService = new AlexaService();
		$this->yahooService = new YahooService();
	}
	
	public function getGooglePageRank($domain) {
		return $this->googleService->getPageRank($domain);
	}
	
	public function getGoogleCacheRank($domain) {
		return $this->googleService->getCacheRank($domain);
	}
	
	public function getSeznamSRank($domain) {
		return $this->seznamService->getSRank($domain);
	}
	
	public function getAlexaTrafficRank($domain) {
		return $this->alexaService->getTrafficRank($domain);
	}
	
	public function getYahooBacklinks($domain) {
		return $this->yahooService->getBacklinks($domain);
	}
	
	public function getYahooPages($domain) {
		return $this->yahooService->getPages($domain);
	}
	
}
