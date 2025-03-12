<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class EmployeeUpdateRequest extends FormRequest
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
            'email' => 'required|email',
            'phone_no' => 'required|min:6',
            'about' => 'required|string',
            'ss_number' => 'required',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'other' => 'nullable|file|mimes:pdf,doc,docx,png,jpeg|max:2048',
            'address' => 'required|min:3|string',
            'latitude' => 'required',
            'longitude' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'other.required' => 'Document(other field) is required',
            'other.file' => 'Document(other field) must be valid file',
            'other.max' => 'Document(other field) accepted only less than 2MB',
            'other.mimes' => 'Document(other field) format is not valid'
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
