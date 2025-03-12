<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CorporateUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|min:3|string',
            'about' => 'required|string',
            'phone_no' => 'required|min:6',
            'ein_number' => 'required',
            'tax_id_number' => 'required|numeric|min:8',
            'registration_number' => 'required|numeric',
            'work_permit_id' => 'required|numeric',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'other' => 'nullable|file|mimes:pdf,doc,docx,png,jpeg|max:2048',
            'email' => ['required', 'email'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            new JsonResponse([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
