# Monkey Routes

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