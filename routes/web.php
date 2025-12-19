<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketCommentController;
use App\Services\Firebase\FirebaseFactory;


Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/firebase-test', function () {
    try {
        $factory = FirebaseFactory::make();

        // Firestore test
        $firestoreDb = $factory->createFirestore()->database();
        $firestoreOk = true;
        $firestoreProbe = [];

        try {
            $documents = $firestoreDb->collection('tickets')->limit(1)->documents();
            foreach ($documents as $doc) {
                $firestoreProbe = [
                    'collection' => 'tickets',
                    'sample_exists' => $doc->exists(),
                    'sample_id' => $doc->id(),
                ];
                break;
            }
        } catch (Throwable $e) {
            $firestoreOk = false;
            $firestoreProbe = ['error' => $e->getMessage()];
        }

        // Storage test
        $storageOk = true;
        $storageProbe = [];
        try {
            $bucket = $factory->createStorage()->getBucket(config('firebase.storage_bucket'));
            $storageProbe = [
                'bucket' => (string) config('firebase.storage_bucket'),
                'bucket_exists' => $bucket->exists(),
            ];
        } catch (Throwable $e) {
            $storageOk = false;
            $storageProbe = ['error' => $e->getMessage()];
        }

        return response()->json([
            'ok' => $firestoreOk && $storageOk,
            'project_id' => (string) config('firebase.project_id'),
            'firestore' => array_merge(['ok' => $firestoreOk], $firestoreProbe),
            'storage' => array_merge(['ok' => $storageOk], $storageProbe),
        ]);
    } catch (Throwable $e) {
        return response()->json([
            'ok' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
})->middleware(['auth', 'role:admin,agent'])->name('firebase.test');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
    Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::patch('/admin/users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('admin.users.updateRole');

    // Admin categories management
    Route::get('/admin/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/admin/categories/create', [\App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/admin/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/admin/categories/{category}/edit', [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/admin/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('admin.categories.destroy');
});

Route::middleware(['auth', 'role:customer,admin,agent'])->group(function () {
    // Download attachment (supports signed URL)
    Route::get('/tickets/{ticket}/attachments/download/{path}', [\App\Http\Controllers\TicketController::class, 'downloadAttachment'])
        ->name('tickets.attachments.download')
        ->middleware('signed');

    Route::resource('tickets', TicketController::class);

    // Comment Ticket
    Route::post('/tickets/{ticket}/comments', [TicketCommentController::class, 'store'])->name('tickets.comments.store');

    // Aksi agent/admin
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assignAgent'])
        ->middleware('role:admin')
        ->name('tickets.assign');

    Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])
        ->middleware('role:admin,agent')
        ->name('tickets.updateStatus');
});

require __DIR__ . '/auth.php';
