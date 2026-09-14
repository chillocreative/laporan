<?php

namespace App\Http\Requests\MinitMesyuarat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMinitMesyuaratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bulan' => [
                'sometimes',
                'required',
                'date',
                Rule::unique('minit_mesyuarats')->ignore($this->route('minit_mesyuarat'))->where('user_id', $this->user()->id),
            ],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulan.unique' => 'Minit mesyuarat untuk bulan ini telah wujud. Sila kemaskini rekod sedia ada.',
            'file.mimes' => 'Hanya fail PDF atau Word (DOC/DOCX) dibenarkan.',
            'file.max' => 'Saiz fail mestilah kurang daripada 5MB.',
        ];
    }
}
