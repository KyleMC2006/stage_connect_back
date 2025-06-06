<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Etablissement extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nom_etablissement',
        'siteweb',
        'adresse',
        'ville_id',
        'numero_agrement',
        
    ];
    protected $table = 'etablissements';

    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function etudiants(){
        return $this->hasMany(Etudiant::class);
    }

    public function ecolefil(){
        return $this->belongsToMany(Filiere::class,'ecole_fils','id_etablissement','id_filiere');
    }

    public function ville(){  
        return $this->belongsTo(Ville::class);
    }
}
