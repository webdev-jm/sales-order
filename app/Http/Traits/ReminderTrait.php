<?php

namespace App\Http\Traits;

use App\Models\Reminders;

Trait ReminderTrait
{

    public function setReminder($model, $model_id, $message = '', $user_ids = [], $link = '', $due_date = NULL) {
        $current_date = date('Y-m-d H:i:s');
        $user_id = auth()->user()->id;

        $model = "App\Models\\".$model;
        
        // calculate due date
        if(empty($due_date)) {
            $due_date_reminder = 3;
            // add remider due date to current date
            $due_date = date('Y-m-d', strtotime($current_date.' + '.$due_date_reminder.' days'));
        }

        // add comma(,) separator to user ids
        $user_ids = ','.implode(',', $user_ids).',';

        $reminder = new Reminders([
            'user_id' => $user_id,
            'user_ids' => $user_ids,
            'date' => $current_date,
            'due_date' => $due_date,
            'model_type' => $model,
            'model_id' => $model_id,
            'message' => $message,
            'link' => $link,
            'status' => NULL,
        ]);
        $reminder->save();
    }
}