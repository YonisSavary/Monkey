<?= include_file("m_head") ?>

<aside>
    <h1>Summary</h1>
    <ul id="asideMenu">
    </ul>
</aside>

<?= include_file("m_doc_introduction") ?>

<?= include_file("m_doc_lifecycle") ?>

<?= include_file("m_doc_configuration") ?>
<?= include_file("m_doc_register") ?>
<?= include_file("m_doc_routes") ?>

<?= include_file("m_doc_database") ?>

<?= include_file("m_doc_models") ?>
<?= include_file("m_doc_controllers") ?>
<?= include_file("m_doc_views") ?>

<?= include_file("m_doc_auth") ?>

<script src="<?= url("admin/js/m_api_documentation.js") ?>"></script>

<?= include_file("m_footer") ?>