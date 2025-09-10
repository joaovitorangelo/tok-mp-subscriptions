import { initializeApp } from "https://www.gstatic.com/firebasejs/9.18.0/firebase-app.js";

import { getMessaging, getToken } from "https://www.gstatic.com/firebasejs/9.18.0/firebase-messaging.js";

const firebaseConfig = {
    apiKey: tok_mp_subs.firebase_api_key,
    authDomain: tok_mp_subs.firebase_auth_domain,
    projectId: tok_mp_subs.firebase_project_id,
    storageBucket: tok_mp_subs.firebase_storage_bucket,
    messagingSenderId: tok_mp_subs.firebase_messaging_sender_id,
    appId: tok_mp_subs.firebase_app_id,
    measurementId: tok_mp_subs.firebase_measurement_id
};

let app;
if (!getApps().length) {
  app = initializeApp(firebaseConfig);
} else {
  app = getApp();
}

const messaging = getMessaging(app);

document.addEventListener('DOMContentLoaded', function() {
    Notification.requestPermission().then(permission => {
        if (permission !== 'granted') return;

        navigator.serviceWorker.register(`${tok_mp_subs.plugin_url}sw.js`).then(registration => {
            getToken(messaging, {
                serviceWorkerRegistration: registration,
                vapidKey: tok_mp_subs.firebase_vapid_key
            }).then(fcm_token => {
                jQuery.ajax({
                    url: tok_mp_subs.ajax_url,
                    type: 'POST',
                    data: { action: 'store_fcm_token_web', fcm_token },
                    success: res => console.log(res),
                    error: (jqXHR, textStatus, errorThrown) => console.error(jqXHR, textStatus, errorThrown)
                });
            }).catch(err => console.log('Erro ao recuperar token:', err));
        });
    });
});
