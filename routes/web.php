<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ChatController;
use App\Models\User;
use App\Models\Message;
use App\Models\GroupMessage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/contacts', [FriendController::class, 'contacts'])
    ->middleware('auth')
    ->name('contacts.index');

/*
|--------------------------------------------------------------------------
| REGISTER
|--------------------------------------------------------------------------
*/

Route::get('/register', [RegisterController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [RegisterController::class, 'sendOtp'])
    ->name('register.sendOtp');

Route::get('/register/verify', [RegisterController::class, 'showVerifyOtp'])
    ->name('register.verify');

Route::post('/register/verify', [RegisterController::class, 'verifyOtp'])
    ->name('register.verifyOtp');

Route::get('/register/profile', [RegisterController::class, 'showProfileForm'])
    ->name('register.profile');

Route::post('/register/profile', [RegisterController::class, 'completeRegistration'])
    ->name('register.complete');

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    $friends = User::whereIn('id', function ($query) {
        $query->select('sender_id')
            ->from('friend_requests')
            ->where('receiver_id', auth()->id())
            ->where('status', 'accepted');
    })
        ->orWhereIn('id', function ($query) {
            $query->select('receiver_id')
                ->from('friend_requests')
                ->where('sender_id', auth()->id())
                ->where('status', 'accepted');
        })
        ->get();

    $friends = $friends->map(function ($friend) {
        $friend->last_message = Message::where(function ($query) use ($friend) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $friend->id);
        })
            ->orWhere(function ($query) use ($friend) {
                $query->where('sender_id', $friend->id)
                    ->where('receiver_id', auth()->id());
            })
            ->latest()
            ->first();

        $friend->unread_count = Message::where('sender_id', $friend->id)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return $friend;
    });

    $groups = auth()->user()
        ->groups()
        ->with('creator')
        ->latest()
        ->get();

    $groups = $groups->map(function ($group) {
        $group->last_message = GroupMessage::where('chat_group_id', $group->id)
            ->with('sender')
            ->latest()
            ->first();

        return $group;
    });

    return view('dashboard', compact('friends', 'groups'));
})->middleware(['auth'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | FRIENDS
    |--------------------------------------------------------------------------
    */

    // Lista amigos
    Route::get('/friends', [FriendController::class, 'index'])
        ->name('friends.index');

    // Procurar utilizadores
    Route::get('/friends/search', [FriendController::class, 'search'])
        ->name('friends.search');

    // Enviar pedido amizade
    Route::post('/friends/request/{user}', [FriendController::class, 'sendRequest'])
        ->name('friends.request');

    // Aceitar pedido
    Route::post('/friends/accept/{friendRequest}', [FriendController::class, 'accept'])
        ->name('friends.accept');

    // Recusar pedido
    Route::post('/friends/reject/{friendRequest}', [FriendController::class, 'reject'])
        ->name('friends.reject');

    /*
    |--------------------------------------------------------------------------
    | CHAT
    |--------------------------------------------------------------------------
    */

    Route::get('/chat/{user}/partial', [ChatController::class, 'partial'])
        ->name('chat.partial');

    Route::get('/chat/{user}', [ChatController::class, 'show'])
        ->name('chat.show');

    // Send message    
    Route::post('/chat/{user}', [ChatController::class, 'send'])
        ->name('chat.send');

    Route::delete('/messages/{message}/delete-for-everyone', [ChatController::class, 'deleteForEveryone'])
        ->name('messages.deleteForEveryone');

    Route::put('/messages/{message}', [ChatController::class, 'updateMessage'])
        ->name('messages.update');

    Route::post('/messages/{message}/delivered', [ChatController::class, 'markDelivered'])
        ->name('messages.delivered');

    /*
    |--------------------------------------------------------------------------
    | GROUP CHAT
    |--------------------------------------------------------------------------
    */
    Route::get('/groups', [GroupController::class, 'index'])
        ->name('groups.index');

    Route::get('/groups/create', [GroupController::class, 'create'])
        ->name('groups.create');

        // Abrir na mesma tela routes
    Route::post('/groups', [GroupController::class, 'store'])
        ->name('groups.store');

    Route::get('/groups/{group}/partial', [GroupController::class, 'partial'])
        ->name('groups.partial');

    Route::get('/groups/{group}', [GroupController::class, 'show'])
        ->name('groups.show');

    Route::get('/groups/{group}', [GroupController::class, 'show'])
        ->name('groups.show');

    Route::post('/groups/{group}/messages', [GroupController::class, 'sendMessage'])
        ->name('groups.messages.send');

    Route::delete('/group-messages/{message}/delete-for-everyone', [GroupController::class, 'deleteMessage'])
        ->name('group.messages.deleteForEveryone');

    Route::post('/groups/{group}/members', [GroupController::class, 'addMembers'])
        ->name('groups.members.add');

    Route::delete('/groups/{group}/members/{user}', [GroupController::class, 'removeMember'])
        ->name('groups.members.remove');

    Route::post('/groups/{group}/members/{user}/make-admin', [GroupController::class, 'makeAdmin'])
        ->name('groups.members.makeAdmin');

    Route::delete('/groups/{group}/leave', [GroupController::class, 'leaveGroup'])
        ->name('groups.leave');

    Route::put('/groups/{group}', [GroupController::class, 'update'])
        ->name('groups.update');

    Route::put('/group-messages/{message}', [GroupController::class, 'updateMessage'])
        ->name('group.messages.update');

    Route::post('/typing/{user}', [ChatController::class, 'typing'])
        ->name('chat.typing');
});

require __DIR__ . '/auth.php';