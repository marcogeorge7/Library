<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Pages\PrintBarcodes;
use App\Filament\Admin\Resources\PrintJobResource;
use App\Models\Book;
use App\Models\Category;
use App\Models\Copy;
use App\Models\Edition;
use App\Models\PrintJob;
use App\Models\Publisher;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintBarcodesTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): User
    {
        $role = Role::create(['name' => 'Admin']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user, 'web');
        Filament::setCurrentPanel(Filament::getPanel('admins'));

        return $user;
    }

    protected function makeCopy(): Copy
    {
        $book = Book::factory()->create(['series_id' => null]);
        $edition = Edition::factory()->create(['book_id' => $book->id]);

        return Copy::factory()->create(['edition_id' => $edition->id, 'is_printed' => false]);
    }

    public function test_editions_mode_lists_editions_and_prints_all_copies(): void
    {
        $user = $this->actingAsAdmin();

        $scratchFile = tempnam(sys_get_temp_dir(), 'escpos-test-');
        config(['printer.connector' => 'usb', 'printer.address' => $scratchFile]);

        $copy = $this->makeCopy();
        $edition = $copy->edition;

        Livewire::test(PrintBarcodes::class)
            ->assertSuccessful()
            ->set('mode', 'editions')
            ->callTableAction('print_all_copies', $edition)
            ->assertSuccessful();

        $copy->refresh();
        $this->assertTrue($copy->is_printed);
        $this->assertNotNull($copy->printed_at);

        $printJob = PrintJob::first();
        $this->assertNotNull($printJob);
        $this->assertSame($user->id, $printJob->user_id);
        $this->assertTrue($printJob->copies->pluck('id')->contains($copy->id));

        $this->assertFileExists($scratchFile);
        $this->assertStringContainsString($copy->barcode, file_get_contents($scratchFile));

        @unlink($scratchFile);
    }

    public function test_copies_mode_lists_copies_and_prints_selected(): void
    {
        $this->actingAsAdmin();

        $scratchFile = tempnam(sys_get_temp_dir(), 'escpos-test-');
        config(['printer.connector' => 'usb', 'printer.address' => $scratchFile]);

        $copy = $this->makeCopy();

        Livewire::test(PrintBarcodes::class)
            ->set('mode', 'copies')
            ->assertSuccessful()
            ->callTableAction('reprint', $copy)
            ->assertSuccessful();

        $copy->refresh();
        $this->assertTrue($copy->is_printed);

        @unlink($scratchFile);
    }

    public function test_print_history_resource_lists_jobs(): void
    {
        $this->actingAsAdmin();

        $scratchFile = tempnam(sys_get_temp_dir(), 'escpos-test-');
        config(['printer.connector' => 'usb', 'printer.address' => $scratchFile]);

        $copy = $this->makeCopy();
        app(\App\Services\PrintJobService::class)->print(collect([$copy]), null);

        $this->get(PrintJobResource::getUrl('index'))->assertSuccessful();
        $this->get(PrintJobResource::getUrl('view', ['record' => PrintJob::first()]))->assertSuccessful();

        @unlink($scratchFile);
    }
}
