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