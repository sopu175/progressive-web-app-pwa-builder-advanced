importScripts('https://storage.googleapis.com/workbox-cdn/releases/6.5.4/workbox-sw.js');
workbox.setConfig({debug:false});
workbox.precaching.precacheAndRoute([]);

// Cache CSS/JS/images with StaleWhileRevalidate
workbox.routing.registerRoute(
    ({request}) => ['style','script','image'].includes(request.destination),
    new workbox.strategies.StaleWhileRevalidate()
);
// Cache pages/posts with NetworkFirst
workbox.routing.registerRoute(
    ({request}) => request.mode === 'navigate',
    new workbox.strategies.NetworkFirst({cacheName:'pages'})
);