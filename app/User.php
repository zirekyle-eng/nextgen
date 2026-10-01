<?php

namespace App;

use App\Models\BloodGroup;
use App\Models\Lga;
use App\Models\Nationality;
use App\Models\StaffRecord;
use App\Models\State;
use App\Models\StudentRecord;
use App\Models\Mark;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'phone', 'phone2', 'dob', 'gender', 'photo', 'address', 'bg_id', 'password', 'nal_id', 'state_id', 'lga_id', 'code', 'user_type', 'email_verified_at', 'religion_status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function student_record()
    {
        return $this->hasOne(StudentRecord::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class, 'nal_id');
    }

    public function blood_group()
    {
        return $this->belongsTo(BloodGroup::class, 'bg_id');
    }

    public function staff()
    {
        return $this->hasMany(StaffRecord::class);
    }

    public function clubs()
    {
        return $this->belongsToMany(
            \App\Models\Club::class,
            'club_members',
            'user_id',
            'club_id'
        )->using(\App\Models\ClubMember::class);
    }

    public function my_students()
    {
        return $this->hasMany(StudentRecord::class, 'my_parent_id');
    }

    public function marks()
    {
        return $this->hasMany(Mark::class, 'student_id', 'id');
    }

    /**
     * Scope: Get teachers/advisors (معلمين/مرشدين)
     */
    public function scopeTeachers($query)
    {
        return $query->where('user_type', 'teacher');
    }

    /**
     * Scope: Get parents (أولياء أمور)
     */
    public function scopeParents($query)
    {
        return $query->where('user_type', 'parent');
    }

    /**
     * Scope: Get by user type
     */
    public function scopeByUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Check if user is a teacher/advisor
     */
    public function isTeacher()
    {
        return $this->user_type === 'teacher';
    }

    /**
     * Check if user is a parent
     */
    public function isParent()
    {
        return $this->user_type === 'parent';
    }

    /**
     * Get the associated guardian if user is a parent
     */
    public function getGuardian()
    {
        if ($this->isParent()) {
            return \App\Models\Guardian::where('email', $this->email)->first();
        }
        return null;
    }
}
