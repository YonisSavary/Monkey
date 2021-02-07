<section class="main-section content-section" id="m_auth">
    <h1>Service : Authentication</h1>
    <p>
        Monkey include a simple authentication class
    </p>
    <h2>Configuration</h2>
    <p>
        <code>Monkey\Services\Auth</code> need 4 keys to be configured in 
        your <code>monkey.ini</code> file :
    </p>
    <table>
        <tr>
            <td><code>auth_enabled</code></td>
            <td>Is the auth service enabled ?</td>
        </tr>
        <tr>
            <td><code>auth_model</code></td>
            <td>Model class name for users</td>
        </tr>
        <tr>
            <td><code>auth_login_field</code></td>
            <td>Unique field used to authenticate users (can be email, login, phone...etc)</td>
        </tr>
        <tr>
            <td><code>auth_pass_field</code></td>
            <td>field name where password are stored</td>
        </tr>
    </table>
    <h2>Usage</h2>
    <p>
        <code>Auth</code> usage is meant to be simple 
    </p>
<pre>
use Monkey\Services\Auth;

// Can create a new password (BCRYPT with a cost of 8 by default)
Auth::create_password();

// Check if the password of "admin" is "somePassword" in your database
// and return the result
Auth::check("admin", "somePassword");

// Try to connect as "admin" using "somePassword", return true if the 
// authentication was successful
Auth::attempt("admin", "somePassword");

// Directly log a user with its model object
Auth::login($userObject);

// Logout a user if one is actually authenticated
Auth::logout();

// Is the current client authenticated ?
Auth::is_logged();

// Get the Model object stored in the SESSION
Auth::get_user();
</pre>
    <h3>Token</h3>
    <p>
        When a user is logged, a 64-random-characters string is created in the session, 
        and can be recovered with
    </p>
<pre>
use Monkey\Services\Auth;
Auth::token();

// Can give : 272143223ac0bd8a2448e2c0bf7143e0f1580a9f3e23823c80df2e3a4423f5b4
// for example

</pre>
</section>