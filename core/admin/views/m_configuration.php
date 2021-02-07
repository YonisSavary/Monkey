<?= include_file("m_head") ?>
    <h1>Monkey Configuration</h1>
    
    <h2>Add a key</h2>
    <section class="f-col">
        <section class="form-group">
            <label for="newKey">Key</label>
            <input type="text" id="newKey">
        </section>
        <section class="form-group">
            <label for="newValue">Value</label>
            <input type="text" id="newValue">
        </section>
        <section class="form-group">
            <button onclick="addNewKey()" class="button green">Add</button>
        </section>
    </section>

    <h2>Current Configuration</h2>

    <section class="f-row align-center mb-1 mt-1">
        <strong>Key</strong>
        <section class="f-filler"></section>
        <strong>Value (Press enter to save)</strong>
    </section>
    <section id="cfgSlot">

    </section>
    <script src="<?= url("admin/js/m_api_configuration.js") ?>"></script>
<?= include_file("m_footer") ?>