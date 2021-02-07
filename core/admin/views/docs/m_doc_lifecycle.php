<section class="content-section main-section" id="m_lifecycle">
    <h1>Request Lifecycle</h1>
    <p>
        Here is how a request is processed by Monkey :
    </p>
    <ol>
        <li>A Request comes to `index.php`</li>
        <li><code>index.php</code> initialize Monkey components and call the router</li>
        <li><code>Monkey\Router</code> go through all your routes and compare the request path</li>
        <li>If found, the middlewares and callback are called with a <code>Monkey\Web\Request</code> object argument
        <br>If not found : <code>Trash</code> is called to display an error</li>
        <li>If the callback return a <code>Response</code> object, its content is displayed</li>
    </ol>
</section>