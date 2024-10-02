<?php

namespace Tests\Browser;

use Facebook\WebDriver\Exception\TimeoutException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Keyboard;
use Tests\DuskTestCase;

class RegistrationTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testEnsureInputFieldsLoaded(): void
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visitRoute('register')
                ->assertSee('Name')
                ->assertSee('Email')
                ->assertSee('Password')
                ->assertSee('Confirm Password');
        });
    }
    public function testPasswordShouldNotBeCopied(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visitRoute('register');

            $this->fillForm($browser, [
                '#name'                  => 'Rohan',
                '#email'                 => 'test@best.com',
                '#password'              => 'SecretPassword!Shhh....'
            ]);

            $browser->withKeyboard(fn(Keyboard $keyboard) => $keyboard->copyAll());
            $browser->click('#password_confirmation');
            $browser->withKeyboard(fn(Keyboard $keyboard) => $keyboard->paste());

            $browser->assertDontSeeIn('#password_confirmation', 'SecretPassword!Shhh....');
        });
    }

    public function testConfirmationPasswordShouldMatch(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visitRoute('register');

            $this->fillForm($browser, [
                '#name'                  => 'Rohan',
                '#email'                 => 'test@best.com',
                '#password'              => 'SecretPassword!Shhh....',
                '#password_confirmation' => 'SecretPassword!',
            ]);

            $browser->pressAndWaitFor('REGISTER', 2);

            $browser->assertSee('The password field confirmation does not match.');
        });
    }

    public function testPasswordShouldAtLeastBe8Character(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visitRoute('register');

            $this->fillForm($browser, [
                '#name'                  => 'Rohan',
                '#email'                 => 'test@best.com',
                '#password'              => 'Secret',
                '#password_confirmation' => 'Secret',
            ]);

            $browser->pressAndWaitFor('REGISTER', 2);

            $browser->assertSee('The password field must be at least 8 characters.');
        });
    }

    public function testRegistrationSuccessful(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visitRoute('register');

            $this->fillForm($browser, [
                '#name'                  => 'Rohan',
                '#email'                 => 'test@best.com',
                '#password'              => 'SecretPassword!Shhh....',
                '#password_confirmation' => 'SecretPassword!Shhh....',
            ]);

            $browser->press('REGISTER')->pause(2000);

            $browser->assertPathIs('/dashboard');
        });
    }

    public function fillForm(Browser $browser, $inputBindings = []): void
    {
        $inputBindings ??= [
            '#name'                  => 'Rohan',
            '#email'                 => 'test@best.com',
            '#password'              => 'SecretPassword!Shhh....',
            '#password_confirmation' => 'SecretPassword!Shhh....'
        ];

        try {
            foreach ($inputBindings as $input => $value) {
                $browser->waitFor($input)->type($input, $value);
            }
        } catch (TimeoutException $e) {
            logger()->error('Error while waiting for input : ' . $e->getMessage());
        }
    }
}
