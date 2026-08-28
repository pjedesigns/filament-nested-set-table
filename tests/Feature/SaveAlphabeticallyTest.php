<?php

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NodeTrait;
use Pjedesigns\FilamentNestedSetTable\Pages\OrderPage;

beforeEach(function () {
    Schema::create('repro_people', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('last_name');
        $table->unsignedBigInteger('_lft')->default(0);
        $table->unsignedBigInteger('_rgt')->default(0);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('repro_people');
});

class ReproPerson extends Model
{
    use NodeTrait;

    protected $table = 'repro_people';

    protected $fillable = ['first_name', 'last_name'];
}

class ReproPersonResource extends Filament\Resources\Resource
{
    protected static ?string $model = ReproPerson::class;

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }
}

class ReproOrderPage extends OrderPage
{
    protected static string $resource = ReproPersonResource::class;

    public array $errors = [];

    public array $successes = [];

    public function getAlphabeticalOrderField(): array
    {
        return ['first_name', 'last_name'];
    }

    public function getMaxDepth(): int
    {
        return 0;
    }

    protected function notifySuccess(string $message): void
    {
        $this->successes[] = $message;
    }

    protected function notifyError(string $message): void
    {
        $this->errors[] = $message;
    }
}

it('sorts a flat list made of two sorted runs in one pass', function () {
    // Mimic the real-world data: a sorted run followed by a second sorted run
    // (records appended later), exactly like the reported scenario.
    $run1 = [
        ['Alexa', 'Brooke'], ['Angelina', 'Elise'], ['Ashley', 'Jay'],
        ['Becky', 'Holt'], ['Bella', 'Mendez'], ['Brooke', 'Lea'],
        ['Carmen', 'Lee'], ['Charlie', 'Rose'], ['Chloe', 'Dee'],
        ['Danielle', 'Maye'], ['Diva', 'Ivy'], ['Electra', 'Morgan'],
        ['Elle', 'Pharrell'], ['London', 'Lix'], ['Lucie', 'Jones'],
        ['Mia', 'Middleton'], ['Penny', 'Lee'], ['Rosie', 'Lee'],
        ['Sammi', 'Tye'], ['Vicky', 'Narni'],
    ];
    $run2 = [
        ['Emma', 'Green'], ['Felicity', 'Hill'], ['Harry', 'Amelia'],
        ['Holly', 'Gibbons'], ['Jasmine', 'Jones'], ['Jasmine', 'Marie'],
        ['Jess', 'West'], ['Jessie', 'Jenson'], ['Kaitlin', 'Grey'],
        ['Kayla', 'Louise'], ['Kiki', 'Divine'], ['Lady', 'Natt'],
        ['Lizzie', 'Murphy'],
    ];

    foreach (array_merge($run1, $run2) as [$first, $last]) {
        ReproPerson::create(['first_name' => $first, 'last_name' => $last]);
    }

    expect(ReproPerson::isBroken())->toBeFalse();

    $page = new ReproOrderPage;
    $page->saveAlphabetically();

    $actual = ReproPerson::defaultOrder()
        ->get()
        ->map(fn ($p) => $p->first_name.' '.$p->last_name)
        ->all();

    $expected = collect(array_merge($run1, $run2))
        ->sort(fn ($a, $b) => strnatcasecmp($a[0], $b[0]) ?: strnatcasecmp($a[1], $b[1]))
        ->map(fn ($p) => $p[0].' '.$p[1])
        ->values()
        ->all();

    expect($page->errors)->toBe([], 'saveAlphabetically swallowed: '.implode(' | ', $page->errors))
        ->and($actual)->toBe($expected);
});
