<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLinkRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'target_url' => ['required','url','max:2048'],
            'slug'       => ['nullable','alpha_num','min:3','max:32','unique:links,slug'],
            'expires_at' => ['nullable','date','after:now'],
        ];
    }
}
