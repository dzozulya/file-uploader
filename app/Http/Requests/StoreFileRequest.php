<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,docx',
                'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file.',
            'file.file' => 'Uploaded item must be a valid file.',
            'file.max' => 'The file size must not exceed 10 MB.',
            'file.mimes' => 'Only PDF and DOCX files are allowed.',
            'file.mimetypes' => 'Invalid file type detected.',
        ];
    }
}
