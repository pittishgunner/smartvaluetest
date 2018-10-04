**Smartvalue Practical Test for Alex M**


**Task 1.**
"Create a database wrapper that would allow another developer to run queries easily "

In any controller or even views one can easily put

`$this->db->t('table')->getRows(["column"=>"value"])`

and `t('table')` doesn't need to be reused unless the table changes.

the database wrapper has a few methods to be easily used for other types of queries too:

![alt text](http://smartvalue.expcity.info/img/c1.jpg)


**Task 2.**
 "Create a server-side application that implements the HTTP JSON-RPC protocol* that provides the possibility of fetching the Country Prefix and Country Name based on the Country Code"
  
 The server runs at /api/locations foi this specific request and a single method is currently in use "getCountriesByCode", more api endpoints can easily be added by implementing the /src/JsonRpc/Executor interface
 
 **Task 3.**
 "Make changes to `location_countries` table to improve it"
 
 use sql file in /data/improve.sql
 
 for the purpose of demonstrating the improvement we have 2 public urls 
 
 /site/dbtest?db=dborig
 /site/dbtest?db=db
 
 the results speak for themselves
 ![alt text](http://smartvalue.expcity.info/img/c2.jpg)
 
 the sql dumps for both databases are also included in the /data/ folder
 
 **Task 4.**
 "Create the client side portion of the JSON-RPC protocol that will request data from the server side script you just wrote for a Country Code"
 
 client side is done simple jQuery post call inside the /webroot/js/site.js file
 
 
 **Task 5.**
 "Provide a form where a user can enter a Country Code in a field, and gets the Country Prefix and Name "
 
 form is provided in the root of the app "/"
  ![alt text](http://smartvalue.expcity.info/img/c3.jpg)
  
  
 **Nice to have**
 
 **1.** 
 Extensive unit testing is provided for the /src/JsonRpc/Server.php class and somewhat less extensive (just a few important tests) for the /src/Db/db.php class.
 tests are found in the /tests/ folder
 
 **2.**
 Let's consider this readme.md a short technical document :) also, there are a few documented pieces of code (and also the improvement.sql file) as I felt they needed it.
 
 **Other notes**
 - Since using /api.php url's would look really really bad, I've provided a very basic Controller/View system to take care of the url routing and separation of code. Also a very simple template system for the views.
 - the sql can be further improved depending on actual use of said table but I feel I've shown the most important part of that improvement

 
  **Installation**
 1. place all files on webserver
 2. point a domain/subdomain/virtual host to the /webroot/ folder (the reason for this is security, eg: a compromised system won't be able to access files that are higher up in the hierarchy)
 3. run composer update (only phpunit is installed by composer)
 4. change values insider the /config.php file to match your database options
 
 
 **TODO**
 - develop the "M" in MVC as well on the back of this database wrapper
 - improve the "VC" in MVC to be able to use different layouts, add scripts and css files dynamically
 - use the whole project in any future light-weight apps I may need :) 
 - improve the JsonRpc Server, and create a server based client as well.
 - use above in bigger projects 