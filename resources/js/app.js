import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Initialize Laravel Echo
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: "5b813ee681f5422deebc",
//     cluster: "ap2",
//     forceTLS: false,
//     wsHost: window.location.hostname,
//     wsPort: 6001,
//     wssPort: 6001,
//     disableStats: true,
//     enabledTransports: ['ws', 'wss'],
// });


window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
