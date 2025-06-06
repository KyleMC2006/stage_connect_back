<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Domaine extends Model
{
    use HasFactory;
    protected $fillable = ['libdomaine'];

    protected $table = 'domaines';

    public function entreprises(){
        return $this->hasMany(Entreprise::class);
    }

    public function offres(){
        return $this->hasMany(Offre::class);
    }
}
