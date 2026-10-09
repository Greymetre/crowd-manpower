<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Candidate extends Model
{
    /** @use HasFactory<\Database\Factories\CandidateFactory> */
    use HasFactory;

    public const GENDERS = ['Male', 'Female', 'Other'];

    public const MARITAL_STATUSES = ['Single', 'Married', 'Divorced', 'Widowed'];

    /**
     * Column key => label, in the same order as the client's form.
     * Shared by the listing, Excel/PDF export and import.
     */
    public const COLUMNS = [
        'id' => 'Sr No',
        'name' => 'Name',
        'id_no' => 'Id',
        'working' => 'Working',
        'address' => 'Address',
        'village' => 'Village',
        'tehsil' => 'Tehsil',
        'district' => 'District',
        'state' => 'State',
        'pincode' => 'Pincode',
        'dob' => 'DOB',
        'age' => 'Age',
        'marital_status' => 'Marital Status',
        'gender' => 'Gender',
        'education' => 'Education',
        'applied_date' => 'Applied Date',
    ];

    protected $fillable = [
        'name', 'id_no', 'working', 'address', 'village', 'tehsil', 'district', 'state',
        'pincode', 'dob', 'age', 'marital_status', 'gender', 'education', 'applied_date', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'applied_date' => 'date',
            'age' => 'integer',
        ];
    }

    public static function ageFromDob(mixed $dob): ?int
    {
        return $dob ? Carbon::parse($dob)->age : null;
    }

    /**
     * Value of a column formatted for display / export.
     */
    public function display(string $key): string
    {
        $value = $this->{$key};

        return $value instanceof Carbon ? $value->format('d-m-Y') : (string) ($value ?? '');
    }

    /**
     * Apply the listing filters from the request (search, gender, district, date range).
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                $q->where(function (Builder $q) use ($term) {
                    $like = '%'.$term.'%';
                    $q->where('name', 'like', $like)
                        ->orWhere('id_no', 'like', $like)
                        ->orWhere('address', 'like', $like)
                        ->orWhere('village', 'like', $like)
                        ->orWhere('tehsil', 'like', $like)
                        ->orWhere('district', 'like', $like)
                        ->orWhere('pincode', 'like', $like);

                    if (ctype_digit($term)) {
                        $q->orWhere('id', (int) $term);
                    }
                });
            })
            ->when($filters['gender'] ?? null, fn (Builder $q, $v) => $q->where('gender', $v))
            ->when($filters['district'] ?? null, fn (Builder $q, $v) => $q->where('district', $v))
            ->when($filters['from'] ?? null, fn (Builder $q, $v) => $q->whereDate('applied_date', '>=', $v))
            ->when($filters['to'] ?? null, fn (Builder $q, $v) => $q->whereDate('applied_date', '<=', $v));
    }
}
