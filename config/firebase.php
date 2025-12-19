<?php

return [
    'credentials' => env('FIREBASE_CREDENTIALS'),
    'project_id'  => env('FIREBASE_PROJECT_ID'),
    'database_url' => env('FIREBASE_DATABASE_URL'),
    'firestore_enabled' => env('FIREBASE_FIRESTORE_ENABLED', false),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
];
