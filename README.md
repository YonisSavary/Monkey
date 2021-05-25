# Monkey | Light MVC Framework 

## Hello There !

This page will teach you how to use Monkey and its components,
don't worry, it is made to be simple !

**Thanks For Trying Monkey**










# Summary
 * Request Lifecycle
 * Configuration
 * Register
 * Routing
 * Database
 * Models
 * Controllers
 * Views
 * Authentication











# Monkey Hierarchy

Let's explains how a Monkey project look like, the file hierarchy was made 
to be the simplest possible so we can't be lost : 

## Directories

 * app : your MVC application, with models/views/controllers and others PHP files you want to load
 * cache : the hold-all of json files except the framework configuration
 * core : Monkey core files, which are meant to be simples and well-documented, it is made to let you edit features at will 
 * public : Public PHP directory, supposed to host `index.php` and your assets



## Namespaces
Here are the differents namespaces you may see/use, I won't explain the explicits ones :

| Namespace | Purpose |
|-----------|---------|
| Models\ | **Your** Models |
| Controllers\ | **Your** Controllers |
| Middlewares\ | **Your** Middlewares |
| Monkey\Framework | Some internal components for Monkey (like the `Router`, `AppLoader`...)  | 
| Monkey\Services\ | So far, only the `Auth` component is in this namespace | 
| Monkey\Web\ | Everything that touch to the Http Requests/Responses | 
| Monkey\Dist\ | `DB` and `Query` components | 
| Monkey\Model\ | `Model` and `ModelParser` components | 








# Request Lifecycle
Here is how a request is processed by Monkey :

 1. A Request comes to `index.php`
 2. `index.php` initialize Monkey components and call the router
 3. `Monkey\Framework\Router` go through all your routes and compare the request URI
 4. If found, the middlewares and callback are called with a `Monkey\Web\Request` object as argument, ff not found : `Trash` is called to display an error
 5. If a callback or the controller return a `Monkey\Web\Response` object, its content is displayed and the request killed
 







# Configuration

## How to configure Monkey ?

The framework configuration is stored in `monkey.json`
and read by the `Monkey\Storage\Config` component,
here are some functions you may find interesting


```php
// Read a file and put its content into the configuration
Config::read_file("someConfig.json"):

// Basics function, you can set and get specifics keys 
// from your configuration

Config::set("foo", "no");

Config::get("foo"); // no default value
Config::get("foo", "toto"); // "toto" if the "foo" key doesn't exists

// How to check if a key is present in your configuration
// You can also check for multiples keys

Config::exists("foo");
Config::multiple_exists(["foo", "bar"]);


// This line is automatically called in monkey.php
// But you can call it to get the file configuration without
// saving it in the component storage

Config::init();
$configuration = Config::init(true);
```


Note : The Configuration is stored in `$GLOBALS["monkey"]["config"]`

## List of all configurable framework keys
|Key Name | Purpose | Default (if any) |
|---------|---------|------------------|
| `register_store` | Directory where `Monkey\Storage\Register` store its files | "./config" |
| `app_directories` | Directory where Monkey look for app files | "./app" |
| `cached_apploader` | Does the apploader cache its directories with `Monkey\Storage\Register` not advised in dev environment (can save you some times) | false |
| `db_enabled` | Do Monkey create a connection to a database ? | false |
| `db_driver` | Driver used by PDO |  |
| `db_host` | Host of the database |  |
| `db_port` | DB Service port (usually 3306) |  |
| `db_name` | Database name |  |
| `db_user` | Database login |  |
| `db_pass` | Database user password |  |
| `db_file` | Database file-name (only for sqlite driver) |  |
| `app_url_prefix` | URL prefix (pretty useful for assets files url) |  |




## Organize your configuration !

If you want to split your configuration file, you can create
a file named `monkey.json` in your **application folder** 

By default, you can create a file as `./app/monkey.json` it will 
be read by the `Monkey\Framework\AppLoader` component and treated as more important than the initial configuration file
    

    
## Organize your application ! 

Monkey Allow you to have multiples MVC applications connected to the framework !
Let's assume that we have two directories at the project root, let's call them 
`app_users` and `app_admin`, both of them have theirs MVC 
directories, just like they were 2 separated apps.

You can combine them by editing `app_directories` in `monkey.json`

```json
"app_directories" : ["./app_users", "./app_admin"]
```

Notes : 
 * Views Directories are shared, one application can access the views of another, 
 **be aware when naming your views !**
 * Change may not be visible at first, be sure to refresh your cache by either setting `cached_apploader` 
 to `false` in `monkey.json`, or by deleting `config/apploader.json` if you do not want to disable it
    










    
# Register

The Monkey register is here to store your data and retrieve it quickly, to summarize it :
it's an interface with JSON objects stored in files, for example : Monkey use 
the register to store your application's routes

Most of the register functions are similar to the `Monkey\Storage\Config` one

```php
// Set the foo key to the given array and save a .json file
Register::set("foo", ["bar"=>"blah"])

// Get the foo bar
Register::get("foo")

// Initialize the component
// Create the store directory if inexistant and load its content
Register::init()

// Write the content of the "foo" key into a ser file 
// Note : this function is automatically called by 'set'
Register::write("foo")

// Load the .json files into the register 
// Note : this function is automatically called by 'init'
Register::load_files()
```


Note : The directory where the .json files are stored can be edited by changing `register_store`
in monkey.json


















    
# Routing

Monkey routes are stored in `config/routes.json` by default, a route is defined by 2 mandatory and 3 optionnals properties :

 * path : the url to your route
 * callback : the route callback
 * name (optionnal) : a name for your route, useful when templating
 * methods (optionnal) : allowed HTTP methods (GET, POSTS, PUT ...)
 * middlewares (optionnal) : names of the middlewares classes to call


Here is an full-example 
```json
{
    "path": "/someExample",
    "callback": "ExampleController->someMethod",
    "name": "TheExampleRoute",
    "methods": ["PUT", "POST"] ,
    "middlewares": ["middlewareClass1", "middlewareClass2"]
}
```

You can also define them in your PHP files with `Router::add()` method

```php
// As said just before, only the path and callback are mandatory
Router::add("/someUrl", function(){...}, "route_name", ["Middle1", "Middle2"], ["GET"])

// Full String Syntax
Router::add("/someUrl", "SomeController->someFunction")

// Laravel-like syntax
Router::add("/someUrl", [SomeController::class, 'someFunction'])
```

**PS : Also, if a request don't respect a route methods, it is skipped and don't raise any error, with that 
you can define multiples routes with the same path but with differents allowed methods**

    
    
    
## Slugs

You can define slugs in your route path : here is an example

```php
// Assuming your route path is "/person/{firstname}/{lastname}"
// And your request path is    "/person/dwight/schrute"
// You can access it by doing it 

// This function is in a controller
function someName(Request $req)
{
    $req->slugs["firstname"] // return "dwight"
    $req->slugs["lastname"]  // return "schrute"
}
```
    
    
## Middlewares

A middleware is just a class in the `Middlewares` namespace, having a 
`handle` function, when a middleware is in a routes middlewares list, its `handle` function 
is called with the current `Monkey\Web\Request` as first parameter, it can either :

 * Return a Response that is instantly displayed
 * Redirect a request with `Router::redirect`
 * Do nothing when everything's right


    
    
## The Router Component

With this feature comes the `Monkey\Framework\Router` component, which have theses functions

```php
// Redirect the current request
Router::redirect("/somePath");

// Initialize the component
// Read the routes in the registers
// And add the admin interface routes if the feature is enabled
Router::init();

// Get a path regex for a path 
// Useful when a route path has slugs 
Router::get_regex("/somePath/{someSlugs}");

// Given parameters, return a route object
Router::get_route();

// Add a temporary route, which is not saved 
// in the register
Router::add();

// Remove a route, by its name or path
Router::remove("/somePath");
Router::remove("orAName");

// Given a route path and a request path  
// this function return an array containing 
// the slugs names and their values
Router::build_slugs();

// Route the current HTTP request
Router::route();
```
    

## Routing Groups

There are 3 Types of groups :
- path (URI prefixes)
- middlewares groups
- allowed methods groups

Description :
- path are the prefixes to add before new routes url
- middlewares are the middlewares or closure to execute before executing the controller callback
- methods are the allowed methods of your new routes (merged with the route methods)

Example :
```php
[
	"path" => ["api"],
	"middlewares" => ["AuthMiddleware" , "AnotherOne", function(){...}],
	"methods" => ["POST"]
]
```

With these groups, every new route :
- will have a "/api" prefix on their paths
- will need the execution of the "AuthMiddleware", "AnotherOne" and the Closure middlewares
- and can be accessed with the POST methods
   

You can disable one or multiples groups with `Router::end_groups([...])` or `Router::end_all_groups`

PS : If you want to, you can get the declared groups
with `Router::get_groups`










    
# Database

There are several keys that need to be edited in `monkey.json` :

| Key | Role |
|-----|------|
| db_enabled | Is the service enabled ? |
| db_driver | driver name for `PDO` |
| db_host | IP of the distant machine |
| db_port | Port of the db service (usually 3306) |
| db_name | Database name |
| db_user | Login to log with |
| db_pass | Password for the user |
| db_file | File path for database (sqlite driver) |


The composant to prepare and execute query is `Monkey\Dist\DB` :

```php
// Initialize the component, create a connection and throw 
// an error if something went wrong
DB::init();

// Return a bool to "are we connected to the database ?"
DB::check_connection();

// Prepare a query and store it in the DB component
DB::prepare(string $request);

// Bind a value on the stored query
// Note : the used function is "bindParam"
DB::bind(string $bind, mixed $value);

// Execute the prepared query and return the results 
// (or an empty array)
DB::execute();

// Execute the prepared query and return the results 
// (or an empty array)
// Note: you can give parameters to your query 
DB::query(string $query, ...$param);

// Get a new PDO object (using the configuration DSN by default)
DB::get_connection("admin", "pass");
// You can define a custom dsn if you want to, 
// Which is equivalent to directly creating a PDO Object
DB::get_connection("admin", "pass", "mysql:host=127.0.0.1;dbname=somedb");

// Get a DSN connection string built from 
// your configuration file
DB::get_dsn(); 

// Home-made function to prepare a query, it return a string
// but does not execute it directly
DB::quick_prepare("INSERT INTO ... VALUES ({}, {}, '{}')", [5, 'foo', 'bar']);
// <= INSERT INTO ... VALUES (5, 'foo', 'foo')
```
    























    
# Models
## The Query class 

`Monkey\Query` was made to be a SQL query builder class, it usage is pretty simple, it can handle 
4 types of queries : `INSERT`, `SELECT`, `UPDATE`, `DELETE`, here are 
some examples :

        
Query modes constants are made on a CRUD name system, so :

 * `Query::CREATE` is an `INSERT` query
 * `Query::READ` is an `SELECT` query
 * `Query::UPDATE` is an `UPDATE` query
 * `Query::DELETE` is an `DELETE FROM` query

        
```php
// Insert values in a table
$q = new Query("tableName", ["firstname", "name"], Query::CREATE);
$q->values("big", "chungus");
$results = $q->exec();

// Make a SELECT query 
// Note the query constructor have Query::READ as a default value for the 3rd parameter
$q = new Query("tableName", ["firstname","name"], Query::READ);
$q->where("firstname", "big");
$q->limit(1);
$results = $q->exec();

// Make a UPDATE query 
$q = new Query("tableName", [], Query::UPDATE);
$q->set("name", "boii");
$q->where("firstname", "big");
$q->exec();

// Make a DELETE query
$q = new Query("tableName", [], Query::DELETE);
$q->where("firstname", "big")->and()->where("name", "boii");
$q->exec();
```
        
If you don't want to execute the query, you can simply call `build`, to retrieve
it
        
    
    
## Precisions

You may have noticed, on the last query, we used the `and` function, this one, 
and the `or` are made to make your query more readable, so you can add multiples
conditions more easily

```php
$q->where("firstname", "boo")->or()->where("name", "barrr");
```
    
    
## Models

**
Monkey philosophy on the models is : your application shouldn't define what your database has to look like, 
the purpose of models is to have a structural copy of your tables
**

Here is an example of model :
```php
<?php

namespace Models;

use Monkey\Model\Model;

class Users extends Model {
    protected $table="users";
    protected $primary_key="id";
    public $id;
    public $name;
}
```

The structure is simple, every public fields of your class defines your model fields,
and the others are used in it internal methods.


The abstract class `Monkey\Model\Model` has a few functions linked to the `Monkey\Dist\Query` ones :

```php$modelObject = new User();
// Table method
$modelObject->get("id", "name");
$modelObject->get_all();
$modelObject->update();
$modelObject->insert();
$modelObject->delete_from();

// Object method 
$modelObject->save();
$modelObject->delete();
```

**Note : `delete_from` build a query to delete from a table, the `delete` method 
delete an object following it primary key**

All of theses functions return a Query object pre-filled with your model informations, also when you are creating 
your model object, the `Monkey\Model` create a `Monkey\ModelParser` object, which is used to 
parse the results objects.

        
    



















# Controllers

Controllers are simple, there is only one constraint, it has to be in the 
`Controllers` namespace, then you can create public functions and use them in your routes

Also, as a route function is called, a `Monkey\Web\Request` object is given a first parameter




















    
# Views

PHP is already a template engine, so we didn't put one more,
Monkey has one class that may help you to do render PHP templates


Assuming that we created a file named `app/views/subdirectory/home.php`, we can 
render it by calling :

```php
use Monkey\Web\Renderer;
Renderer::render("home");
// or
Renderer::render("subdirectory/home");
```
        
This function act in a recursive way, so you can move your templates
into subfolders it won't be a problem
        

## Example 

Let say we've created two files : `someTemplate.php` and `someController.php` :

`someTemplate.php` content :
```php
<h1> 
    <?= $title ?> 
</h1>
```

`someController.php` content :
```php
Renderer::render("someTemplate", ["title"=>"It works"]);
``` 

This example will display a `It works` title !
    
## Little helpers (Rendering Functions)

Depsite PHP being a template engine, we added some functions to help you out 
while making your templates

```php
// Add your app url prefix to the first parameter
// "app_url_prefix" in monkey.json
<?= url("assets/someExample/app.css") ?>

// Can render a template inside another
// For example, every sections of the documentations
// are separated into multiples files
<?= render("another/file") ?>
<?= render("another/file", ["someVariable" => [...]]) ?>

// This function can find the path to a route if it 
// has a name, if no route was found, "/loginPage" would be return in this example
<?= router("loginPage") ?>
```
    
    
## Passing Variables to Renderer
There is a quick way to pass variables to the `Renderer` class
```php
Renderer::render("home", ["app_name"=>"MonkeyExample"]);
```
You can access `app_name` with this instruction
```php
<?= $vars["app_name"] ?> // Will display "MonkeyExample"
```
    
**PS: The variable usage will evolve, the goal is 
to allow you to use a `$app_name` function for example**















    
# Authentication

Monkey include a simple authentication class

## Configuration

`Monkey\Services\Auth` need 4 keys to be configured in 
your `monkey.json` file :

| key | purpose |
|-----|---------|
| `auth_enabled` | Is the auth service enabled ? |
| `auth_model` | Model class name for users |
| `auth_login_field` | Unique field used to authenticate users (can be email, login, phone...etc) |
| `auth_pass_field` | field name where password are stored |
| `auth_salt_field` | field for salts (leave null to disable) |
  
    
## Usage

`Auth` usage is meant to be simple 

```php
use Monkey\Services\Auth;

// Return a hashed password (BCRYPT with a cost of 8 by default)
Auth::create_password("someNewPassword");

// Check if the password of "admin" is "somePassword" in your database
// and return the result
Auth::check("admin", "somePassword");

// Try to connect as "admin" using "somePassword", return true if the 
// authentication was successful
Auth::attempt("admin", "somePassword");

// Directly log a user with its model object
Auth::login($user_object);

// Logout a user if one is actually authenticated
Auth::logout();

// Is the current client authenticated ?
Auth::is_logged();

// Get the Model object stored in the SESSION
Auth::get_user();
```
    
    
## Token

When a user is logged, a 64-random-characters string is created in the session, 
and can be recovered with

```php
use Monkey\Services\Auth;

// Can give : 272143223ac0bd8a2448e2c0bf7143e0f1580a9f3e23823c80df2e3a4423f5b4 for example
Auth::token();
```






## Middleware 

By default, Monkey includes a little middleware that you can use/edit/delete, it 
is stored in `app/middlewares` and redirect to another path every un-authenticated client 
on a route

A middleware is a class that is in the `Middlewares` namespace, and have a `handle` function, which can take a `Request` object as first parameter, and can either return an edited request or a `Response` object (if a response is return, its content it directly displayed and the request killed)

Also, if you want to have a cleaner code, you can use the `\Monkey\Framework\Middleware`
interface, it force you to make the right function