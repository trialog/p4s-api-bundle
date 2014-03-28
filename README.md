AMISURE - P4S API
========================

[![Latest Stable Version](https://poser.pugx.org/trialog/p4s-api-bundle/v/stable.png)](https://packagist.org/packages/trialog/p4s-api-bundle)

La plateforme P4S du projet de recherche français AMISURE (http://www.amisure.fr) offre une API REST.
Cette bibliothèque fournit un bundle Symfony2 permettant une intégration facilitée avec cette API. Plus besoin de se soucier de l'API REST bas niveau, il est possible de manipuler directement des objets PHP.

Features
---------------------
* Authenticate to the P4S - OAuth 2.0, using the classical Symfony2 login workflow
* Access to the P4S API using PHP methods and objects
	* Retrieve beneficiary information
	* List organisations and their users
	* Create and list events of the beneficiary agenda
	* Create link between the beneficiary and organisations or organisation users
	* Create and list the evaluations of the beneficiary

Installation & Usage
---------------------
Add this library to your Symfony2 project using your Composer file. You need to add the following dependencies:

	"require" : {
		"php" : ">=5.3.3",
		"hwi/oauth-bundle" : "dev-master",
		"trialog/p4s-api-bundle" : "dev-master",
		"zumba/json-serializer" : "dev-master"
	}
	
Normally, only "trialog/p4s-api-bundle" should be required, but a known bug prevents us to use this alone.

Then, you need to add this library as a bundle to app/AppKernel.php:

	public function registerBundles()
	{
		$bundles = array(
			// ...
			new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
			new Amisure\P4SApiBundle\AmisureP4SApiBundle()

You can then use the p4s.accessor service in your code, for exemple in a Controller to retrieve a list of Organisations:

	$this->get('p4s.accessor')->findOrganizations(array(
		'organizationType' => UserConstants::SAAD,
		'departementCode' => '94'
	));


_If you don't want to use this bundle to manage the OAuth authentication, you can by-pass the following steps. But you need to provide the OAuth "access_token" as a session variable: $this->session->get('access_token')._

To enable the OAuth authentification system, you need to configure this library in your config.yml file:

	# HWIOAuth
	hwi_oauth:
	    firewall_name: oauth_secured_area
	    http_client:
	        timeout:       5 # Time in seconds, after library will shutdown request, by default: 5
	        verify_peer:   false # Setting allowing you to turn off SSL verification, by default: true
	        ignore_errors: true # Setting allowing you to easier debug request errors, by default: true
	        max_redirects: 5 # Number of HTTP redirection request after which library will shutdown request,
	                         # by default: 5
	    resource_owners:
	        p4s.login:
	            type:                oauth2
	            client_id:           123456abcdef
	            client_secret:       apppass1
	            access_token_url:    %p4s_path%api/token
	            authorization_url:   %p4s_path%api/login
	            infos_url:           %p4s_path%api/profile
	            scope:               "read"
	            user_response_class: HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse
	            paths:
	                identifier: id
	                nickname:   username
	                realname:   fullname

The parameter %p4s_path% can be defined defined in parameters.yml and should contain the P4S URL: http://p4s.trialog.com.

You should also configure your firewall in security.tml

	security:
	    encoders:
	        Amisure\P4SApiBundle\Entity\User\SessionUser:
	            algorithm:   sha1
	            iterations: 1
	            encode_as_base64: false
	
	    role_hierarchy:
	        ROLE_ADMIN:       ROLE_USER, ROLE_BENEFICIARY, ROLE_ORG_USER, ROLE_ORG_ADMIN_USER
	        ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH, BENEFICIARY, ROLE_ORG_USER, ROLE_ORG_ADMIN_USER]
	
	    providers:
	        chain_provider:
	            chain:
	                providers: [user_db]
	        user_db:
	            entity: { class: Amisure\P4SApiBundle\Entity\User\SessionUser, property: username }
	        my_custom_hwi_provider:
	            id: ib_user.oauth_user_provider
	
	    firewalls:
	        oauth_secured_area:
	            anonymous: true
	            logout: ~
	            oauth:
	                resource_owners:
	                    p4s.login: "/oauth/login/check-p4s"
	                login_path: /home
	                check_path: /oauth/login
	                failure_path: /oauth/login
	                oauth_user_provider:
	                    service: ib_user.oauth_user_provider
	                
	    access_control:
	        - { path: ^/home, roles: IS_AUTHENTICATED_ANONYMOUSLY }
	        - { path: ^/aide, roles: IS_AUTHENTICATED_ANONYMOUSLY }
	        - { path: ^/oauth/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
	        - { path: ^/oauth/connect, role: IS_AUTHENTICATED_ANONYMOUSLY }
	        - { path: ^/, roles: IS_AUTHENTICATED_FULLY }
	        
Then, you need to complete your routs in your routing.yml file:

	login_p4s:
	    pattern: /oauth/login/check-p4s
	logout:
	    pattern:   /logout

Work in progress
---------------------
### Current tasks
* [███▒ 75%] Link with the P4S external API - client version
	* Beneficiary: ok v1
	* Organization: ok v1
	* OrganizationUser: ok v1
	* Event: ok v1
	* Evaluation: ok v1
	* LiaisonBook
	* Document
	* Send message
* [████ 95%] Link with P4S external API - authentication (using OAuth)
* [▒▒▒▒  0%] Link this library to the future "trialog/php-p4s-api" (Symfony2 unaware)
* [▒▒▒▒  5%] Configure a unit tests engine
* [▒▒▒▒  0%] Add unit tests

### Known bugs
Si aucune activité n'a eu lieu avec le P4S durant 1h, alors toutes tentatives de connexion du service ou d'appel au P4S (si l'utilisateur est déjà connecté au service) sont vouées à l'échec. La solution consiste à manuellement se déconnecter du P4S (bouton "Déconnexion" en haut à droite sur l'IHM du P4S), puis de reconnecter le service au P4S (bouton "Se connecter via le P4S" sur l'IHM du service)

License
---------------------
This software is the property of TRIALOG (http://www.trialog.com).

* It is using the Symfony 2 framework under the MIT license. For more information, see the LICENSE_Symfony2 file.

