<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

/** Замена картинок сайта. Пустое поле — на сайте заглушка шаблона. */
class SiteImages extends Page
{
    /** ключ => [подпись, подсказка] */
    public const IMAGES = [
        'logo' => ['Логотип', 'Шапка, подвал, меню. 345×75, лучше PNG с прозрачным фоном'],
        'favicon' => ['Иконка вкладки', 'Только PNG, квадрат от 64×64'],
        'slide_1' => ['Слайд 1 — компьютер', '1400×660'],
        'slide_1_mobile' => ['Слайд 1 — телефон', '416×420'],
        'slide_2' => ['Слайд 2 — компьютер', '1400×660'],
        'slide_2_mobile' => ['Слайд 2 — телефон', '416×420'],
        'slide_3' => ['Слайд 3 — компьютер', '1400×660'],
        'slide_3_mobile' => ['Слайд 3 — телефон', '416×420'],
        'banner_1' => ['Баннер на главной — слева', '690×720'],
        'banner_2' => ['Баннер на главной — справа', '690×720'],
    ];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Картинки сайта';

    protected static ?string $title = 'Картинки сайта';

    public ?array $data = [];

    public function mount(): void
    {
        $keys = array_keys(self::IMAGES);
        $this->form->fill(array_combine($keys, array_map(Setting::read(...), $keys)));
    }

    public function form(Schema $schema): Schema
    {
        $fields = [];
        foreach (self::IMAGES as $key => [$label, $hint]) {
            $fields[] = FileUpload::make($key)
                ->label($label)
                ->helperText($hint.' · до 2 МБ')
                ->disk('uploads')
                ->directory('site')
                // SVG не принимаем: в нём может быть скрипт
                ->acceptedFileTypes($key === 'favicon' ? ['image/png'] : ['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(2048);
        }

        return $schema->components($fields)->columns(2)->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([Actions::make([Action::make('save')->label('Сохранить')->submit('save')])]),
        ]);
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $path) {
            $old = Setting::read($key);
            // заменили или убрали картинку — старый файл больше не нужен
            if ($old && $old !== $path) {
                Storage::disk('uploads')->delete($old);
            }
            Setting::write($key, $path);
        }

        Notification::make()->title('Сохранено')->success()->send();
    }
}
