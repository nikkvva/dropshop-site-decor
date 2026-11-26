<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteImages;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Картинки сайта меняются в админке; без загрузки — заглушки шаблона. */
class SiteImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_uploads_template_placeholders_are_shown(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('bazaro/img/logo/logo.png')
            ->assertSee('bazaro/img/slider/slide-2-1.jpg')
            ->assertSee('bazaro/img/banner/banner-3-1.jpg');
    }

    public function test_uploaded_image_replaces_placeholder_and_old_file_is_deleted(): void
    {
        Storage::fake('uploads');
        $this->actingAs(User::factory()->create());

        Livewire::test(SiteImages::class)
            ->fillForm(['logo' => UploadedFile::fake()->image('logo.png', 345, 75)])
            ->call('save')
            ->assertHasNoFormErrors();
        $first = Setting::read('logo');
        Storage::disk('uploads')->assertExists($first);

        $this->get('/')
            ->assertSee('uploads/'.$first)
            ->assertDontSee('bazaro/img/logo/logo.png');

        // как в админке: старая картинка убрана крестиком, в поле только новая
        Livewire::test(SiteImages::class)
            ->fillForm(['logo' => [UploadedFile::fake()->image('new.png', 345, 75)]])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertNotSame($first, Setting::read('logo'));
        Storage::disk('uploads')->assertExists(Setting::read('logo'));
        Storage::disk('uploads')->assertMissing($first);
    }

    public function test_svg_is_rejected(): void
    {
        Storage::fake('uploads');
        $this->actingAs(User::factory()->create());

        Livewire::test(SiteImages::class)
            ->fillForm(['logo' => UploadedFile::fake()->create('logo.svg', 1, 'image/svg+xml')])
            ->call('save')
            ->assertHasFormErrors(['logo']);
        $this->assertNull(Setting::read('logo'));
    }

    public function test_guest_cannot_open_page(): void
    {
        $this->get('/admin/site-images')->assertRedirect('/admin/login');
    }
}
