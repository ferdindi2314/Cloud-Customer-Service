<?php

namespace App\Services\Firebase;

use Kreait\Firebase\Factory;

class FirebaseFactory
{
    public static function make(): Factory
    {
        $credentialsPath = config('firebase.credentials');

        $absoluteCredentialsPath = $credentialsPath;
        if (is_string($credentialsPath) && $credentialsPath !== '' && !self::isAbsolutePath($credentialsPath)) {
            $absoluteCredentialsPath = base_path($credentialsPath);
        }

        $factory = (new Factory)
            ->withServiceAccount($absoluteCredentialsPath)
            ->withProjectId(config('firebase.project_id'));

        $databaseUrl = config('firebase.database_url');
        if (is_string($databaseUrl) && $databaseUrl !== '') {
            $factory = $factory->withDatabaseUri($databaseUrl);
        }

        return $factory;
    }

    private static function isAbsolutePath(string $path): bool
    {
        // Windows: C:\ or \\server\share
        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1 || str_starts_with($path, '\\\\')) {
            return true;
        }

        // Unix-like: /var/www/...
        return str_starts_with($path, '/');
    }
}
