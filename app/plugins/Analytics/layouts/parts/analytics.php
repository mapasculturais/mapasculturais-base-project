<!-- Script google Analytics -->
<?php
    $key = $config["analytics_key"];
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $key ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', '<?= $key ?>');
</script>