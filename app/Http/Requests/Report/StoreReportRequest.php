<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Report::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('description')) {
            $this->merge([
                'description' => strip_tags($this->input('description'), '<p><br><strong><em><u><s><ol><ul><li><blockquote><h2><h3><a><span>'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:50000'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => [
                'file',
                'max:5120', // 5MB
                'mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.max' => 'Anda boleh memuat naik maksimum 10 fail.',
            'attachments.*.max' => 'Setiap fail mestilah kurang daripada 5MB.',
            'attachments.*.mimes' => 'Hanya imej, PDF, dan dokumen Office dibenarkan.',
        ];
    }
}
