<?php

//http://api.geonames.org/countryInfoJSON?username=EditionsLVA
require_once('core.rest.php');
class geonames  {

	public $domain     = 'api.geonames.org';
	public $username = 'EditionsLVA';
	public $language = 'fr';

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function __construct(){
		
		global $_CONFIG;

		
		$this->rest = new coreRest('', '', $this->domain);
		$this->rest->setMode('curl');
	}

	
	public function countryInfoGet(  array $opt = array()){

		$mvs = array();
		$uri  = '/countryInfoJSON?username='.$this->username. '&lang='.$this->language;
		

		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'GET',
			'data'  => $mvs
		));


		if($d['body']['geonames']){//$d['headers']['HTTP/1.1'] == '200 OK'
		
			return $this->cleanarray( $d['body']['geonames'],$opt['format']);
		}

		return array();
		
		
		
		//return htmlGet($this->domain, "/countryInfoJSON" ,'GET', array ('username' => $this->username, 'lang' => $this->language));
		

	}
	
	
	function cleanarray($arr, $format) { // clean and sort the array and format for datasrc
		
		
		foreach ($arr as $v) {
			if ($v['population'] > 15000 )	$rec[$v['countryCode']]= $v['countryName'];	
			
			/*on exclut les pays de moins de 15000 habitants soit 
			Anguilla (13254) - Antarctique (0) - Géorgie du Sud et les îles Sandwich du Sud (30) - Île Bouvet (0) - Île Christmas (1500) - Île Heard et îles McDonald (0) - Île Norfolk (1828) - Îles Cocos (628) - Îles Malouines (2638) - Îles mineures éloignées des États-Unis (0) - Montserrat (9341) - Nauru (10065) - Nioué (2166) - Pitcairn (46) - Saint-Barthélémy (8450) - Saint-Pierre et Miquelon (7012) - Sainte-Hélène (7460) - Svalbard et Île Jan Mayen (2550) - Terres australes françaises (140) - Territoire britannique de l'océan Indien (4000) - Tokelau (1466) - Tuvalu (10472) - Vatican (921)*/
		}
		asort($rec);
		if ($format == 'html') {
			$html = '';
			foreach ($rec as $k => $v) {
				//$html .= '<option value="' . $k .'">' . $v . '</option>';
				$html .= '<option value="' .$k.'-'. $v .'">' . $v . '</option>';
			}
			return '<optgroup label="Tous les pays">'.$html.'</optgroup>';
		}
		else return $rec;
	
		
	}

		
       
}

