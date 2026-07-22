<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobCreateRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'category_id' => 'required',
            'role_id' => 'required',
            'profession_id' => 'required',
            'experience' => 'required',
            'education' => 'required',
            'job_type' => 'required',
            'gender' => 'nullable|in:any,male,female',
            'min_age' => 'nullable|integer|min:0|max:100',
            'max_age' => 'nullable|integer|min:0|max:100|gte:min_age',
            'experience_area' => 'nullable|string|max:255',
            'business_area' => 'nullable|in:sales,marketing,it,education,healthcare,garments_textile,finance_banking,customer_support,others',
            'business_area_other' => 'required_if:business_area,others|nullable|string|max:255',
            'experience_description' => 'nullable|string|max:2000',
            'required_degrees' => 'nullable|array',
            'required_degrees.*' => 'in:ba,bsc,bba,b_com,ma,msc,mba,diploma,hsc,ssc,others',
            'required_degrees_other' => 'nullable|string|max:255',
            'preferred_institutions' => 'nullable|array',
            'preferred_institutions.*' => 'nullable|string|max:255',
            'vacancies' => 'required',
            'salary_mode' => 'required',
            'custom_salary' => 'required_if:salary_mode,==,custom',
            'min_salary' => 'nullable|numeric',
            'max_salary' => 'nullable|numeric',
            'salary_type' => 'required',
            'deadline' => 'required|date',
            'job_start' => 'nullable|date',
            'job_end' => 'nullable|date|after_or_equal:job_start',
            'description' => 'required|string|min:50',
            'featured' => 'nullable|numeric',
            'is_remote' => 'nullable|numeric',
            'apply_on' => 'required',
            'location' => $this->method() == 'PUT' ? '' : Rule::requiredIf(! $this->hasLocation()),
        ];
    }

    private function hasLocation(): bool
    {
        return session()->has('location')
            || ($this->filled('location') && $this->filled('lat') && $this->filled('lng') && $this->filled('country'));
    }
}
