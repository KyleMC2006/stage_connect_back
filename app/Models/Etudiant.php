<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Etudiant extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'matricule',
        'projets',
        'competences',
        'CV',
        'parcours',
        'id_filiere',
        'id_etablissement'

    ];

    
    protected $table = 'etudiants';
 

    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function etablissement(){
        return $this->belongsTo(Etablissement::class);
    }

    public function filiere(){
        return $this->belongsTo(Filiere::class);
    }
    public function stages()
    {
        return $this->hasMany(Stage::class);
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class);
    }

    
}
