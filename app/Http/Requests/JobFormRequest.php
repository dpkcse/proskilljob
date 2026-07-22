<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobFormRequest extends FormRequest
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
        if ($this->method() == 'PUT') {
            return [
                'title' => 'required|string|max:255',
                'company_id' => 'nullable',
                'company_name' => 'required_if:company_id,null',
                'category_id' => 'required|numeric',
                'role_id' => 'required|numeric',
                'profession_id' => 'required|numeric',
                'experience' => 'required',
                'education' => 'required',
                'vacancies' => 'required|string',
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
                'salary_mode' => 'required',
                'custom_salary' => 'required_if:salary_mode,==,custom',
                'min_salary' => $this->minSalaryRules(),
                'max_salary' => $this->maxSalaryRules(),
                'salary_type' => 'required',
                'deadline' => 'required',
                'job_start' => 'nullable|date',
                'job_end' => 'nullable|date|after_or_equal:job_start',
                'description' => 'required|string|min:50',
                'apply_on' => 'nullable',
                'apply_email' => 'nullable|email',
                'apply_url' => 'nullable|url',
                'featured' => 'nullable|numeric',
                'highlight' => 'nullable|numeric',
                'is_remote' => 'nullable|numeric',
            ];
        } elseif ($this->method() == 'POST') {
            return [
                'title' => 'required|string|max:255',
                'company_name' => 'required_if:company_id,null',
                'company_id' => 'required_if:company_name,null',
                'category_id' => 'required|numeric',
                'role_id' => 'required|numeric',
                'profession_id' => 'required|numeric',
                'experience' => 'required',
                'education' => 'required|numeric',
                'vacancies' => 'required|string',
                'job_type' => 'nullable',
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
                'salary_mode' => 'required',
                'custom_salary' => 'required_if:salary_mode,==,custom',
                'min_salary' => $this->minSalaryRules(),
                'max_salary' => $this->maxSalaryRules(),
                'salary_type' => 'nullable',
                'deadline' => 'required',
                'job_start' => 'nullable|date',
                'job_end' => 'nullable|date|after_or_equal:job_start',
                'description' => 'required|string|min:50',
                'apply_on' => 'nullable',
                'apply_email' => 'nullable|email',
                'apply_url' => 'nullable|url',
                'featured' => 'nullable|numeric',
                'highlight' => 'nullable|numeric',
                'is_remote' => 'nullable|numeric',
                'location' => Rule::requiredIf(! session('location')),
            ];
        }
    }

    private function minSalaryRules(): array
    {
        $rules = ['nullable', 'numeric'];

        if ($this->filled('max_salary')) {
            $rules[] = 'lte:max_salary';
        }

        return $rules;
    }

    private function maxSalaryRules(): array
    {
        $rules = ['nullable', 'numeric'];

        if ($this->filled('min_salary')) {
            $rules[] = 'gte:min_salary';
        }

        return $rules;
    }
}
