<?php

namespace App\Services\Firebase;

use Google\Cloud\Core\Timestamp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class TicketService
{
    private $firestore;
    private $storage;
    private $bucket;

    public function __construct()
    {
        $factory = FirebaseFactory::make();
        $this->firestore = $factory->createFirestore()->database();

        $this->storage = null;
        $this->bucket = null;
        try {
            $this->storage = $factory->createStorage();
            if (config('firebase.storage_bucket')) {
                $this->bucket = $this->storage->getBucket(config('firebase.storage_bucket'));
            }
        } catch (\Throwable $e) {
            // ignore - bucket may not be configured (we support local storage)
            $this->storage = null;
            $this->bucket = null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllTickets(): array
    {
        $documents = $this->firestore->collection('tickets')->documents();
        return $this->documentsToArray($documents);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTicketsByCustomer(string $customerId): array
    {
        $documents = $this->firestore->collection('tickets')
            ->where('customer_id', '=', $customerId)
            ->documents();

        return $this->documentsToArray($documents);
    }

    public function createTicket(array $data): string
    {
        $now = new Timestamp(Carbon::now('Asia/Jakarta'));
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $data['status'] = $data['status'] ?? 'open';

        // ensure customer_id is stored as string to avoid Firestore type mismatch
        if (isset($data['customer_id'])) {
            $data['customer_id'] = (string) $data['customer_id'];
        }

        $ticketRef = $this->firestore->collection('tickets')->add($data);
        return $ticketRef->id();
    }

    public function findTicket(string $id)
    {
        return $this->firestore->collection('tickets')->document($id)->snapshot();
    }

    public function getTicket(string $id): ?array
    {
        $snapshot = $this->findTicket($id);
        if (!$snapshot->exists()) {
            return null;
        }

        $ticket = $snapshot->data();
        $ticket['id'] = $snapshot->id();
        return $this->normalizeTicket($ticket);
    }

    public function updateTicket(string $id, array $data): void
    {
        $data['updated_at'] = new Timestamp(Carbon::now('Asia/Jakarta'));
        $this->firestore->collection('tickets')->document($id)->set($data, ['merge' => true]);
    }

    public function deleteTicket(string $id): void
    {
        $this->firestore->collection('tickets')->document($id)->delete();
    }

    /**
     * @param array<int, UploadedFile> $files
     * @return array<int, array<string, mixed>>
     */
    public function uploadAttachments(array $files, string $ticketId): array
    {
        $attachments = [];

        $driver = config('attachments.driver', 'local');
        $localDisk = config('attachments.local_disk', 'local');
        $maxSize = (int) config('attachments.max_size_bytes', 5242880);

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            // validate size
            if ($file->getSize() > $maxSize) {
                // skip files larger than allowed
                continue;
            }

            $filename = trim($file->getClientOriginalName()) ?: uniqid('file_', true);

            if ($driver === 'firebase' && $this->bucket) {
                // upload to Firebase Storage
                $extension = $file->getClientOriginalExtension();
                $objectName = 'tickets/' . $ticketId . '/attachments/' . uniqid('', true) . ($extension ? ('.' . $extension) : '');

                $object = $this->bucket->upload(
                    fopen($file->getRealPath(), 'r'),
                    [
                        'name' => $objectName,
                        'metadata' => [
                            'contentType' => $file->getClientMimeType() ?: 'application/octet-stream',
                        ],
                    ]
                );

                $attachments[] = [
                    'name' => $filename,
                    'path' => $objectName,
                    'content_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'storage' => 'firebase',
                ];
            } else {
                // store locally on configured disk
                try {
                    $dir = 'tickets/' . $ticketId . '/attachments';
                    $storedPath = Storage::disk($localDisk)->putFileAs($dir, $file, $filename);
                    $attachments[] = [
                        'name' => $filename,
                        'path' => $storedPath,
                        'content_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'storage' => 'local',
                    ];
                } catch (\Throwable $e) {
                    logger()->error('Failed to store attachment locally: ' . $e->getMessage());
                    continue;
                }
            }
        }

        return $attachments;
    }

    public function getAttachmentTemporaryUrl(string $ticketId, string $path, string $expires = '+60 minutes'): ?string
    {
        $driver = config('attachments.driver', 'local');

        if ($driver === 'firebase' && $this->bucket) {
            $object = $this->bucket->object($path);
            if (!$object->exists()) {
                return null;
            }
            return $object->signedUrl(new \DateTimeImmutable($expires));
        }

        // For local storage generate a temporary signed route to download
        try {
            $expiresAt = Carbon::parse($expires);
        } catch (\Throwable $e) {
            $expiresAt = Carbon::now()->addMinutes(60);
        }

        // route will be tickets.attachments.download and expect 'ticket' and 'path' params
        // path will be base64 encoded to keep route-safe
        return URL::temporarySignedRoute(
            'tickets.attachments.download',
            $expiresAt,
            ['ticket' => $ticketId, 'path' => base64_encode($path)]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getComments(string $ticketId): array
    {
        $documents = $this->firestore
            ->collection('tickets')
            ->document($ticketId)
            ->collection('comments')
            ->orderBy('created_at', 'asc')
            ->documents();

        $comments = $this->documentsToArray($documents);
        foreach ($comments as &$comment) {
            $comment = $this->normalizeComment($comment);
        }

        return $comments;
    }

    public function addComment(string $ticketId, array $data): string
    {
        $now = new Timestamp(Carbon::now('Asia/Jakarta'));
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        $ref = $this->firestore
            ->collection('tickets')
            ->document($ticketId)
            ->collection('comments')
            ->add($data);

        // update ticket timestamp for list sorting/visibility
        $this->updateTicket($ticketId, ['updated_at' => $now]);

        return $ref->id();
    }

    private function documentsToArray($documents): array
    {
        $result = [];
        foreach ($documents as $doc) {
            if (!$doc->exists()) {
                continue;
            }
            $row = $doc->data();
            $row['id'] = $doc->id();
            $result[] = $this->normalizeTicket($row);
        }

        // newest first by updated_at (if exists)
        usort($result, function ($a, $b) {
            return strcmp((string)($b['updated_at_iso'] ?? ''), (string)($a['updated_at_iso'] ?? ''));
        });

        return $result;
    }

    private function normalizeTicket(array $ticket): array
    {
        if (isset($ticket['created_at']) && $ticket['created_at'] instanceof Timestamp) {
            $ticket['created_at_iso'] = Carbon::instance($ticket['created_at']->get())->setTimezone('Asia/Jakarta')->toDateTimeString();
        }
        if (isset($ticket['updated_at']) && $ticket['updated_at'] instanceof Timestamp) {
            $ticket['updated_at_iso'] = Carbon::instance($ticket['updated_at']->get())->setTimezone('Asia/Jakarta')->toDateTimeString();
        }

        return $ticket;
    }

    private function normalizeComment(array $comment): array
    {
        if (isset($comment['created_at']) && $comment['created_at'] instanceof Timestamp) {
            $comment['created_at_iso'] = Carbon::instance($comment['created_at']->get())->setTimezone('Asia/Jakarta')->toDateTimeString();
        }
        if (isset($comment['updated_at']) && $comment['updated_at'] instanceof Timestamp) {
            $comment['updated_at_iso'] = Carbon::instance($comment['updated_at']->get())->setTimezone('Asia/Jakarta')->toDateTimeString();
        }
        return $comment;
    }
}
