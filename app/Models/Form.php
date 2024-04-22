<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // format question
    public function getFormattedQuestionsAttribute()
    {
        $questions = $this->questions->toArray();
        return array_map(function($question)
        {
            return [
                'id' => $question['id'],
                'form_id' => $question['form_id'],
                'name' => $question['name'],
                'choice_type' => $question['choice_type'],
                'choices' => $question['choices'],
                'is_required' => $question['is_required'],
            ];
        }, $questions);
    }



    public function allowedDomains()
    {
        return $this->hasMany(AllowedDomain::class);
    }

    // format atribute allowed domains
    public function getFormattedAllowedDomainsAttribute()
    {
        $alloweodDomains = $this->allowedDomains->toArray();
        return array_map(function($alloweodDomain)
        {
            return $alloweodDomain['domain'];
        }, $alloweodDomains);
    }
}
