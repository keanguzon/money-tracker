    <!-- Custom JavaScript -->
    <script src="<?= APP_URL ?>/assets/js/darkmode.js"></script>
    <script src="<?= APP_URL ?>/assets/js/main.js"></script>
    <?php if (isset($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?= APP_URL ?>/assets/js/<?= $script ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
