<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    private function attempt(string $password)
    {
        return Livewire::test(Login::class)
            ->fillForm(['email' => 'admin@example.com', 'password' => $password])
            ->call('authenticate');
    }

    public function test_correct_password_logs_in(): void
    {
        User::factory()->create(['email' => 'admin@example.com', 'password' => 'secret123']);

        $this->attempt('secret123');

        $this->assertAuthenticated();
    }

    public function test_after_5_wrong_passwords_login_is_blocked_for_15_minutes(): void
    {
        User::factory()->create(['email' => 'admin@example.com', 'password' => 'secret123']);

        for ($i = 0; $i < 5; $i++) {
            $this->attempt('wrong')->assertHasFormErrors(['email']);
        }

        // 6-я попытка даже с верным паролем не проходит
        $this->attempt('secret123');
        $this->assertGuest();

        // Через 14 минут всё ещё заблокировано
        $this->travel(14)->minutes();
        $this->attempt('secret123');
        $this->assertGuest();

        // Через 16 минут — пускает
        $this->travel(2)->minutes();
        $this->attempt('secret123');
        $this->assertAuthenticated();
    }
}
