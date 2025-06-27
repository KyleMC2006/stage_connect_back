<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commentaire extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'comment','profil_commus_id'
    ];
    
    protected $table = 'commentaires';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profilCommu()
    {
        return $this->belongsTo(ProfilCommu::class, 'profil_commus_id');
    }


}
