<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFormRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }

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
        if ($this->method() == 'POST') {
            return [
                'name' => 'required',
                'email' => ['required', Rule::unique('admins', 'email')],
                'password' => 'required|min:8',
                'roles' => 'required',
            ];
        } else {
            return [
                'name' => 'required',
                'email' => ['required', Rule::unique('admins', 'email')->ignore($this->user->id)],
                'roles' => 'required',
            ];
        }
    }

    public function messages()
    {
        return [
            'email.unique' => __('The email has already been taken.'),
        ];
    }
}
