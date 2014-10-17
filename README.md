CarbonCopy
===========

CarbonCopy is a collaboration manager based in contexts using the basis of communication.


Overview
------------

Each enterprise or team has their own communication culture, and CarbonCopy was developed to adaptate to
that culture.

In CarbonCopy all communication enviroment is based on participants, contexts, topics and interaction; they are the pillar of
the system.


Features
-----------

* Timeline organized information. Is posible see account, context and user timeline.
* Support n contexts.
* Is posible personalize context/topics labels.
* Support multiple accounts.


Configuration
-----------------

CarbonCopy is developed using CodeIgniter 2.2.0 PHP Framework and wiredesignz HMVC.

* Modify file application/config.php:17 to set Base Site URL.
* Modify file application/database.php to set database conection settings.
* Make sure that _accounts/cc and application/logs is writeable recursively.
* If don't use rewrite engine disable in .htaccess:5.

Setup
-------

* Create database using name used in database.php
* Go to setup in the browser (http://installation-path/setup) and fill setup form with the firt user account.
* Delete setup directory.