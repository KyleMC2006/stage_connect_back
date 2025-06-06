<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Filiere extends Model
{
    use HasFactory;
    protected $fillable = ['libfil'];
    

    protected $table = 'filieres';

    public function etudiant(){
        return $this->hasMany(Etudiant::class);
    }

    public function ecole_fil(){
        return $this->belongsToMany(Etablissement::class,'ecole_fils','id_filiere','id_etablissement');
    }

    public function filannee(){
        return $this->belongsToMany(Annee::class,'filannee','id_fil','id_annee');
    }
}

