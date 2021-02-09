<?= include_file("m_head") ?>
    <section>
        <section class="f-row align-center">
            <h1>Configured Routes</h1>
            <div class="f-filler"></div>
            <button onclick="fetchRoutes()" class="button blue">Refresh</button>
        </section>
        <section class="f-row align-center mb-1">
            <strong>Route Path</strong>
            <section class="f-filler"></section>
            <strong>Route Callback</strong>
        </section>
        <section id="routesSlot">

        </section>
    </section>

    <section class="main-section">
        <h1>Add a Route</h1>
        <section class="form-group">
            <label for="path">Path</label>
            <input type="text" id="path">
        </section>
        <section class="form-group">
            <label for="callback">Callback</label>
            <input type="text" placeholder="controllerName->methodName" id="callback">
        </section>
        <section class="form-group">
            <label for="name">Name (optionnal)</label>
            <input type="text" id="name">
        </section>
        <section class="form-group">
            <label for="middlewares">Middlewares (optionnal)</label>
            <input type="text" placeholder="middleware1, middleware2..." id="middlewares">
        </section>
        <section class="form-group">
            <label for="methods">Methods (optionnal)</label>
            <input type="text" placeholder="POST, GET, PUT..." id="methods">
        </section>
        <section class="form-group">
            <button id="addButton" class="button green">Add</button>
        </section>
    </section>

    <section class="main-section">
        <h1>Import/Export Routes</h1>
        <p>
            If you don't want to create routes with your bare-hand, 
            or you have a lot of similar routes, you can import of export them
            in JSON
        </p>
        <p>
            Here is an example of what the results should look like :
        </p>
<pre>
[
    {
        "path": "/routepath",
        "callback": "controller->method",
        "name": "someName",
        "methods" : ["GET", "POST"],
        "middlewares" : ["m1", "m2"]
    },
    ...
]
</pre>
        <p class="info-section">
            Note : <code>name</code>, <code>methods</code> and <code>middlewares</code> are still optionnal
        </p>
        <h2>Import</h2>
            <p class="warning-section">
                This function check if sent routes already exists, existing routes are not replaced !
            </p>
            <form action="<?= router("m_api_route_import")."?password=".Monkey\Config::get("admin_password") ?>" enctype="multipart/form-data" method="POST" class="f-col">
                <input id="fileinput" type="file" name="routesfiles" id="">
                <label for="fileinput" class="button green">Choose a file</label>
                <input type="submit" value="Send" class="button green">
            </form>
        <h2>Export</h2>
        <a href="<?= router("m_api_route_export")."?password=".Monkey\Config::get("admin_password") ?>" class="button green">Download</a>
    </section>











    <section class="overlay" id="editor">
        <section class="container">
            <section class="f-row align-center">
                <h1>Edit a Route</h1>
                <div class="f-filler"></div>
                <button id="editor_deletebutton" class="button red">Delete</button>
            </section>
            <section class="form-group">
                <label for="editor_path">Path</label>
                <input type="text" id="editor_path">
            </section>
            <section class="form-group">
                <label for="editor_callback">Callback</label>
                <input type="text" id="editor_callback">
            </section>
            <section class="form-group">
                <label for="editor_name">Name (optionnal)</label>
                <input type="text" id="editor_name">
            </section>
            <section class="form-group">
                <label for="editor_middlewares">Middlewares (optionnal)</label>
                <input type="text" placeholder="middleware1, middleware2..." id="editor_middlewares">
            </section>
            <section class="form-group">
                <label for="editor_methods">Methods (optionnal)</label>
                <input type="text" placeholder="POST, GET, PUT..." id="editor_methods">
            </section>
            <section class="spaced f-row align-center">
                <div class="f-filler"></div>
                <button id="editor_cancelButton" class="button">Cancel</button>
                <button id="editor_addButton" class="button green ml-1">Save</button>
            </section>
        </section>
    </section>
    <script src="<?= url("admin/js/m_api_route.js")?>"></script>
<?= include_file("m_footer") ?>