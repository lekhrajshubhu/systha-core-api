<?php

namespace Systha\Core\Models;



use Illuminate\Database\Eloquent\Model;


class SurveyModel extends Model
{
    protected $table = "surveys";
    protected $guarded = [];
    
    public function groups()
    {
        return $this->hasMany(SurveyGroupModel::class, 'survey_id', 'id');
    }

     public function questions()
    {
        return $this->hasMany(SurveyQuestionModel::class,'survey_id','id');
    }
}