        </main>
    </div>
</div>

<meta name="csrf-token" content="<?= Csrf::token() ?>">
<script>const BASE_URL_JS = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>/js/ajax.js"></script>
<script src="<?= BASE_URL ?>/js/notifications.js"></script>
<script src="<?= BASE_URL ?>/js/datepicker.js"></script>
<script>
    // باز و بسته کردن منو در حالت موبایل
    document.getElementById('menuToggle')?.addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('open');
    });
</script>
</body>
</html>
