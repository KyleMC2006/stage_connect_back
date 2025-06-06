<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ville extends Model
{
    use HasFactory;

    protected $fillable = ['nom_ville'];
    protected $table = 'villes';

    public function entreprises(){
        return $this->hasMany(Entreprise::class);
    }
    
    public function etablissements(){
        return $this->hasMany(Etablissement::class);
    }
}
