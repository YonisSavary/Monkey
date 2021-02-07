<section class="content-section main-section" id="m_views">
    <h1>Views</h1>
    <p>
        PHP is already a template engine, so we didn't put one more,
        Monkey one class that may help you to do simples templates
    </p>
    <p>
        Assuming that we created a file named <code>app/views/home.php</code>, we can 
        render it by calling :
    </p>
<pre>
use Monkey\Web\Renderer;
Renderer::render("home");
</pre>
    <section class="info-section">
        This function act in a recursive way, so you can move your templates
        into subfolders it won't be a problem
    </section>
    <h2>Little helpers (Rendering Functions)</h2>
    <p>
        Depsite PHP being a template engine, we added some functions to help you out 
        while making your templates
    </p>
<pre>
// Add your app url prefix to the first parameter
// "app_prefix" in monkey.ini

&lt;?=php url("assets/someExample/app.css") ?&gt;

// Can render a template inside another
// For example, every sections of the documentations
// are separated into multiples files

&lt;?=php include_file("another/file") ?&gt;

// This function can find the path to a route if it 
// has a name, if no route was found, "/loginPage" would be return in this example

&lt;?=php router("loginPage") ?&gt;
</pre>
</section>