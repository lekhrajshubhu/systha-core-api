<?php

namespace Systha\Core\Models;



use Illuminate\Database\Eloquent\Model;


class SurveyQuestionModel extends Model
{
    protected $table = "survey_questions";
    protected $guarded = [];

    public function survey()
    {
        return $this->belongsTo(SurveyModel::class, 'survey_group_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(SurveyGroupModel::class, 'survey_group_id', 'id');
    }

    public function options()
    {
        return $this->hasMany(SurveyQuestionOptionModel::class, 'question_id', 'id');
    }
}
