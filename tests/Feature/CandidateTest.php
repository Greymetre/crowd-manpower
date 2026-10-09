<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class CandidateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/candidates')->assertRedirect('/');
        $this->get('/candidates/create')->assertRedirect('/');
    }

    public function test_save_record_stays_on_the_record_and_calculates_age(): void
    {
        $response = $this->actingAs($this->user)->post('/candidates', [
            'name' => 'Ramesh Kumar',
            'dob' => now()->subYears(30)->subDay()->format('Y-m-d'),
            'gender' => 'Male',
            'pincode' => '303702',
            'action' => 'save',
        ]);

        $candidate = Candidate::sole();
        $response->assertRedirect(route('candidates.edit', $candidate));
        $this->assertSame(30, $candidate->age);
        $this->assertSame($this->user->id, $candidate->created_by);
    }

    public function test_submit_goes_back_to_the_listing(): void
    {
        $candidate = Candidate::factory()->create();

        $this->actingAs($this->user)
            ->put("/candidates/{$candidate->id}", ['name' => 'Updated Name', 'action' => 'submit'])
            ->assertRedirect(route('candidates.index'));

        $this->assertSame('Updated Name', $candidate->fresh()->name);
    }

    public function test_validation_errors(): void
    {
        $this->actingAs($this->user)
            ->post('/candidates', ['name' => '', 'pincode' => '12', 'gender' => 'X'])
            ->assertSessionHasErrors(['name', 'pincode', 'gender']);

        $this->assertDatabaseCount('candidates', 0);
    }

    public function test_form_navigation_buttons_point_to_neighbouring_records(): void
    {
        [$a, $b, $c] = Candidate::factory()->count(3)->create();

        $this->actingAs($this->user)->get("/candidates/{$b->id}/edit")
            ->assertOk()
            ->assertSee(route('candidates.edit', $a), false)
            ->assertSee(route('candidates.edit', $c), false);

        // New record: "Previous" jumps to the last saved record, Sr No is next ID
        $this->actingAs($this->user)->get('/candidates/create')
            ->assertOk()
            ->assertSee(route('candidates.edit', $c), false)
            ->assertSeeInOrder(['Sr No', (string) ($c->id + 1)]);
    }

    public function test_listing_search_and_filters(): void
    {
        Candidate::factory()->create(['name' => 'Sunita Devi', 'gender' => 'Female', 'district' => 'Ajmer']);
        Candidate::factory()->create(['name' => 'Mahesh Singh', 'gender' => 'Male', 'district' => 'Kota']);

        $this->actingAs($this->user)->get('/candidates?q=Sunita')
            ->assertOk()->assertSee('Sunita Devi')->assertDontSee('Mahesh Singh');

        $this->actingAs($this->user)->get('/candidates?gender=Male')
            ->assertSee('Mahesh Singh')->assertDontSee('Sunita Devi');
    }

    public function test_delete(): void
    {
        $candidate = Candidate::factory()->create();

        $this->actingAs($this->user)->delete("/candidates/{$candidate->id}")->assertRedirect();
        $this->assertModelMissing($candidate);
    }

    public function test_excel_export_respects_filters(): void
    {
        Candidate::factory()->create(['name' => 'Sunita Devi', 'gender' => 'Female']);
        Candidate::factory()->create(['name' => 'Mahesh Singh', 'gender' => 'Male']);

        $response = $this->actingAs($this->user)->get('/candidates/export/excel?gender=Female');
        $response->assertOk()->assertDownload();

        $rows = $this->readXlsx($response->baseResponse->getFile()->getPathname());
        $this->assertSame(array_values(Candidate::COLUMNS), $rows[0]);
        $this->assertCount(2, $rows);
        $this->assertSame('Sunita Devi', $rows[1][1]);
    }

    public function test_pdf_export(): void
    {
        Candidate::factory()->count(60)->create();

        $response = $this->actingAs($this->user)->get('/candidates/export/pdf');

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_import_from_excel(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        $writer = new Writer();
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['Name', 'ID', 'Village', 'DOB', 'Gender', 'Marital Status', 'Applied Date', 'Pincode']));
        $writer->addRow(Row::fromValues(['Ramesh Kumar', 'CMS-1', 'Chomu', '15-08-1995', 'm', 'married', '01/10/2026', 303702]));
        $writer->addRow(Row::fromValues(['', '', '', '', '', '', '', '']));                       // blank, ignored
        $writer->addRow(Row::fromValues(['', 'CMS-2', 'Amer', '', '', '', '', '']));              // missing name
        $writer->addRow(Row::fromValues(['Sita', '', '', 'not a date', 'Female', '', '', '']));   // bad date
        $writer->close();

        $response = $this->actingAs($this->user)->post('/candidates/import', [
            'file' => new UploadedFile($path, 'candidates.xlsx', null, null, true),
        ]);

        $response->assertRedirect(route('candidates.import'))
            ->assertSessionHas('import_error_total', 2);

        $c = Candidate::sole();
        $this->assertSame('Ramesh Kumar', $c->name);
        $this->assertSame('CMS-1', $c->id_no);
        $this->assertSame('1995-08-15', $c->dob->format('Y-m-d'));
        $this->assertSame('Male', $c->gender);
        $this->assertSame('Married', $c->marital_status);
        $this->assertSame('2026-10-01', $c->applied_date->format('Y-m-d'));
        $this->assertSame('303702', $c->pincode);
        $this->assertSame($c->dob->age, $c->age);
    }

    public function test_import_rejects_file_without_name_column(): void
    {
        $file = UploadedFile::fake()->createWithContent('x.csv', "Foo,Bar\n1,2\n");

        $this->actingAs($this->user)->post('/candidates/import', ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_pages_render(): void
    {
        Candidate::factory()->count(3)->create();

        foreach (['/dashboard', '/candidates', '/candidates/create', '/candidates/import'] as $url) {
            $this->actingAs($this->user)->get($url)->assertOk();
        }
        $this->actingAs($this->user)->get('/candidates/import/template')->assertOk()->assertDownload();
    }

    private function readXlsx(string $path): array
    {
        $reader = new Reader();
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
        }
        $reader->close();

        return $rows;
    }
}
