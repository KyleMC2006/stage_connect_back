<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder; 

class MessageController extends Controller
{
    
    /**

     * @return \Illuminate\Http\JsonResponse
     *  @param  \Illuminate\Http\Request  $request
     */
    public function index()
    {
        $user = Auth::user();

        
        $usersIds = Message::where('expediteur_id', $user->id)
                                 ->orWhere('destinataire_id', $user->id)
                                 ->pluck('expediteur_id', 'destinataire_id')
                                 ->flatten() 
                                 ->unique()
                                 ->filter(function ($id) use ($user) {
                                     return $id !== $user->id; // Exclure l'ID de l'utilisateur actuel
                                 });

        $conversations = [];
        foreach ($usersIds as $participantId) {
            $participant = User::find($participantId);
            if ($participant) {
                $lastMessage = Message::where(function (Builder $query) use ($user, $participant) {
                                    $query->where('expediteur_id', $user->id)
                                          ->where('destinataire_id', $participant->id);
                                })
                                ->orWhere(function (Builder $query) use ($user, $participant) {
                                    $query->where('expediteur_id', $participant->id)
                                          ->where('destinataire_id', $user->id);
                                })
                                ->latest() 
                                ->first();

                $unreadCount = Message::where('expediteur_id', $participant->id)
                                      ->where('destinataire_id', $user->id)
                                      ->where('lu', false)
                                      ->count();

                $conversations[] = [
                    'participant' => [
                        'id' => $participant->id,
                        'name' => $participant->name,
                        
                    ],
                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'contenu' => $lastMessage->contenu,
                        'created_at' => $lastMessage->created_at,
                        'is_my_message' => ($lastMessage->expediteur_id === $user->id), 
                        'lu' => $lastMessage->lu,
                    ] : null,
                    'unread_count' => $unreadCount,
                ];
            }
        }

        usort($conversations, function($a, $b) {
            $dateA = $a['last_message'] ? $a['last_message']['created_at'] : null;
            $dateB = $b['last_message'] ? $b['last_message']['created_at'] : null;

            if ($dateA === null && $dateB === null) return 0;
            if ($dateA === null) return 1;
            if ($dateB === null) return -1;

            return $dateB <=> $dateA; 
        });


        return response()->json($conversations, 200);
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'destinataire_id' => 'required|exists:users,id',
            'contenu' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();

        
        if ($request->destinataire_id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous envoyer de message à vous-même.'], 400);
        }

        $message = Message::create([
            'expediteur_id' => $user->id,
            'destinataire_id' => $request->destinataire_id,
            'contenu' => $request->contenu,
            'lu' => false, 
        ]);

   

        return response()->json(['message' => 'Message envoyé avec succès', 'data' => $message], 201);
    }


    public function getConversation(User $otherUser)
    {
        $user = Auth::user();

        
        $messages = Message::where(function (Builder $query) use ($user, $otherUser) {
                                $query->where('expediteur_id', $user->id)
                                      ->where('destinataire_id', $otherUser->id);
                            })
                            ->orWhere(function (Builder $query) use ($user, $otherUser) {
                                $query->where('expediteur_id', $otherUser->id)
                                      ->where('destinataire_id', $user->id);
                            })
                            ->orderBy('created_at', 'asc') // Tri chronologique
                            ->with(['expediteur', 'destinataire']) 
                            ->get();

        
        Message::where('expediteur_id', $otherUser->id)
               ->where('destinataire_id', $user->id)
               ->where('lu', false)
               ->update(['lu' => true]);

        return response()->json($messages, 200);
    }


    public function markAsRead(Message $message)
    {
        $user = Auth::user();

        
        if ($message->destinataire_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé. Vous ne pouvez pas marquer ce message comme lu.'], 403);
        }

        if (!$message->lu) {
            $message->update(['lu' => true]);
        }

        return response()->json(['message' => 'Message marqué comme lu avec succès.', 'data' => $message], 200);
    }


    public function destroy(Message $message)
    {
        $user = Auth::user();


        if ($message->expediteur_id !== $user->id && $message->destinataire_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé à supprimer ce message.'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message supprimé avec succès'], 204);
    }
}