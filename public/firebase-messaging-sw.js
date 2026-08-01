importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js');

// Initialize Firebase App in Service Worker
const firebaseConfig = {
  apiKey: "AIzaSyCjcYek3pCosfdI0CJB3D08-BnP2HScIsY",
  authDomain: "sipatlikur.firebaseapp.com",
  projectId: "sipatlikur",
  storageBucket: "sipatlikur.firebasestorage.app",
  messagingSenderId: "521144391233",
  appId: "1:521144391233:web:b37d0780aecb6c68acd8c0",
  measurementId: "G-46KWQ3FQ41"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    const title = (payload.notification && payload.notification.title) || (payload.data && payload.data.title) || 'SIPATLIKUR Notifikasi';
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
