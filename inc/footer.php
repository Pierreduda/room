</main>
<footer class="container-fluid">
    <div class="row">
        <div class="col bg-dark text-light text-center py-3 w-100">
            &copy; <?php echo date('Y') ?> Room - Tous droits réservés | <a href="mentions_legales.php">Mentions Légales</a> | <a href="cgv.php">CGV</a>
        </div>
    </div>
</footer>
<script src="<?php echo URL ?>inc/js/ajax.js"  type="module"></script>
<script type="module" src=<?php echo '"' . URL . 'inc/js/datepicker.js"' ?>></script>
<script src="<?php echo URL ?>node_modules/dayjs/dayjs.min.js"></script>
<script src="<?php echo URL ?>node_modules/dayjs/plugin/customParseFormat.js"></script>
<script src="<?php echo URL ?>node_modules/lightbox2/src/js/lightbox.js"></script>
<script>
    dayjs.extend(window.dayjs_plugin_customParseFormat);
</script>
</body>

</html>