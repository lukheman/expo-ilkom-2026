// Service worker placeholder. Keep browser requests out of MVC routing.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
