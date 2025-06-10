<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partenariat extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'etablissement_id',
        'date_debut',
        'date_fin',
        'statut',
        
    ];


    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

 
    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }
}