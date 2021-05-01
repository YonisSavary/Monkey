# Monkey Cache

This directory is used by the `Register` component 
as a cache storage.
If you want to use another directory, you can simply edit 
`register_store` in `monkey.json`

# Monkey Routes

You can define your routes in a file named `routes.json`
Here is a full-example for a valid route 

{
    "path": "/someExample",
    "callback": "ExampleController->someMethod",
    "name": "TheExampleRoute",
    "methods": ["PUT", "POST"] ,
    "middlewares": ["middlewareClass1", "middlewareClass2"]
}

**Important ! `name`, `methods` and `middlewares` are optionnal !**
**Only `path` and `callback` are essentials**