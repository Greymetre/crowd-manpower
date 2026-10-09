<?php

namespace App\Http\Requests;

use App\Models\Candidate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Shared with the Excel/CSV import so both paths validate the same way.
     */
    public static function fieldRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'id_no' => ['nullable', 'string', 'max:50'],
            'working' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:100'],
            'tehsil' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'digits:6'],
            'dob' => ['nullable', 'date', 'before:today'],
            'age' => ['nullable', 'integer', 'between:0,120'],
            'marital_status' => ['nullable', Rule::in(Candidate::MARITAL_STATUSES)],
            'gender' => ['nullable', Rule::in(Candidate::GENDERS)],
            'education' => ['nullable', 'string', 'max:100'],
            'applied_date' => ['nullable', 'date'],
        ];
    }

    public function rules(): array
    {
        return self::fieldRules();
    }

    public function attributes(): array
    {
        return [
            'id_no' => 'Id',
            'dob' => 'DOB',
            'marital_status' => 'marital status',
            'applied_date' => 'applied date',
        ];
    }

    /**
     * Validated data with age derived from DOB when DOB is given.
     */
    public function candidateData(): array
    {
        $data = $this->validated();

        if (! empty($data['dob'])) {
            $data['age'] = Candidate::ageFromDob($data['dob']);
        }

        return $data;
    }
}
