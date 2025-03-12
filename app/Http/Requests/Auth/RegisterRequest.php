<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;


class RegisterRequest extends FormRequest
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
        $rule = [
            'name' => 'required|min:3|string',
            'role' => 'required|string|in:professional,corporate,user,employee',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
            'about' => 'required|string'
        ];

        $role = $this->input('role');

        if ($role == "professional") {
            $rule['ss_number'] = 'required';
            $rule['profile'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
            $rule['certificate'] = 'required|file|mimes:pdf,doc,docx,png,jpeg|max:2048';
            $rule['other'] = 'required|file|mimes:pdf,doc,docx,png,jpeg|max:2048';
            $rule['address'] = 'required|string';
            $rule['longitude'] = 'required';
            $rule['latitude'] = 'required';
        } else if ($role == "corporate") {
            $rule['ein_number'] = 'required';
            $rule['logo'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
            $rule['tax_id_number'] = 'required|numeric|min:8';
            $rule['registration_number'] = 'required|numeric';
            $rule['work_permit_id'] = 'required|numeric';
            $rule['other'] = 'required|file|mimes:pdf,doc,docx,png,jpeg|max:2048';
            $rule['phone_no'] = 'required|min:6';
            $rule['address'] = 'required|string';
            $rule['longitude'] = 'required';
            $rule['latitude'] = 'required';
        } else if ($role == "user") {
            $rule['ss_number'] = 'required';
            $rule['profile'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
        }

        return $rule;
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
