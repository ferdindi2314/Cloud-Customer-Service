<?php

namespace App\Services\Firebase;

use Google\Cloud\Core\Timestamp;

class UserService
{
    private $firestore;

    public function __construct()
    {
        $factory = FirebaseFactory::make();
        $this->firestore = $factory->createFirestore()->database();
    }

    /**
     * Create a user document in Firestore `users` collection.
     * Returns the Firestore document id.
     *
     * @param array<string, mixed> $data
     * @return string
     */
    public function createUser(array $data): string
    {
        $now = new Timestamp(new \DateTime());
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        $ref = $this->firestore->collection('users')->add($data);
        return $ref->id();
    }

    /**
     * Find a Firestore user document by the Laravel user id stored in `laravel_id`.
     * Returns an array with 'id' and 'data' keys or null when not found.
     *
     * @param string $laravelId
     * @return array|null
     */
    public function findByLaravelId(string $laravelId): ?array
    {
        $documents = $this->firestore->collection('users')
            ->where('laravel_id', '=', $laravelId)
            ->limit(1)
            ->documents();

        foreach ($documents as $doc) {
            if ($doc->exists()) {
                return ['id' => $doc->id(), 'data' => $doc->data()];
            }
        }

        return null;
    }

    /**
     * Update Firestore user document by laravel id (best-effort).
     * Returns true on success, false otherwise.
     *
     * @param string $laravelId
     * @param array $data
     * @return bool
     */
    public function updateByLaravelId(string $laravelId, array $data): bool
    {
        $found = $this->findByLaravelId($laravelId);
        if (!$found) {
            return false;
        }

        try {
            $this->firestore->collection('users')->document($found['id'])->set($data, ['merge' => true]);
            return true;
        } catch (\Throwable $e) {
            logger()->error('Firestore update user failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete Firestore user document by laravel id (best-effort).
     * Returns true when deleted or not found, false when an error occurred.
     *
     * @param string $laravelId
     * @return bool
     */
    public function deleteByLaravelId(string $laravelId): bool
    {
        $found = $this->findByLaravelId($laravelId);
        if (!$found) {
            return true;
        }

        try {
            $this->firestore->collection('users')->document($found['id'])->delete();
            return true;
        } catch (\Throwable $e) {
            logger()->error('Firestore delete user failed: ' . $e->getMessage());
            return false;
        }
    }
}
