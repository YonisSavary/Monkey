# Controllers Syntax

Here is an example of a very simple controller 

<pre>
<?php 

namespace Controllers;

class SomeName {
    public function someFunction() {
        return Response::html("Hello there !");
    }
}
</pre>


# Middlewares Syntax

Here is an example of a very simple Middlewares,
which is quite similar to the controllers one 
(only the required namespace changes)

<pre>

<?php 

namespace Middlewares;

class SomeName {
    public function someFunction(Request $req) {
        if ($req->path !== "/thesecretpath") {
            Router::redirect("/toobad");
        }
    }
}

</pre>



# Models Syntax

Here is an example of a very simple model

<pre>

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

</pre>




# Monkey Views

Views are classical php files that you can call with the
`Renderer` component

As PHP is already a templating tool, quite simple but it 
still do the trick for simple uses, you can have something 
like this

Monkey's philosophy is to stay quite simple, BUT, you can
still add a renderer component with composer if you wish so

<ul>
    <?php for ($i=0; $i<5; $i++) { ?>
        <li>Some Example : <?= $i ?></li>
    <?php } ?>
</ul>

&lt;ul&gt;
    &lt;?php for ($i=0; $i&lt;5; $i++) { ?&gt;
        &lt;li&gt;Some Example : &lt;?= $i ?&gt;&lt;/li&gt;
    &lt;?php } ?&gt;
&lt;/ul&gt;