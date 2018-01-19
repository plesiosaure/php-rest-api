<?php

// Dcumentation
// http://217.167.201.245/test_supercal/supercal/doc

class calendrierMvsSync  {

	public $domain  = '';
	public $url     = '';

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function __construct(){
		
		global $_CONFIG;

		$this->url =  $_CONFIG['supercal']['url'];
		$this->domain =  $_CONFIG['supercal']['domain'];

		require_once('core.rest.php');
		$this->rest = new coreRest('', '', $this->domain);
		$this->rest->setMode('curl');
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	private function __log(array $data){

		$ok = ($data['raw']['ok'] === true);

		$data = array_merge(array(
			'from'        => 'supercal',
			'from_domain' => $_SERVER['HTTP_HOST'],
			'method'      => $_SERVER['REQUEST_METHOD'],
			'host'        => $this->domain,
			'success'     => $ok
		), $data);

		$this->apiLoad('calendrierLog')->dev($data);
	}
	
// VILLE ///////////////////////////////////////////////////////////////////////////////////////////////////////

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function villeGet(array $opt){

		
		$uri  = $this->url . '/ville/id/' . $opt['id'];
		

		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'GET',
			'data'  => array()
		));

		
		if(isset($d['body']['ok'])){
			return $d['body'];
		}

		else return array('ok' => false, 'msg' => "aucune réponse de l'API villeGet");

	}


// MANIFESTATION ///////////////////////////////////////////////////////////////////////////////////////////////////////

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function manifestationGet(array $opt){

		$uri  = $this->url . '/manifestation/id/' . $opt['id'] . '/type/' . $opt['mvs']['type'].(isset($opt['twin']) && $opt['twin']?'?twin':'');

		
		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'GET',
			'data'  => array()
		));

	

		if(isset($d['body']['ok'])){
			return $d['body'];
		}

		else return array('ok' => false, 'msg' => "aucune réponse de l'API manifestationGet");

	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
// UTILISE DEPUIS calendrierManifestation::manifestationCreationManif();
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function manifestationCreation(array $opt){

		
		$uri  = $this->url . '/manifestation';
		

		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'PUT',
			'data'  => $opt
		));


		return isset($d['body'])?$d['body']:array('ok' => false, 'msg' => "aucune réponse de l'API manifestationCreation");

		
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function manifestationDelete(array $opt){

		
		$uri  = $this->url . '/manifestation/id/'. $opt['id'] . '/type/' . $opt['mvs']['type'];
	
		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'DELETE',
			'data'  => array(),
		));

	
		if(isset($d['body']['ok'])){
			return $d['body'];
		}

		else return array('ok' => false, 'msg' => "aucune réponse de l'API manifestationDelete");
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function manifestationUpdate(array $opt){

		$uri  = $this->url . '/manifestation';
		

		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'POST',
			'data'  => $opt
		));

		

		return isset($d['body'])?$d['body']:array('ok' => false, 'msg' => "aucune réponse de l'API manifestationUpdate");
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	private function manifestationBuild($me){

		$dates = array();
		foreach(($me['date'] ?: array()) as $e){
			$start      = date("Y-m-d", $e['start']);
			$end        = date("Y-m-d", $e['end']);
			$canceled   = $e['canceled']  ? '1' : '0';
			$postponed  = $e['postponed'] ? '1' : '0';
			$unsure     = $e['unsure']    ? '1' : '0';

			$dates[]    = $start.'|'.$end.'|'.$canceled.'|'.$postponed.'|'.$unsure;
		}
		$dates = implode('||', $dates);

		$mvs = array(
			'intitule_manifestation'                    =>  $me['name'],
			'horaires_manifestation'                    =>  $me['schedule'],
			'ouverture_manifestation'                   =>  $me['opening'],
			'adresse_manifestation'                     =>  $me['geo']['address'],
			'situation_geo_manifestation'               =>  $me['geo']['comment'],
			'nb_exposant_manifestation'                 =>  $me['number'],
			'telephone_manifestation'                   =>  $me['phone'],
			'fax_manifestation'                         =>  $me['fax'],
			'mail_manifestation'                        =>  $me['email'],
			'site_web_manifestation'                    =>  $me['web'],
		    'tarif_manifestation'                       =>  $me['price'],
			'resume_date_manifestation'             	=>  $me['resume_date'],
			'communique_manifestation'                  =>  $me['presentation'],
			'type_manifestation'                        =>  $me['mvs']['type'],
			'id_manifestation_supercal'                 =>  $me['_id'],
			'id_categorie_manifestation'                =>  $me['mvs']['category'],
			'id_organisateur_manifestation'             =>  $me['organisateur']['id'],
			'id_ville_manifestation'                    =>  $me['city']['id'],
			'periodicite_manifestation'                 => ($me['periodicity'] ?: 1),
			'type_exposant_pro_manifestation'           => ($me['pro']          ? 1 : 0),
			'type_exposant_particulier_manifestation'   => ($me['individual']   ? 1 : 0),
			'type_exposant_habitant_manifestation'      => ($me['resident']     ? 1 : 0),
			'lieu_interieur_manifestation'              => ($me['indoor']       ? 1 : 0),
			'lieu_exterieur_manifestation'              => ($me['outdoor']      ? 1 : 0),
			'jouet_manifestation'                       => ($me['game']         ? 1 : 0),
			'manifestation_payante'                     => ($me['paying']       ? 1 : 0),
			'manifestation_gratuite'                    => ($me['free']         ? 1 : 0),
			'date_manifestation'                        =>  $dates
		);

		if(!empty($me['id'])) $mvs['id_manifestation'] = $me['id'];

		return $mvs;
	}






// ORGANISATEUR / ///////////////////////////////////////////////////////////////////////////////////////////////////////

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
// UTILISE DEPUIS calendrierManifestation::manifestationCreationOrganisateur();
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function organisateurGet(array $opt){

		
		$uri  = $this->url . '/organisateur/id/' . $opt['id'] ;
		

		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'GET',
			'data'  => array()
		));

		

		if(isset($d['body']['ok'])){
			return $d['body'];
		}

		else return array('ok' => false, 'msg' => "aucune réponse de l'API organisateurGet");

	}
	
	public function organisateurCreation(array $opt){

		$uri  = $this->url . '/organisateur';
		

		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'PUT',
			'data'  => $opt
		));

		

		return isset($d['body'])?$d['body']:array('ok' => false, 'msg' => "aucune réponse de l'API organisateurCreation");
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function organisateurDelete(array $opt){

		$uri  = $this->url . '/organisateur/_id/'.$opt['_id'];
		$time = microtime(true);

		$d = $this->rest->request(array(
			'uri'  => $uri,
			'verb' => 'DELETE',
			'data' => array()
		));

		$time = microtime(true) - $time;

		$this->__log(array(
			'time' => $time,
			'url'  => $uri,
			'raw'  => $d['body'],
			'api'  => array(
				'args' => array()
			)
		));

		return $d['body']['ok'];
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function organisateurUpdate(array $opt){

		$uri  = $this->url . '/organisateur';
		

		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'POST',
			'data'  => $opt
		));


		return isset($d['body'])?$d['body']:array('ok' => false, 'msg' => "aucune réponse de l'API organisateurUpdate");

	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	private function organisateurBuild($me){

		$mvs = array(
			'id_organisateur'                => $me['id'],
			'raison_sociale_organisateur'    => $me['name'],
			'civilite_organisateur'          => $me['title'],
			'nom_organisateur'               => $me['lastname'],
			'prenom_organisateur'            => $me['firstname'],
			'adresse_organisateur'           => $me['address'],
			'id_ville_organisateur'          => $me['city']['id'],
			'telephone_organisateur'         => $me['phone'],
			'fax_organisateur'               => $me['fax'],
			'mobile_organisateur'            => $me['mobile'],
			'fonction_organisateur'          => $me['fonction'],
			'email_organisateur'             => $me['email'],
			'siteweb_organisateur'           => $me['siteweb'],
			'commentaire_organisateur'       => $me['commentaire'],
		//	'date_creation_organisateur'     => $me[''],
		//	'date_modification_organisateur' => $me[''],
			'rubrique_organisateur'          => $me['rubrique'],
			'npai_organisateur'              => ($me['npai'] ? 1 : 0),
			'id_organisateur_supercal'       => $me['_id']
		//	'com_supercal'                   => $me[''],
		);

		return $mvs;
	}
	
	
	
// PAYS ///////////////////////////////////////////////////////////////////////////////////////////////////////

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function paysGet(array $opt){

		
		$uri  = $this->url . '/pays/nom/' . http_build_query($opt);
		

		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'GET',
			'data'  => array()
		));

		

		if(isset($d['body']['ok'])){
			return $d['body'];
		}

		else return array('ok' => false, 'msg' => "aucune réponse de l'API paysGet");

	}

// VILLEs ///////////////////////////////////////////////////////////////////////////////////////////////////////

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function villesGet(array $opt){
//echo "<h3>VillesGet</h3>";
//Zend_debug::dump($opt);
		
		$uri  = $this->url . '/villes?'. http_build_query($opt);
	
		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'GET',
			'data'  => array()
		));

//echo "<h3>Retour VillesGet</h3>";
//Zend_debug::dump($d['body']);		
		if(isset($d['body']['ok'])){
			return $d['body'];
		}

		else return array('ok' => false, 'msg' => "aucune réponse de l'API villesGet");

	}

// ORGANISATEURs ///////////////////////////////////////////////////////////////////////////////////////////////////////

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function organisateursGet(array $opt){

//Zend_debug::dump($opt);		
		$uri  = $this->url . '/organisateurs?'. http_build_query($opt);
	//Zend_debug::dump($uri);		

		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'GET',
			'data'  => array()
		));

	//Zend_debug::dump($d);	
		if(isset($d['body']['ok'])){
			return $d['body'];
		}

		else return array('ok' => false, 'msg' => "aucune réponse de l'API organisateursGet");

	}
	
	// ORGANISATEURs ///////////////////////////////////////////////////////////////////////////////////////////////////////

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function manifestationsGet(array $opt){

		
		$uri  = $this->url . '/manifestations?'. http_build_query($opt);
	

		$d = $this->rest->request(array(
			'debug' => DEBUG,
			'uri'   => $uri,
			'verb'  => 'GET',
			'data'  => array()
		));

//Zend_debug::dump($uri);	die;

		if(isset($d['body']['ok'])){
			return $d['body'];
		}

		else return array('ok' => false, 'msg' => "aucune réponse de l'API manifestationsGet");

	}
}

