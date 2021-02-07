<?= include_file("m_head") ?>
<section class="main-section">
    <h1>Models availables in your Database</h1>
    <p>
        Here is a list of your database tables, you can right click on a 
        model to either save it, or delete it from your application (not from your 
        database)
    </p>
    <section class="f-row align-center mb-1">
        <section class="f-filler">
            <strong>Model</strong>
        </section>
        <section class="f-row">
            <strong>Already Saved</strong>
        </section>
    </section>
    <section id="modelSection">
    </section>

    <section class="hidden context-menu" id="context-menu">
        <ul>
            <li onclick="fetchModel()">Fetch from database</li>
            <li onclick="deleteModel()">Remove from Monkey</li>
        </ul>
    </section>
    <script src="<?= url("admin/js/m_api_model.js") ?>"></script>
</section>
<?= include_file("m_footer") ?>