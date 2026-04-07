<?php

namespace Systha\Core\Models;



use Illuminate\Database\Eloquent\Model;


class SurveyQuestionOptionModel extends Model
{
    protected $table = "survey_question_options";
    protected $guarded = [];

        public function question()
        {
            return $this->belongsTo(SurveyQuestionModel::class, 'question_id', 'id');
        }
}
