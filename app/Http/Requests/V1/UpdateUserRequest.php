<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $method = $this->method();
        return [
            "first_name" => ["sometimes"],
            "last_name" => ["sometimes"],
            "phone" => ["sometimes", "unique:users,phone", "max:11"],
            "location" => ["sometimes"],
            "password" => ["sometimes", "min:8"],
            "token" => ["sometimes"],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->imageSource) {
            $this->merge([
                "image_source" => $this->imageSource,
            ]);
        }

        if ($this->firstName) {
            $this->merge([
                "first_name" => $this->firstName,
            ]);
        }

        if($this->lastName){
            $this->merge([
                "last_name" => $this->lastName,
            ]);
        }
    }
}
