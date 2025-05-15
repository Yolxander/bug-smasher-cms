<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | The path to your Firebase service account credentials JSON file.
    |
    */
    'credentials' => storage_path('firebase-credentials.json'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Database URL
    |--------------------------------------------------------------------------
    |
    | The URL of your Firebase Realtime Database.
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Firebase Storage Bucket
    |--------------------------------------------------------------------------
    |
    | The name of your Firebase Storage bucket.
    |
    */
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', ''),
];
