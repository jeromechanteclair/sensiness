<?php


/**
 * @package probance-optin
 */

/* Classe contenant les fonctions permettant d'appeler l'API REST Probance API contact
 * 
 * Les URL sont du type http://xxx.probance.com/rt/api/resource/client/xx/search?prenom={prenom}&email={email}
 * avec xxx = début de l'URL du projet et xx = xxx si compte principal, xxx_souscompte pour un sous compte
 * 
 * La liste des paramètres à passer aux fonctions est définie dans le PPM correspondant
 */

namespace Inc\Common;

class ProbanceAPI 
{
    public $compte;
    public $souscompte;
    public $infra;
	public $login;
	public $password;
	public $debug;

	/**
	 * Instiate ProbanceAPI class
	 * @param string $compte 	 : Probance API account
	 * @param string $souscompte : Probance API subaccount
	 * @param string $login		 : Probance API login
	 * @param string $password	 : Probance API password
	 * @param string $infra		 : Probance Infra
	 */
    public function __construct()
    {

		/*
        * Retrieve API parameters
        */
        try{
			$this->login=get_option('probance-optin_api-login');
			$this->password=get_option('probance-optin_api-passwd');
			$this->compte=get_option('probance-optin_api-projectName');
			$this->souscompte=get_option('probance-optin_api-account');
			$this->infra=get_option('probance-optin_api-infra');
			$this->debug=get_option('probance-optin_api-cbdebug');
        }catch(Exception $e){
            Utils::write_log('[PROBANCE - optin] Erreur récupération option API : '.$e);
        }
    }

	/**
	 * HTTP GET
	 * Recherche si un membre existe avec l'email/le customer_id passé en paramètre (format attendu array de type [email][emailmembre]
	 * @param  string $param	: url parameters passed to the API get request
	 * @return string 'O' / '1' : return '1' if the contact exists else '0'
	 */
	public function apicontact_exist($param)
	{	

		$login=$this->login;
		$password=$this->password;
		$compte=$this->compte;
		$souscompte=$this->souscompte;
		$infra=$this->infra;
		$debug=$this->debug;

		//construction de l'URL d'appel
		$service_url=$this->build_urlContact($compte,$souscompte,$infra,'search',$param);
		
		if($debug==1)
			Utils::write_log('[PROBANCE - optin] CALL API (exist) ################################################## ');

		//Mise en place de la ressource curl
		$options = array(
				CURLOPT_USERPWD=>$login.':'.$password,
				CURLOPT_URL=>$service_url,
				CURLOPT_HTTPHEADER=>array('accept:application/json'),
				CURLOPT_RETURNTRANSFER=>1, //permet de récupérer la réponse dans une variable
				CURLOPT_HEADER=>0, //on ne retourne pas les header
				CURLOPT_NOBODY=>0, //on ne retourne pas le body
				CURLOPT_CONNECTTIMEOUT=>120, 
				CURLOPT_TIMEOUT=>120,
				);

		if($debug==1)
			Utils::write_log('[PROBANCE - optin] Options API : CURLOPT_USERPWD:'.$options[CURLOPT_USERPWD].' ||| : CURLOPT_URL:'.$options[CURLOPT_URL]);
		
		$ch = curl_init();	
		curl_setopt_array($ch, $options);
		
		//Exécution de l'appel
		if(!$result = curl_exec($ch))
		{
			trigger_error(curl_error($ch));
		}
		
		//On récupère le code HTTP de la réponse
		$httpcode=curl_getinfo($ch,CURLINFO_HTTP_CODE);

		// close cURL resource, and free up system resources
		curl_close($ch);
		//On retourne le bool suivant le retour
		if($debug==1)
			Utils::write_log('[PROBANCE - optin] Retour API : '.$result.' Retour strpos '.strpos($result, "Error"));
		
		return strpos($result, "ERROR")!==false?'0':'1';
	}

	/* Méthode apicontact_update
	* HTTP POST
	* Mise à jour du membre identifié par la clé définie.  
	*/
	public function apicontact_update($cle,$param)
	{	

		$login=$this->login;
		$password=$this->password;
		$compte=$this->compte;
		$souscompte=$this->souscompte;
		$infra=$this->infra;
		$debug=$this->debug;

		if($debug==1)
			Utils::write_log('[PROBANCE - optin] CALL API (update) ################################################### ');

		//on récupère le nom et l'email à passer dans l'URL
		$param_url=array($cle=>$param[$cle]);
		
		//construction de l'URL d'appel
		$service_url=$this->build_urlContact($compte,$souscompte,$infra,'update', $param_url);
			
		//encodage des paramètres en JSON
		$param_json=json_encode($param);
		
		//Mise en place de la ressource curl
		$options = array(
				CURLOPT_USERPWD=>$login.':'.$password,
				CURLOPT_URL=>$service_url,
				CURLOPT_CUSTOMREQUEST=>"POST",
				CURLOPT_POSTFIELDS=>($param_json),
				CURLOPT_HTTPHEADER=>array('content-length: ' . strlen($param_json),'content-type: application/json; charset=UTF-8','connection: Keep-Alive','accept:application/json'),
				CURLOPT_ENCODING=>'gzip,deflate',
				CURLOPT_RETURNTRANSFER=>1, //permet de récupérer la réponse dans une variable
				CURLOPT_HEADER=>0, //on ne retourne pas les header
				CURLOPT_NOBODY=>0, //on ne retourne pas le body
				CURLOPT_CONNECTTIMEOUT=>120,
				CURLOPT_TIMEOUT=>120,			
		);

		if($debug==1)
			Utils::write_log('[PROBANCE - optin] Options API : CURLOPT_USERPWD:'.$options[CURLOPT_USERPWD].' ||| : CURLOPT_URL:'.$options[CURLOPT_URL].' ||| : CURLOPT_POSTFIELDS:'.$options[CURLOPT_POSTFIELDS]);

		$ch = curl_init();
		curl_setopt_array($ch, $options);
		
		//Exécution de l'appel
		if(!$result = curl_exec($ch))
		{
			trigger_error(curl_error($ch));
		}
		
		//On récupère le code HTTP de la réponse
		$httpcode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($debug==1)
			Utils::write_log('[PROBANCE - optin] Retour API : '.$result);

		//On renvoie un tableau avec le code HTTP + le résultat de l'appel
		return(array($httpcode,$result));
	}

	/* Méthode apicontact_create
	* HTTP POST
	* Création du membre
	*/
	public function apicontact_create($param)
	{
		$login=$this->login;
		$password=$this->password;
		$compte=$this->compte;
		$souscompte=$this->souscompte;
		$infra=$this->infra;
		$debug=$this->debug;

		if($debug==1)
			Utils::write_log('[PROBANCE - optin] CALL API (create) ######################################################### ');

		//construction de l'URL d'appel
		$service_url=$this->getURL($compte,$souscompte,$infra).'/create';
			
		//encodage des paramètres en JSON
		$param_json=json_encode($param);
		
		//Mise en place de la ressource curl
		$options = array(
				CURLOPT_USERPWD=>$login.':'.$password,
				CURLOPT_URL=>$service_url,
				CURLOPT_CUSTOMREQUEST=>"POST",
				CURLOPT_POSTFIELDS=>($param_json),
				CURLOPT_HTTPHEADER=>array('content-length: ' . strlen($param_json),'content-type: application/json; charset=UTF-8','connection: Keep-Alive','accept:application/json'),
				CURLOPT_ENCODING=>'gzip,deflate',
				CURLOPT_RETURNTRANSFER=>1, //permet de récupérer la réponse dans une variable
				CURLOPT_HEADER=>0, 
				CURLOPT_NOBODY=>0, 
				CURLOPT_CONNECTTIMEOUT=>120,
				CURLOPT_TIMEOUT=>120,			
		);

		if($debug==1)
			Utils::write_log('[PROBANCE - optin] Options API : CURLOPT_USERPWD:'.$options[CURLOPT_USERPWD].' ||| : CURLOPT_URL:'.$options[CURLOPT_URL].' ||| : CURLOPT_POSTFIELDS:'.$options[CURLOPT_POSTFIELDS]);

		$ch = curl_init();
		curl_setopt_array($ch, $options);
		
		//Exécution de l'appel
		if(!$result = curl_exec($ch))
		{
			trigger_error(curl_error($ch));
		}
		
		//On récupère le code HTTP de la réponse
		$httpcode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($debug==1)
			Utils::write_log('[PROBANCE - optin] Retour API : '.$result);

		//On renvoie un tableau avec le code HTTP + le résultat de l'appel
		return(array($httpcode,$result));
	}

	/* Méthode apicontact_getInfos
	* HTTP GET
	* Récupère les infos avec l'email ou le customer_id passé en paramètre (format attendu array de type [email][emailmembre]
	* Retourne les données profil
	*/
	public function apicontact_getInfos($param)
	{	
		$login=$this->login;
		$password=$this->password;
		$compte=$this->compte;
		$souscompte=$this->souscompte;
		$infra=$this->infra;
		$debug=$this->debug;

		if($debug==1)
			Utils::write_log('[PROBANCE - optin] CALL API (getInfos) ########################################################### ');

		//construction de l'URL d'appel
		$service_url=$this->build_urlContact($compte,$souscompte,$infra,'search',$param);
		
		//Mise en place de la ressource curl
		$options = array(
				CURLOPT_USERPWD=>$login.':'.$password,
				CURLOPT_URL=>$service_url,
				CURLOPT_HTTPHEADER=>array('accept:application/json'),
				CURLOPT_RETURNTRANSFER=>1, //permet de récupérer la réponse dans une variable
				CURLOPT_HEADER=>0, //on ne retourne pas les header
				CURLOPT_NOBODY=>0, //on ne retourne pas le body
				CURLOPT_CONNECTTIMEOUT=>120, 
				CURLOPT_TIMEOUT=>120,
				);
		
		if($debug==1)
			Utils::write_log('[PROBANCE - optin] Options API : CURLOPT_USERPWD:'.$options[CURLOPT_USERPWD].' ||| : CURLOPT_URL:'.$options[CURLOPT_URL]);


		$ch = curl_init();	
		curl_setopt_array($ch, $options);
		
		//Exécution de l'appel
		if(!$result = curl_exec($ch))
		{
			trigger_error(curl_error($ch));
		}
		
		//On récupère le code HTTP de la réponse
		$httpcode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		
		//echo("strpos ". strpos($result, "ERROR"). "</br></br>");
		// close cURL resource, and free up system resources
		curl_close($ch);

		if($debug==1)
			Utils::write_log('[PROBANCE - optin] Retour API : '.$result);

		//On le result
		return $result;
	}

	/* Méthode getURL
	* Renvoie l'URL pour les appels à l'API
	*/
	private function getURL($compte,$souscompte,$infra){
		$service_url = "http://".$compte.".".$infra."/rt/api/resource/client/".$compte."_".$souscompte;
		return $service_url;
	}	

	/* Méthode build_urlContact 
	* Construit une URL avec l'opération et le tableau de paramètres passés en entrée *  
	*/
	private function build_urlContact($compte,$souscompte,$infra,$operation,$param)
	{		
		$service_url=$this->getURL($compte,$souscompte,$infra)."/".$operation."?".http_build_query($param);
		return $service_url;
	}

	/* Méthode random_str
	* Input : nb caractères
	* Génère une suite de caractères aléatoire
	*/
	public function random_str($nbr) {
		$str = "";
		$chaine = "abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNOPQRSUTVWXYZ0123456789";
		$nb_chars = strlen($chaine);

		for($i=0; $i<$nbr; $i++)
		{
			$str .= $chaine[ rand(0, ($nb_chars-1)) ];
		}

		return $str;
	}
	
}

?>