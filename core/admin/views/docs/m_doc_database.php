<section class="content-section main-section" id="m_database">
    <h1>Database</h1>
    <p>
        There are several keys that need to be edited in <code>monkey.ini</code> :
        <table>
            <tr>
                <th>Key</th>
                <th>Role</th>
            </tr>
            <tr>
                <td>db_enabled</td>
                <td>Is the service enabled ?</td>
            </tr>
            <tr>
                <td>db_driver</td>
                <td>driver name for <code>PDO</code></td>
            </tr>
            <tr>
                <td>db_host</td>
                <td>IP of the distant machine</td>
            </tr>
            <tr>
                <td>db_port</td>
                <td>Port of the db service (usually 3306)</td>
            </tr>
            <tr>
                <td>db_name</td>
                <td>Database name</td>
            </tr>
            <tr>
                <td>db_user</td>
                <td>Login to log with</td>
            </tr>
            <tr>
                <td>db_pass</td>
                <td>Password for the user</td>
            </tr>
        </table>
    </p>
    <p>
        The composant to prepare and execute query is <code>Monkey\DB</code> :
    </p>
<pre>
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
// Note: you can specify the fetch mode for PDO
DB::query(string $query, int $mode=PDO::FETCH_ASSOC);
</pre>
</section>