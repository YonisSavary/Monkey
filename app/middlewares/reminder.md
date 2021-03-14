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