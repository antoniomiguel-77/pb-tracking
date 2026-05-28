@php($pixelId = data_get($config, 'pixel_id'))
@if($pixelId)
<script>
(function (w, d, s, u, n, t, a) {
    if (w.fbq) {
        return;
    }

    n = w.fbq = function () {
        n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
    };

    if (!w._fbq) {
        w._fbq = n;
    }

    n.push = n;
    n.loaded = true;
    n.version = '2.0';
    n.queue = [];
    t = d.createElement(s);
    t.async = true;
    t.src = u;
    a = d.getElementsByTagName(s)[0];
    a.parentNode.insertBefore(t, a);
})(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

fbq('init', @json($pixelId));

window.YetoTracking = window.YetoTracking || {};
window.YetoTracking.providers = window.YetoTracking.providers || {};
window.YetoTracking.providers.facebook = {
    enabled: true,
    pixelId: @json($pixelId),
};
</script>
@endif
