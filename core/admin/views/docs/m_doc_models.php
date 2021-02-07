<section class="content-section main-section" id="m_models">
    <h1>Models</h1>
    <h2>The Query class </h2>
    <p>
        <code>Monkey\Query</code> was made to be a SQL query builder class, it usage is pretty simple, it can handle 
        4 types of queries : <code>INSERT</code>, <code>SELECT</code>, <code>UPDATE</code>, <code>DELETE</code>, here are 
        some examples :
    </p>
    <section class="info-section">
        Query modes constants are made on a CRUD name system, so :
        <ul>
            <li><code>Query::CREATE</code> is an <code>INSERT</code> query</li>
            <li><code>Query::READ</code> is an <code>SELECT</code> query</li>
            <li><code>Query::UPDATE</code> is an <code>UPDATE</code> query</li>
            <li><code>Query::DELETE</code> is an <code>DELETE FROM</code> query</li>
        </ul>
    </section>
<pre>
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
</pre>
    <section class="info-section">
        If you don't want to execute the query, you can simply call <code>build</code>, to retrieve
        it
    </section>

    <h2>Precisions</h2>
    <p>
        You may have noticed, on the last query, we used the <code>and</code> function, this one, 
        and the <code>or</code> are made to make your query more readable, so you can add multiples
        conditions more easily
    </p>
<pre>
$q->where("firstname", "boo")->or()->where("name", "barrr");
</pre>

    <h2>Models</h2>

    <strong>
    Monkey philosophy on the models is : your application shouldn't define what your database has to look like, 
    the purpose of models is to have a structural copy of your tables
    </strong>

    <p>Here is an example of model :</p>
<pre>
&lt;?php

namespace Models;

use Monkey\Model;

class Users extends Model {
    protected $table="users";
    protected $primary_key="id";
    public $id;
    public $name;
}
</pre>
    <p>
        The structure is simple, every public fields of your class defines your model fields,
        and the others are used in it internal process.
    </p>
    <p>
        The abstract class <code>Monkey\Model</code> has a few functions linked to the <code>Monkey\Query</code> ones :
    </p>
<pre>
$modelObject = new User();
$modelObject->get("id", "name");
$modelObject->getAll();
$modelObject->update();
$modelObject->insert();
$modelObject->delete();
</pre>
    <p>
        All of theses functions return a Query object pre-filled with your model informations, also when you are creating 
        your model object, the <code>Monkey\Model</code> create a <code>Monkey\ModelParser</code> object, which is used to 
        parse the results objects.
    </p>
    <section class="warning-section">
        Note : the monkey models are still in progress
    </section>
</section>