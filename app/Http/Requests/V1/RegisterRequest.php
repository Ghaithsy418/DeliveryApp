<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

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
            "first_name" => ["required"],
            "last_name" => ["required"],
            "phone" => ["required","unique:users,phone","max:11"],
            "location" => ["required"],
            "password" => ["required","min:8","string"],
        ];
    }

    protected function prepareForValidation()
    {
        if($this->imageSource){
            $this->merge([
                "image_source" => $this->imageSource,
            ]);
        }

        $this->merge([
            "first_name" => $this->firstName,
            "last_name" => $this->lastName,
            "password_confirmation" => $this->passwordConfirmation,
        ]);
    }

    public function messages()
    {
        return [
            "phone.unique" => "this phone is used before plz try another one",
            "phone.max" => "this phone must not be greater than 11 numbers",
        ];
    }
}
