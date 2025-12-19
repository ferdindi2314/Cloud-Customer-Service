<?php

return [
    // driver: 'local' or 'firebase'
    'driver' => env('ATTACHMENTS_DRIVER', 'local'),

    // when using local, which disk to use (see config/filesystems.php)
    'local_disk' => env('ATTACHMENTS_LOCAL_DISK', 'local'),

    // max allowed bytes for local storage (default 5MB)
    'max_size_bytes' => env('ATTACHMENTS_MAX_SIZE_BYTES', 5242880),
];
