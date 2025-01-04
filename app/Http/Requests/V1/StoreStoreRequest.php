<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->tokenCan("create");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => ["required","unique:stores,name"],
            "type" => ["required"],
            "description" => ["required"],
            "location" => ["required"],
        ];
    }

    public function prepareForValidation(){
        if($this->imageSource){
            $this->merge([
                "image_source" => $this->imageSource,
            ]);
        }
    }
}
