<?php

return [
    'apiKey' => "AIzaSyBfX3cxbhYVO5LfF6ttN_7zyqmkE7emWY8",
    'authDomain' => "bugsmasher-f8191.firebaseapp.com",
    'projectId' => "bugsmasher-f8191",
    'storageBucket' => "bugsmasher-f8191.firebasestorage.app",
    'messagingSenderId' => "685844233257",
    'appId' => "1:685844233257:web:c6c7153b197194c76b3038",
    'serviceAccount' => [
        'type' => 'service_account',
        'project_id' => 'bugsmasher-f8191',
        'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
        'private_key' => env('FIREBASE_PRIVATE_KEY'),
        'client_email' => env('FIREBASE_CLIENT_EMAIL'),
        'client_id' => env('FIREBASE_CLIENT_ID'),
        'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
        'token_uri' => 'https://oauth2.googleapis.com/token',
        'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
        'client_x509_cert_url' => env('FIREBASE_CLIENT_CERT_URL')
    ]
];
