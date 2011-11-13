<?php

/**
 * Class for retrieving Yahoo.com pagecount and inlinks.
 * @author Petr Stuchlik (stuchl4n3k.net)
 */
class YahooService extends RankService {
	
	const ADDRESS = 'http://siteexplorer.search.yahoo.com/search?bwm=i&bwmo=&bwmf=s&p=';
	
	private $cache;
	
	public function __construct() {
		$this->cache = array();
	}
	
	protected function getPagesAndBacklinks($domain) {
		$wrapper = array('backlinks' => -1, 'pages' => -1);
		$res = $this->getRemoteContents(self::ADDRESS.$domain);
		if ($res !== FALSE) {
			$wrapper = array('backlinks' => 0, 'pages' => 0);
			// I know this could be rewritten in to one preg_match, it's just way simpler this way :)
			if (preg_match('/<a class="btn"[^>]*>Pages \(([^\(]+)\)/Uis', $res, $matches)) {
				$wrapper['pages'] = str_replace(',', '', $matches[1]);
			}
			if (preg_match('/<span class="btn"[^>]*>Inlinks \(([^\(]+)\)/Uis', $res, $matches)) {
				$wrapper['backlinks'] = str_replace(',', '', $matches[1]);
			}
		}
		$this->cache[$domain] = $wrapper;
	}
	
	public function getPages($domain) {
		if (!isset($this->cache[$domain])) {
			$this->getPagesAndBacklinks($domain);
		}
		return $this->cache[$domain]['pages'];
	}
	
	public function getBacklinks($domain) {
		if (!isset($this->cache[$domain])) {
			$this->getPagesAndBacklinks($domain);
		}
		return $this->cache[$domain]['backlinks'];
	}
}