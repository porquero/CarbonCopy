CarbonCopy
===========

CarbonCopy is a collaborative communication manager based in contexts, using the basis of communication and user defined structures:

![Context Structure](https://raw.githubusercontent.com/porquero/CarbonCopy/master/pub/readme/context-structure.png)

It is posible to create any communication environment using own structures: Projects, Knowledge Managment, Corporate Forum,
 Wiki, Historical Success, Tracking Project, etc.


Overview
------------

Each enterprise or team has their own communication culture, and CarbonCopy was developed to be adaptate to
that culture.

In CarbonCopy the communication enviroment is based on participants, contexts, topics and interaction; they are the pillars of
the system. And the purpose of this project is that teams use this system of communication instead of others, for
example email.


Features
-----------

* English/Spanish Interface language.
* Timeline information organized. It is possible to see the information timeline in every account, context and user.
* It supports multiple contexts hierarchically.
* It is possible to personalize the context/topics labels to use team definitions and concepts.
* It is 99% NoSQL.
* Top calendar for due dates.
* You can extend application with components and sections.

Configuration
-----------------

CarbonCopy is developed using CodeIgniter 2.2.0 PHP Framework and wiredesignz HMVC.

* Modify file application/config/config.php to set Base Site URL.
* Modify file application/config/database.php to set database conection settings.
* Make sure that _accounts/cc and application/logs is writeable recursively.
* If rewrite engine is not use, disable it in .htaccess.


Setup
-------

* Create database using name used in database.php
* Go to setup in the browser (http://installation-path/setup) and fill setup form with the first user account.
* Delete setup directory.