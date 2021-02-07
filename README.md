# Monkey | Light MVC Framework for PHP

## Hello there !

Monkey is a brand new Framework for PHP with basics features and syntaxes

## Web Interface

Monkey is meant to be simple to use/configure, so, it has it own little administration interface where you can :
* Read the Monkey documentation
* Change your Monkey app configuration
* Fetch and delete models from your application
* Create/edit/delete routes (but also export/import them) 

(And yes, you can change the administration url prefix easily in your configuration)

## Where's the documentation ?

To access Monkey's documentation, you need to clone this repository and start a development PHP server 

<pre>
cd public
php -S localhost:3000
</pre>

and then go to `localhost:3000/admin/documentation` and tada ! Everything's here !

## How's made the folder hierarchy ?

Monkey try to be the simplest possible, an empty Monkey projet contains :
* `app` : your MVC files
* `config` : your serialized config objects
* `app` : core, pretty explicit (Monkey core + admin interface)
* `public` : public directory with `index.php` and the assets directory

## Is Composer available ?

if a `vendor/autoload.php` exists, monkey will load it directly, so yes 

*Also, Monkey was made from scratch*