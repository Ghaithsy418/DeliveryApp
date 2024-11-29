<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        if($method === "PUT"){
            return [
                "name" => ["required"],
                "ingredients" => ["required"],
                "type" => ["required"],
                "price" => ["required"],
                "count" => ["required"],
                "image_source" => ["sometimes","string"],
            ];
        }else{
            return [
                "name" => ["sometimes"],
                "ingredients" => ["sometimes"],
                "type" => ["sometimes"],
                "price" => ["sometimes"],
                "count" => ["sometimes"],
                "image_source" => ["sometimes","string"],
            ];
        }
    }

    public function prepareForValidation(){
        if($this->imageSource){
            $this->merge([
                "image_source" => $this->imageSource,
            ]);
        }
    }
}
