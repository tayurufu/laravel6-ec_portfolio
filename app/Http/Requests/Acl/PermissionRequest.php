<?php

namespace App\Http\Requests\Acl;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'guard_name' => 'string|max:255'
        ];
    }

    protected function failedValidation(Validator $validator) {
        $res = response()->json([
           // 'errors' => $validator->errors(),
            'message' => $validator->messages()->first()
        ], 400);
        throw new HttpResponseException($res);
    }
}
