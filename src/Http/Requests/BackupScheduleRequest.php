<?php

namespace MahmoudMhamed\BackupBrowse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BackupScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'time' => ['nullable', 'date_format:H:i'],
            'day_of_week' => ['nullable', 'integer', 'between:0,6'],
            'day_of_month' => ['nullable', 'integer', 'between:1,31'],
            'only_db' => ['nullable', 'boolean'],
            'only_files' => ['nullable', 'boolean'],
            'enabled' => ['nullable', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $data['only_db'] = $this->boolean('only_db');
        $data['only_files'] = $this->boolean('only_files');
        $data['enabled'] = $this->boolean('enabled');

        return $data;
    }
}
