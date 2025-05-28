<?php if (isset($before_body_close)): ?>
    <?php echo $before_body_close; ?>
<?php endif; ?>

<script src="js/scripts.js"></script>
<script>
    // Force theme application again in case any dynamic content was added
    document.addEventListener('DOMContentLoaded', function() {
        // Re-apply theme to ensure everything is styled correctly
        if (window.ThemeManager) {
            const currentTheme = window.ThemeManager.getCurrentTheme();
            window.ThemeManager.setTheme(currentTheme);
        }
    });
</script>
</body>
</html> 