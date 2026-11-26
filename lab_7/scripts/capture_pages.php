<?php

declare(strict_types=1);

use App\Models\Discount;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

config(['session.driver' => 'array']);
session()->start();

$outputDirectory = dirname(__DIR__).'/docs/lab7/report_assets/pages';
if (! is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0777, true);
}

/** @param array<string, mixed> $data */
function captureView(string $view, array $data, string $filename): void
{
    $html = view($view, $data)->render();
    $html = str_replace(
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        '../bootstrap.min.css',
        $html
    );
    $html = str_replace(
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        '../bootstrap.bundle.min.js',
        $html
    );

    file_put_contents($GLOBALS['outputDirectory'].'/'.$filename, $html);
}

function clearViewState(): void
{
    session()->forget(['success', 'error', '_old_input']);
    View::share('errors', new ViewErrorBag);
}

$events = Event::query()->orderBy('starts_at')->get();
$discounts = Discount::query()->orderBy('code')->get();
$tickets = Ticket::query()
    ->with(['event:id,name,venue,starts_at', 'discounts:id,code,name,type,value'])
    ->orderByDesc('id')
    ->paginate(10);
$ticket = Ticket::query()->with(['event', 'discounts'])->firstOrFail();

clearViewState();
captureView('home', [], '01-home.html');

clearViewState();
captureView('tickets.index', compact('tickets', 'events'), '02-ticket-catalog.html');

clearViewState();
captureView('tickets.create', compact('events', 'discounts'), '03-ticket-create.html');

session()->flash('_old_input', [
    'event_id' => '',
    'code' => '',
    'seat_number' => '',
    'price' => 'invalid',
    'customer_email' => 'wrong-address',
    'status' => 'available',
]);
$errorBag = new ViewErrorBag;
$errorBag->put('default', new MessageBag([
    'event_id' => ['Поле «Подія» є обов’язковим.'],
    'code' => ['Поле «Код квитка» є обов’язковим.'],
    'seat_number' => ['Поле «Місце» є обов’язковим.'],
    'price' => ['Поле «Ціна» повинно бути числом.'],
    'customer_email' => ['Вкажіть коректну електронну адресу.'],
]));
View::share('errors', $errorBag);
captureView('tickets.create', compact('events', 'discounts'), '04-ticket-validation.html');

clearViewState();
captureView('tickets.edit', compact('ticket', 'events', 'discounts'), '05-ticket-edit.html');

clearViewState();
session()->flash('success', "Квиток {$ticket->code} успішно оновлено.");
captureView('tickets.index', compact('tickets', 'events'), '06-ticket-success.html');

echo "Captured 6 Blade pages in {$outputDirectory}".PHP_EOL;
