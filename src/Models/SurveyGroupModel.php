<?php

namespace Systha\Core\Models;

use Illuminate\Database\Eloquent\Model;


class SurveyGroupModel extends Model
{
    protected $table = "survey_groups";
    protected $guarded = [];
    

    public function survey(){
        return $this->belongsTo(SurveyModel::class, 'survey_id', 'id');
    }
     public function questions()
    {
        return $this->hasMany(SurveyQuestionModel::class, 'survey_group_id', 'id');
    }

}