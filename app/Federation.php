<?php

namespace App;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Webpatser\Countries\Countries;

class Federation extends Model
{

    protected $table = 'federation';
    public $timestamps = true;

    protected $guarded = ['id'];


    public function president()
    {
        return $this->hasOne(User::class, 'id', 'president_id');
    }


    public function associations()
    {
        return $this->hasMany(Association::Class);
    }

    public function clubs()
    {
        return $this->hasManyThrough(Club::class, Association::class);
    }

    public function country()
    {
        return $this->belongsTo(Countries::Class);
    }

    /**
     * @param $query
     * @param User $user
     * @return Builder
     * @throws AuthorizationException
     */
    public function scopeForUser($query, User $user)
    {
        switch (true) {
            case $user->isSuperAdmin():
                return $query;
            case $user->isFederationPresident() && $user->federationOwned != null:
                return $query->where('id', '=', $user->federationOwned->id);

            case $user->isAssociationPresident() && $user->associationOwned:
                return $query->where('id', '=', $user->associationOwned->federation->id);

            case $user->isClubPresident() && $user->clubOwned:
                return $query->where('id', '=', $user->clubOwned->association->federation->id);
            default:
                throw new AuthorizationException();

        }
    }

    public static function fillSelect2($user)
    {
        $federations = new Collection();

        if ($user->isSuperAdmin()) {
            // User is SuperAdmin
            $federations = Federation::get(['id as value', 'name as text']);
        } else if ($user->isFederationPresident()) {
            $federations = $user->federationOwned()->get(['id as value', 'name as text']);
        } else if ($user->isAssociationPresident()) {
            $federations = $user->associationOwned->federation()->get(['id as value', 'name as text']);
        } else if ($user->isClubPresident()) {
            $federations = $user->clubOwned->association->federation()->get(['id as value', 'name as text']);
        }

        return $federations;
    }


    public static function fillSelect($user)
    {
        $federations = new Collection();

        if ($user->isSuperAdmin()) {
            // User is SuperAdmin
            $federations = Federation::pluck('name', 'id');
        } else if ($user->isFederationPresident()) {
            $federation = $user->federationOwned;
            $federations->push($federation);
            $federations = $federations->pluck('name', 'id');
        } else if ($user->isAssociationPresident()) {
            $federation = $user->associationOwned->federation;
            $federations->push($federation);
            $federations = $federations->pluck('name', 'id');
        } else if ($user->isClubPresident()) {
            $federation = $user->clubOwned->association->federation;
            $federations->push($federation);
            $federations = $federations->pluck('name', 'id');
        }

        return $federations;
    }

}