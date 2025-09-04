<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;
// use Illuminate\Database\Eloquent\Model;

class AcademicPeer extends Model
{
    use HasFactory;

    protected $fillable = [
        'sso_id',
        'name',
        'front_degree',
        'behind_degree',
        'job_title',
        'department',
        'institution',
        'country',
        'institution_email',
        'personal_email',
        'year',
        'program_name',
        'expertise_field',
        'phone',
        'gender',
        'research_media',
        'social_media',
        'photo',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'unit_id',
        'editor_id',
    ];

    protected $casts = [
        'research_media' => 'object',
        'social_media' => 'object',
        'phone' => 'object'
    ];
    protected $appends = ['country_name'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function getCountryNameAttribute()
    {
        // AMBIL DATA NEGARA DARI COUNTRIES.JSON
        $countries = json_decode(file_get_contents(resource_path('json/countries.json')), true);

        return $countries[$this->country] ?? $this->country;
    }

}
