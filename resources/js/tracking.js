(function (window, document) {
    const tracking = window.YetoTracking = window.YetoTracking || {};
    const state = tracking.__state = tracking.__state || {
        booted: false,
        initialPageViewSent: false,
    };

    const providerEventMap = {
        facebook: {
            PageView: 'PageView',
            Purchase: 'Purchase',
            Lead: 'Lead',
            CompleteRegistration: 'CompleteRegistration',
            AddToCart: 'AddToCart',
        },
        google: {
            PageView: 'page_view',
            Purchase: 'purchase',
            Lead: 'generate_lead',
            CompleteRegistration: 'sign_up',
            AddToCart: 'add_to_cart',
        },
        tiktok: {
            PageView: 'PageView',
            Purchase: 'CompletePayment',
            Lead: 'Lead',
            CompleteRegistration: 'CompleteRegistration',
            AddToCart: 'AddToCart',
        },
    };

    const providerMethodMap = {
        facebook: (event, data) => {
            if (typeof window.fbq === 'function') {
                window.fbq('track', event, data);
            }
        },
        google: (event, data) => {
            if (typeof window.gtag === 'function') {
                window.gtag('event', event, data);
            }
        },
        tiktok: (event, data) => {
            if (window.ttq && typeof window.ttq.track === 'function') {
                window.ttq.track(event, data);
            } else if (Array.isArray(window.ttq)) {
                window.ttq.push(['track', event, data]);
            }
        },
    };

    const normalizePayload = (eventOrPayload, data = {}) => {
        if (eventOrPayload && typeof eventOrPayload === 'object' && !Array.isArray(eventOrPayload)) {
            const payload = eventOrPayload;

            return {
                event: payload.event || payload.name || 'PageView',
                data: payload.data || {},
                meta: payload.meta || {},
                origin: payload.origin || payload.source || 'browser',
            };
        }

        return {
            event: eventOrPayload,
            data: data || {},
            meta: {},
            origin: 'javascript',
        };
    };

    const mapEventName = (provider, event) => {
        const providerMap = providerEventMap[provider] || {};
        return providerMap[event] || event;
    };

    const dispatchToProviders = (payload) => {
        Object.keys(providerMethodMap).forEach((provider) => {
            const providerConfig = (tracking.config && tracking.config.drivers && tracking.config.drivers[provider]) || null;

            if (!providerConfig || !providerConfig.enabled) {
                return;
            }

            providerMethodMap[provider](mapEventName(provider, payload.event), payload.data);
        });

        return payload;
    };

    const emitBrowserEvent = (payload) => {
        window.dispatchEvent(new CustomEvent('yeto-track', {
            detail: payload,
        }));
    };

    tracking.track = (event, data = {}) => {
        const payload = normalizePayload(event, data);
        dispatchToProviders(payload);
        emitBrowserEvent({
            ...payload,
            origin: 'javascript',
        });

        return payload;
    };

    tracking.pageView = () => tracking.track('PageView');

    const handleBrowserEvent = (event) => {
        const payload = normalizePayload(event.detail || {});

        if (payload.origin === 'javascript') {
            return;
        }

        dispatchToProviders(payload);
    };

    const bindLivewireListeners = () => {
        if (state.booted) {
            return;
        }

        state.booted = true;

        window.addEventListener('yeto-track', handleBrowserEvent);
        window.addEventListener('livewire:navigated', () => {
            tracking.pageView();
        });

        document.addEventListener('alpine:init', () => {
            if (window.Alpine) {
                if (typeof window.Alpine.store === 'function') {
                    window.Alpine.store('yetoTracking', {
                        pageView: tracking.pageView,
                        track: tracking.track,
                    });
                }

                if (typeof window.Alpine.magic === 'function') {
                    window.Alpine.magic('track', () => tracking.track);
                }
            }
        });
    };

    const bootstrap = () => {
        bindLivewireListeners();

        if (!state.initialPageViewSent) {
            state.initialPageViewSent = true;
            tracking.pageView();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap, { once: true });
    } else {
        bootstrap();
    }
})(window, document);
