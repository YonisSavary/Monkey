<section class="main-section content-section" id="m_config">
    <h1>Configuration</h1>
    <h2>How to configure Monkey ?</h2>
    <p>
        The framework configuration is stored in <code>monkey.ini</code>
        and read by the <code>Monkey\Config</code> component when <code>Config::init()</code> is called,
        here are some functions you may find interesting
    </p>
<pre>
// Basics function, you can set and get specifics keys 
// from your configuration

Config::set("foo", "no");
Config::get("foo"); // no

// Save the current configuration in monkey.ini
// You can define which key needs to be excluded

Config::save();
Config::save(["foo", "bar"])

// How to check if a key is present in your configuration
// You can also check for multiples keys

Config::exists("foo");
Config::multiple_exists(["foo", "bar"]);


// This line is automatically called in monkey.php
// But you can call it to get the file configuration without
// saving it in the component storage

Config::init();
$configuration = Config::init(true);
</pre>

    <h2>Discrete Configuration elements</h2>
    <p>
        Discretes elements can be stored in your configurations, they have the same purpose
        at the basics one, but they are temporary as they are not stored in <code>monkey.ini</code> file 
    </p>

<pre>
Config::exists_discrete("foo")
Config::set_discrete("foo", "yes")
Config::get_discrete("foo") // "yes"
</pre>

    <section class="info-section">
        Note : The Configuration is stored in <code>$GLOBALS["monkey"]["config"]</code>
    </section>

    <details>
        <summary>List of all configurable framework keys</summary>
        <section>
            <table>
                <tr>
                    <th>Key Name</th>
                    <th>Purpose</th>
                    <th>Default (if any)</th>
                </tr>

                <tr>
                    <td><code>register_store</code></td>
                    <td>Directory where <code>Monkey\Register</code> store its files</td>
                    <td>"./config"</td>
                </tr>
                <tr>
                    <td><code>app_directory</code></td>
                    <td>Directory where monkey look for app files</td>
                    <td>"./app"</td>
                </tr>
                <tr>
                    <td><code>cached_apploader</code></td>
                    <td>
                        Does the apploader cache its directories with <code>Monkey\Register</code>
                        not advised in dev environment (can save you some times)
                    </td>
                    <td>false</td>
                </tr>
                <tr>
                    <td><code>db_enabled</code></td>
                    <td>Do monkey connect itself to a database</td>
                    <td>false</td>
                </tr>
                <tr>
                    <td><code>db_driver</code></td>
                    <td>Driver used by PDO</td>
                    <td></td>
                </tr>
                <tr>
                    <td><code>db_host</code></td>
                    <td>Host of the database</td>
                    <td></td>
                </tr>
                <tr>
                    <td><code>db_port</code></td>
                    <td>DB Service port (usually 3306)</td>
                    <td></td>
                </tr>
                <tr>
                    <td><code>db_name</code></td>
                    <td>Database name</td>
                    <td></td>
                </tr>
                <tr>
                    <td><code>db_user</code></td>
                    <td>Database login</td>
                    <td></td>
                </tr>
                <tr>
                    <td><code>db_pass</code></td>
                    <td>Database user password</td>
                    <td></td>
                </tr>
                <tr>
                    <td><code>app_prefix</code></td>
                    <td>URL prefix</td>
                    <td></td>
                </tr>
                <tr>
                    <td><code>admin_enabled</code></td>
                    <td>Is the admin interface enabled ?</td>
                    <td>true</td>
                </tr>
                <tr>
                    <td><code>admin_password</code></td>
                    <td>Password for the admin CRUD API</td>
                    <td></td>
                </tr>
                <tr>
                    <td><code>admin_url_prefix</code></td>
                    <td>URL prefix for the admin interface</td>
                    <td>"/admin"</td>
                </tr>

            </table>
        </section>
    </details>

</section>