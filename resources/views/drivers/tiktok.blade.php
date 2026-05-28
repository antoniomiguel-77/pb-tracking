@php($pixelId = data_get($config, 'pixel_id'))
@if($pixelId)
<script>
!function (w, d, t) {
    w.TiktokAnalyticsObject = t;
    var ttq = w[t] = w[t] || [];

    ttq.methods = ['page', 'track', 'identify', 'instances', 'debug', 'on', 'off', 'once', 'ready', 'alias', 'group', 'enableCookie'];
    ttq.setAndDefer = function (obj, method) {
        obj[method] = function () {
            obj.push([method].concat([].slice.call(arguments, 0)));
        };
    };

    ttq.load = function (pixelId) {
        var script = d.createElement('script');
        script.type = 'text/javascript';
        script.async = true;
        script.src = 'https://analytics.tiktok.com/i18n/pixel/events.js?sdkid=' + pixelId + '&lib=' + t;
        var firstScript = d.getElementsByTagName('script')[0];
        firstScript.parentNode.insertBefore(script, firstScript);
    };

    for (var i = 0; i < ttq.methods.length; i++) {
        ttq.setAndDefer(ttq, ttq.methods[i]);
    }

    ttq.load(@json($pixelId));
}(window, document, 'ttq');

window.YetoTracking = window.YetoTracking || {};
window.YetoTracking.providers = window.YetoTracking.providers || {};
window.YetoTracking.providers.tiktok = {
    enabled: true,
    pixelId: @json($pixelId),
};
</script>
@endif
