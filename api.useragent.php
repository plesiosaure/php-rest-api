<?php


require_once('core.rest.php');
class useragent  {

	public $domain     = 'www.useragentstring.com';
	public $url     = '/';
	
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function __construct(){
		
		global $_CONFIG;

		
		$this->rest = new coreRest('', '', $this->domain);
		$this->rest->setMode('curl');
	}

	
	public function useragentGet(  array $opt = array(), $method = 'POST'){

		
		$complete_url = $this->rest->getScheme().$this->rest->getHost().":".$this->rest->getPort().$this->url;
		$full_url = $complete_url."?".http_build_query($opt);
		
		if (filter_var($complete_url, FILTER_VALIDATE_URL)) {
	
			$content = $this->rest->request(array(
				'debug' => DEBUG,
				'uri'   => $this->url,
				'verb'  =>  $method,
				'data'  => $opt
			));
	
			if ($content) {
				if ($content['headers']['HTTP'] == '200') return array(
					'ok'=>true,
					'msg'=>'',
					'data'=>isset($content['body'])?$content['body']:''
				);
				else return array(
					'ok'=>false,
					'msg'=>'erreur '.$content['headers']['HTTP'].' sur l\'url '.$full_url
				);
			}
	
			else return array(
				'ok' => false, 
				'msg' => "aucune réponse de l'API useragent pour ".$full_url
			);
		}
		else return array(
			'ok'=>false,
			'msg'=>'syntaxe invalide pour le site '.$complete_url);// logger une URL invalide a été soumise
		
	
	}
	
       
}