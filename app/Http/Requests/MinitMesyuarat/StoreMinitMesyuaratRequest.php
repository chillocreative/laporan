<?php

namespace App\Http\Requests\MinitMesyuarat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMinitMesyuaratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bulan' => [
                'required',
                'date',
                Rule::unique('minit_mesyuarats')->where('user_id', $this->user()->id),
            ],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'bulan.unique' => 'Minit mesyuarat untuk bulan ini telah wujud. Sila kemaskini rekod sedia ada.',
            'file.required' => 'Sila muat naik fail minit mesyuarat.',
            'file.mimes' => 'Hanya fail PDF atau Word (DOC/DOCX) dibenarkan.',
            'file.max' => 'Saiz fail mestilah kurang daripada 5MB.',
        ];
    }
}
