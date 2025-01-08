<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            "phone" => ["required","unique:users,phone","max:11"],
            "password" => ["required","min:8","string"],
        ];
    }

    public function messages()
    {
        return [
            "phone.unique" => "this phone has used before plz try another one",
            "phone.max" => "this phone must not be greater than 11 numbers",
        ];
    }
}
