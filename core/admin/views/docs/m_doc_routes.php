<section class="content-section main-section" id="m_routing">
    <h1>Routing</h1>
    <p>
        Monkey routes are stored in serrialized object with the <code>Monkey\Register</code> class,
        so it is pretty tricky to edit these by hand, that's why Monkey has a <a href="<?= router("m_route"); ?>">routing interface</a>
        <br>
        A route is defined by 2 mandatory and 3 optionnals properties :
        <ul>
            <li>path : the url to your route</li>
            <li>callback : the function to call (format : "controllerName->methodName")</li>
            <li>name (optionnal) : a name for your route, useful when templating</li>
            <li>methods (optionnal) : allowed HTTP methods (GET, POSTS, PUT ...)</li>
            <li>middlewares (optionnal) : names of the middlewares classes to call</li>
        </ul>
    </p>
    <section class="info-section">
        Note : When a route callback is called, a <code>Monkey\Web\Request</code> object is given in parameter,
        containing most of the request informations
        <br>
        PS : Also, if a request don't respect a route methods, it is skipped and don't raise any error, with that 
        you can define multiples routes with the same path but with differents methods
    </section>
    <h2>Slugs</h2>
    <p>
        You can define slugs in your route path : here is an example
    </p>
<pre>
// Assuming your route path is "/person/{firstname}/{lastname}"
// And your request path is    "/person/dwight/schrute"
// You can access it by doing it 

// This function is in a controller
function someName(Request $req)
{
    $req->slugs["firstname"] // return "dwight"
    $req->slugs["lastname"]  // return "schrute"
}
</pre>
    <h2>Middlewares</h2>
    <p>
        A middleware is just a class in the <code>Middlewares</code> namespace, having a 
        <code>handle</code> function, when a middleware is in a routes middlewares list, its <code>handle</code> function 
        is called with the current <code>Monkey\Web\Request</code> as first parameter
    </p>
    <h2>The Router Component</h2>
    <p>
        With this feature comes the <code>Monkey\Router</code> component, which have theses functions
    </p>
<pre>
// Redirect the current request
Router::redirect("/somePath");

// Save the currents routes with the Register
Router::save();

// Initialize the component
// Read the routes in the registers
// And add the admin interface routes if the feature is enabled
Router::init();

// Get a path regex for a path 
// Useful when a route path has slugs 
Router::get_regex("/somePath/{someSlugs}");

// Given parameters, return a route object
Router::get_route();

// Add a temporary route, which is not saved 
// in the register
Router::add_temp();

// Add a route and save it
Router::add();

// Remove a route, by its name or path
Router::remove("/somePath");
Router::remove("orAName");

// Given a route path and a request path  
// this function return an array containing 
// the slugs names and their values
Router::build_slugs();

// Route the current HTTP request
Router::route_current();

</pre>
</section>