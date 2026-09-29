<?php $pageScripts = $pageScripts ?? []; ?>
        </div>
    </main>
</div>
<div id="toastContainer" class="toast-container" aria-live="polite"></div>
<div id="modalContainer" class="modal-container" aria-live="assertive"></div>
<script src="assets/js/app.js"></script>
<?php foreach ($pageScripts as $script): ?>
<script src="<?= e($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
