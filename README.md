AMISURE - P4S API
========================

[![Latest Stable Version](https://poser.pugx.org/trialog/p4s-api-bundle/v/stable.png)](https://packagist.org/packages/trialog/p4s-api-bundle)

La plateforme P4S du projet de recherche français AMISURE (http://www.amisure.fr) offre une API REST.
Cette librairie fournit un bundle Symfony2 permettant une intégration facilitée avec cette API. Plus besoin de se soucier de l'API REST bas niveau, il est possible de manipuler directement des objets PHP.

Installation
--------------------------------

Use Composer to download and install all external libraries used by this software.

	php composer.phar install

That's it!

Usage
--------------------------------

To be completed...

Work in progress
--------------------------------
### Current tasks
* [█▒▒▒ 25%] Link with the P4S external API - client version
	* Beneficiary: ok v1
	* Organization: ok v1
	* OrganizationUser: ok v1
	* LiaisonBook
	* Event
	* Evaluation: in progress
	* Document
	* Send message
* [████ 95%] Link with P4S external API - authentication (using OAuth)
* [▒▒▒▒  0%] Link this library to the future "trialog/php-p4s-api" (Symfony2 unaware)
* [▒▒▒▒  5%] Configure a unit tests engine
* [▒▒▒▒  0%] Add unit tests

### Future tasks

### Known bugs
* Si aucune activité n'a eu lieu avec le P4S durant 1h, alors toutes tentatives de connexion du service ou d'appel au P4S (si l'utilisateur est déjà connecté au service) sont voués à l'échec.
	* La solution consiste à manuellement se déconnecter du P4S (bouton "Déconnexion" en haut à droite sur l'IHM du P4S), puis de reconnecter le service au P4S (bouton "Se connecter via le P4S" sur l'IHM du service)

License
--------------------------------
This software is the property of TRIALOG.

* It is using the Symfony 2 framework under the MIT license. For more information, see the LICENSE_Symfony2 file.

