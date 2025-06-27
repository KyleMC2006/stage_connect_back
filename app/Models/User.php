<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'description',
        'photo',
        'firebase_uid',
        'couverture', 
        'role', 
        'is_active',

    ];
    protected $table = 'users';

    protected $guarded = ['role'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'en_ligne' => 'boolean',
        ];
    }

    public function etudiant(){
        return $this->hasOne(Etudiant::class);
    }

    public function etablissement(){
        return $this->hasOne(Etablissement::class);
    }

    public function entreprise(){
        return $this->hasOne(Entreprise::class);
    }

    public function profilCommu(){
        return $this->hasOne(ProfilCommu::class);
    }
    public function commentaires(){
        return $this->hasMany(Commentaire::class);
    }

    public function messagesEnvoyes(){
        return $this->hasMany(Message::class, 'expediteur_id');
    }

    public function messagesRecus(){
        return $this->hasMany(Message::class, 'destinataire_id');
    }

    
    public function notifications(){
        return $this->hasMany(Notification::class);
    }

    
    
};



