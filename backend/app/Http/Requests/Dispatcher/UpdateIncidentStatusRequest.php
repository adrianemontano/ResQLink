<?php

namespace App\Http\Requests\Dispatcher;

use App\Models\Incident;
use App\Models\ResponseTeam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['dispatcher', 'admin']) === true;
    }

    public function rules(): array
    {
        $externalTeamNameRule = $this->input('external_team_name') && is_array($this->input('external_team_name'))
            ? ['nullable', 'array']
            : ['nullable', 'string', 'max:100'];

        return [
            'status'             => ['required', Rule::in(['Reported', 'Received', 'Dispatched', 'Completed'])],
            'changed_at'         => ['required', 'date'],
            'response_team_id'   => [
                Rule::requiredIf(fn () => $this->input('status') === 'Dispatched'
                    && ! $this->filled('external_team_name')
                    && empty($this->input('dispatch_team_ids'))),
                'nullable', 'integer', 'exists:response_teams,id',
            ],
            'external_team_name' => $externalTeamNameRule,
            'external_team_name.*' => ['nullable', 'string', 'max:100', 'distinct'],
            'external_team_contact' => ['nullable', 'array'],
            'external_team_contact.*' => ['nullable', 'string', 'max:50'],
            'dispatch_date'      => ['nullable', 'date'],
            'dispatch_time'      => ['nullable', 'date_format:H:i'],
            'notes'              => ['required', 'string', 'max:300'],
            'cancel_team_ids'     => ['nullable', 'array'],
            'cancel_team_ids.*'   => ['integer', 'exists:response_teams,id'],
            'dispatch_team_ids'    => ['nullable', 'array'],
            'dispatch_team_ids.*'  => ['integer', 'exists:response_teams,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'response_team_id.required' => 'Please select a response team or enter an external team name.',
            'changed_at.required'       => 'Date and time is required.',
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v) {
            $status = $this->input('status');
            $teamId = $this->input('response_team_id');
            $dispatchTeamIds = $this->input('dispatch_team_ids', []);
            $externalTeamNames = $this->input('external_team_name', []);
            $dispatchDate = $this->input('dispatch_date');
            $dispatchTime = $this->input('dispatch_time');

            if ($status !== 'Dispatched') {
                return;
            }

            // Validate at least one team is selected
            if (empty($dispatchTeamIds) && empty($externalTeamNames) && empty($teamId)) {
                $v->errors()->add('dispatch_team_ids', 'Please select at least one response team or add an external team.');
                return;
            }

            // Validate dispatch date/time is not in the future
            if ($dispatchDate && $dispatchTime) {
                $dispatchDateTime = \Carbon\Carbon::parse("{$dispatchDate} {$dispatchTime}", 'Asia/Manila');
                $now = \Carbon\Carbon::now('Asia/Manila');
                if ($dispatchDateTime->gt($now)) {
                    $v->errors()->add('dispatch_date', 'Dispatch date and time cannot be in the future.');
                }
            }

            $incident = $this->route('incident');

            // Validate dispatch_team_ids array
            if (! empty($dispatchTeamIds)) {
                foreach ($dispatchTeamIds as $dispatchTeamId) {
                    $busy = Incident::query()
                        ->where('status', 'Dispatched')
                        ->whereHas('responseTeams', fn($q) => $q->where('response_team_id', $dispatchTeamId)->where('status', 'active'))
                        ->when($incident, fn ($q) => $q->where('id', '!=', $incident->id))
                        ->first();

                    if ($busy) {
                        $incNo = 'INC-' . str_pad($busy->id, 4, '0', STR_PAD_LEFT);
                        $v->errors()->add(
                            'dispatch_team_ids',
                            "Team ID {$dispatchTeamId} is currently dispatched to {$incNo}. Complete or cancel that dispatch first."
                        );
                    }

                    $team = ResponseTeam::find($dispatchTeamId);
                    if ($team && ! $team->is_active) {
                        $v->errors()->add('dispatch_team_ids', "Team ID {$dispatchTeamId} is currently inactive and cannot be dispatched.");
                    }
                }
            }

            // Legacy validation for single response_team_id
            if ($teamId) {
                $busy = Incident::query()
                    ->where('status', 'Dispatched')
                    ->where('dispatched_team_id', $teamId)
                    ->when($incident, fn ($q) => $q->where('id', '!=', $incident->id))
                    ->first();

                if ($busy) {
                    $incNo = 'INC-' . str_pad($busy->id, 4, '0', STR_PAD_LEFT);
                    $v->errors()->add(
                        'response_team_id',
                        "This team is currently dispatched to {$incNo}. Complete or cancel that dispatch first."
                    );
                }

                $team = ResponseTeam::find($teamId);
                if ($team && ! $team->is_active) {
                    $v->errors()->add('response_team_id', 'This team is currently inactive and cannot be dispatched.');
                }
            }
        });
    }
}
