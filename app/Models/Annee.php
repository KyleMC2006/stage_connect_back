<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Annee extends Model
{
    use HasFactory;
    protected $fillable = ['libannee'];

    protected $table = 'annees';

    public function filannee(){
        return $this->belongsToMany(Filiere::class,'filannee','id_annee','id_fil');
    }
}
