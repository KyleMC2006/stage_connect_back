<?php



namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        $query = $user->notifications(); 

        
        $notifications = $user->notifications()->latest()->paginate(15);

        return response()->json($notifications, 200);
    }

    public function all(){
     $notifications = Notification::all();
     return response()->json($notifications)
    }

    /**
     * @param  int  $id L'ID de la notification.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification non trouvée ou non autorisée'], 404);
        }

        // Marque la notification comme lue si elle ne l'est pas déjà
        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return response()->json($notification, 200);
    }


    public function destroy($id)
    {
        $user = Auth::user();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification non trouvée ou non autorisée'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification supprimée avec succès'], 200);
    }

    public function countUnreadNotifications()
    {
        // 1. Récupérer l'utilisateur authentifié
        $user = Auth::user();

        // 2. Vérifier si un utilisateur est authentifié
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401); // Unauthorized
        }

        // 3. Compter les notifications où 'is_read' est false
        // La méthode `notifications()` retourne le query builder pour les notifications de cet utilisateur.
        $unreadCount = $user->notifications()->where('is_read', false)->count();

        return response()->json([
            'message' => 'Nombre de notifications non lues pour l\'utilisateur ' . $user->name,
            'count' => $unreadCount
        ], 200);
    }
}