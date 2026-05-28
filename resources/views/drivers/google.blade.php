@php($measurementId = data_get($config, 'measurement_id'))
@if($measurementId)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $measurementId }}"></script>
<script>
window.dataLayer = window.dataLayer || [];

function gtag() {
    dataLayer.push(arguments);
}

gtag('js', new Date());
gtag('config', @json($measurementId), {
    send_page_view: false
});

window.YetoTracking = window.YetoTracking || {};
window.YetoTracking.providers = window.YetoTracking.providers || {};
window.YetoTracking.providers.google = {
    enabled: true,
    measurementId: @json($measurementId),
};
</script>
@endif
