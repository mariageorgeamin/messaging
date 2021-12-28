<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\ThreadResource;
use App\Http\Validators\CreateMessageValidator;
use App\User;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;
use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;

class MessagesController extends BaseController
{
    /**
     * Show all of the message threads to the user.
     *
     * @return mixed
     */
    public function index()
    {
        // All threads, ignore deleted/archived participants
        $threads = Thread::getAllLatest()->get();

        // All threads that user is participating in
        // $threads = Thread::forUser(Auth::id())->latest('updated_at')->get();

        // All threads that user is participating in, with new messages
        // $threads = Thread::forUserWithNewMessages(Auth::id())->latest('updated_at')->get();

        return response()->json(['status' => true, 'message' => __('Success'),
            'thread' => ThreadResource::collection($threads)], 200);
    }

    /**
     * Shows a message thread.
     *
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Validation Error.', 'The thread with ID: ' . $id . ' was not found.');
        }

        // show current user in list if not a current participant
        // $users = User::whereNotIn('id', $thread->participantsUserIds())->get();

        // don't show the current user in list
        $userId = Auth::id();
        $users = User::whereNotIn('id', $thread->participantsUserIds($userId))->get();

        $thread->markAsRead($userId);

        return response()->json(['status' => true, 'message' => __('Success'),
            'thread' => new ThreadResource($thread)], 200);
    }

    /**
     * Stores a new message thread.
     *
     * @return mixed
     */
    public function store(\Illuminate\Http\Request $request, CreateMessageValidator $validator)
    {
        if ($validator->validate($request)->failed()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input = $request->all();

        $thread = Thread::create([
            'subject' => $input['subject'],
        ]);

        // Message
        Message::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'body' => $input['message'],
        ]);

        // Sender
        Participant::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'last_read' => new Carbon(),
        ]);

        // Recipients
        if (Request::has('recipients')) {
            $thread->addParticipant($input['recipients']);
        }

        return response()->json(['status' => true, 'message' => __('Success'),
            'thread' => new ThreadResource($thread)], 200);
    }

    /**
     * Adds a new message to a current thread.
     *
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {

            return response()->json(['status' => false,
                'message' => 'The thread with ID: ' . $id . ' was not found.'], 200);

        }

        $thread->activateAllParticipants();

        // Message
        Message::create([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'body' => Request::input('message'),
        ]);

        // Add replier as a participant
        $participant = Participant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
        ]);
        $participant->last_read = new Carbon();
        $participant->save();

        // Recipients
        if (Request::has('recipients')) {
            $thread->addParticipant(Request::input('recipients'));
        }

        return response()->json(['status' => true, 'message' => __('Success'),
            'thread' => new ThreadResource($thread)], 200);
    }


    /**
     * Adds a new message to a current thread.
     *
     * @param $id
     * @return mixed
     */
    public function search()
    {

        $search = Request::input('search');
        $threads = Thread::getAllLatest()->whereHas('messages' , function ($query) use ($search) {
            $query->where('body','like','%'.$search.'%');
        })->orWhereHas('participants' , function ($query) use ($search) {
            $query->whereHas('user', function ($user) use ($search) {
                $user->where('name','like','%'.$search.'%');
            });
        })->get();

        return response()->json(['status' => true, 'message' => __('Success'),
            'thread' => ThreadResource::collection($threads)], 200);
    }
}
