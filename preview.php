<form action="" method="get">
<input type="text" name="domain" />
<input type="submit" value="check" />
</form>

<?php


$domain = str_replace('http://', '', $_GET['domain']);
if (!strlen($domain)) exit();

// Including
require_once( dirname(__FILE__) . '/RankFace.php' );
require_once( dirname(__FILE__) . '/services/RankService.php' );
require_once( dirname(__FILE__) . '/services/GoogleService.php' );
require_once( dirname(__FILE__) . '/services/SeznamService.php' );
require_once( dirname(__FILE__) . '/services/YahooService.php' );
require_once( dirname(__FILE__) . '/services/AlexaService.php' );

// Processing

$rankFace = new RankFace();
print 'Google PageRank: '.$rankFace->getGooglePageRank($domain).'/10<br>';
print 'GoogleCache Rank: '.$rankFace->getGoogleCacheRank($domain).'/10<br>';
print 'Seznam SRank: '.$rankFace->getSeznamSRank($domain).'/10<br>';
print 'Alexa TrafficRank: '.$rankFace->getAlexaTrafficRank($domain).'<br>';
print 'Yahoo indexed pages: '.$rankFace->getYahooPages($domain).'<br>';
print 'Yahoo backlinks: '.$rankFace->getYahooBacklinks($domain).'<br>';

?>