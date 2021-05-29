# Monkey | Light MVC Framework for PHP 🐒 

Docs are here, [just Here](http://monkey-docs.net) !

Hello there ! Welcome to the Monkey project, a framework written in PHP
made to simplify every tasks in a website creation

## Requirement

- PHP 8.X
- That's it

## Features 🔩

- Routing + Slugs
- Models
- Controllers
- Middlewares
- DB Connection (using PDO)
- Authentification
- Automatic CRUD API for your models
- Application Modularity 


## Monkey philosophy

The goal is to have a framework that contain every must-have features
for a MVC framework, while staying simple and quite instinctive to use


## Component structure

Monkey was made with "components", A.K.A classes that concern only a 
field/features (configuration, routing... etc) while having most of 
their function statical, so you don't have to declare an object everytimes
you want to interact with a framework features (they are, of course, obvious exceptions
like models instances that are made to be objects)