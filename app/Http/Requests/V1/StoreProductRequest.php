<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            "name" => ["required"],
            "description" => ["required"],
            "category" => ["required"],
            "price" => ["required"],
            "count" => ["required"],
            "store_id" => ["required"],

        ];
    }

    public function prepareForValidation()
    {
        //These ones to allow the Camel Case if the user enter it
        if ($this->soldCount) {
            $this->merge([
                "sold_count" => $this->soldCount,
            ]);
        }

        if ($this->imageSource) {
            $this->merge([
                "image_source" => $this->imageSource,
            ]);
        }

        //Here to convert it to Camel Case
        $this->merge([
            "store_id" => $this->storeId,
        ]);
    }
}
