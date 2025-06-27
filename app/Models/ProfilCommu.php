<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfilCommu extends Model
{
    use HasFactory;
    protected $fillable = ['likes','user_id'];

    protected $table = 'profil_commus';

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function commentaire()
    {
        return $this->hasMany(Commentaire::class, 'profil_commus_id');
    }

}
