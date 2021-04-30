# Monkey Reminder

This file contains some reminders for you !

## Controllers Syntax

**Controllers are stored in ./controllers**

Here is an example of a very simple controller 

```php
<?php 

namespace Controllers;

class SomeName {
    public function someFunction() {
        return Response::html("Hello there !");
    }
}
```










## Middlewares Syntax

**Middlewares are stored in ./middlewares**

Here is an example of a very simple Middlewares,
which is quite similar to the controllers one 
(only the required namespace changes)

```php
<?php 

namespace Middlewares;

class SomeName {
    public function someFunction(Request $req) {
        if ($req->path !== "/thesecretpath") {
            Router::redirect("/toobad");
        }
    }
}

```











## Models Syntax

**Models are stored in ./models**

Here is an example of a very simple model

```php
<?php 

namespace Models;

/* 
 * Note : If your table doesn't have a primary key
 * you can replace it by any field name that is unique
 * so far, it is used only for delete queries
 *
 * Protected members are used internally by Monkey
 * Public fields are fetched and parsed when using it
 */
class UserOrSomething {
    protected $table = "user";
    protected $primary_key = "id"; 
    public $username;
    public $password;
    public $last_login;
}

```











## Monkey Views

**Views are stored in ./views**

Views are classical php files that you can call with the
`Renderer` component

As PHP is already a templating tool, quite simple but it 
still do the trick for simple uses, you can have something 
like this

Monkey's philosophy is to stay quite simple, BUT, you can
still add a renderer component with composer if you wish so

```php
<ul>
    <?php for ($i=0; $i<5; $i++) { ?>
        <li>Some Example : <?= $i ?></li>
    <?php } ?>
</ul>
```

## Inserting others php files

If you want to have other php files in your php
(like a module used in a app-wide context), you 
can add them in a folder named "others", `app_loader.php`
will detect them (recursively) and include them