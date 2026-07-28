importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js');

// Initialize Firebase App in Service Worker
const firebaseConfig = {
  apiKey: "AIzaSyDYo1RylA8Sl-LuYoo1EmfJM2yiAJtwfSA",
  authDomain: "sinala-notif.firebaseapp.com",
  projectId: "sinala-notif",
  storageBucket: "sinala-notif.firebasestorage.app",
  messagingSenderId: "977484200703",
  appId: "1:977484200703:web:98380acbd0f5c3a46ceaca",
  measurementId: "G-SRYTNDKP5C"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    const title = (payload.notification && payload.notification.title) || (payload.data && payload.data.title) || 'SINALA Notifikasi';
    const body = (payload.notification && payload.notification.body) || (payload.data && payload.data.body) || '';
    const icon = (payload.notification && payload.notification.icon) || (payload.data && payload.data.icon) || '/adminlte/img/user2.png';

    const notificationOptions = {
        body: body,
        icon: icon,
        badge: icon,
        vibrate: [200, 100, 200],
        data: payload.data || {}
    };

    return self.registration.showNotification(title, notificationOptions);
});
