<!-- ══ BACK TO TOP ══ -->
<button class="back-top" id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</button>

<!-- ══ PWA SERVICE WORKER ══ -->
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/moviz/sw.js').catch(function() {});
}
</script>

</body>
</html>
