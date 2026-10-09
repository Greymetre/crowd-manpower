<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidateRequest;
use App\Models\Candidate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public const FILTERS = ['q', 'gender', 'district', 'from', 'to'];

    public function index(Request $request): View
    {
        $filters = $request->only(self::FILTERS);

        $candidates = Candidate::query()
            ->filter($filters)
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $districts = Candidate::query()
            ->whereNotNull('district')->where('district', '!=', '')
            ->distinct()->orderBy('district')->pluck('district');

        return view('candidates.index', compact('candidates', 'filters', 'districts'));
    }

    public function create(): View
    {
        return view('candidates.form', [
            'candidate' => new Candidate(['applied_date' => now()]),
            'srNo' => (Candidate::max('id') ?? 0) + 1,
            'nav' => $this->navigation(null),
        ]);
    }

    public function store(CandidateRequest $request): RedirectResponse
    {
        $candidate = Candidate::create($request->candidateData() + ['created_by' => $request->user()->id]);

        return $this->afterSave($request, $candidate, 'Record #'.$candidate->id.' saved.');
    }

    public function edit(Candidate $candidate): View
    {
        return view('candidates.form', [
            'candidate' => $candidate,
            'srNo' => $candidate->id,
            'nav' => $this->navigation($candidate),
        ]);
    }

    public function update(CandidateRequest $request, Candidate $candidate): RedirectResponse
    {
        $candidate->update($request->candidateData());

        return $this->afterSave($request, $candidate, 'Record #'.$candidate->id.' updated.');
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        $candidate->delete();

        return back()->with('status', 'Record #'.$candidate->id.' deleted.');
    }

    /**
     * "Save Record" stays on the record, "Submit" goes back to the listing.
     */
    private function afterSave(Request $request, Candidate $candidate, string $message): RedirectResponse
    {
        if ($request->input('action') === 'submit') {
            return redirect()->route('candidates.index')->with('status', $message);
        }

        return redirect()->route('candidates.edit', $candidate)->with('status', $message);
    }

    /**
     * IDs for the First / Previous / Next / Last buttons.
     * On a new (unsaved) record, "Previous" points at the last saved record.
     */
    private function navigation(?Candidate $current): array
    {
        $first = Candidate::min('id');
        $last = Candidate::max('id');

        if (! $current) {
            return ['first' => $first, 'prev' => $last, 'next' => null, 'last' => $last];
        }

        return [
            'first' => $first !== $current->id ? $first : null,
            'prev' => Candidate::where('id', '<', $current->id)->max('id'),
            'next' => Candidate::where('id', '>', $current->id)->min('id'),
            'last' => $last !== $current->id ? $last : null,
        ];
    }
}
