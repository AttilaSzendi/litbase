<?php

namespace Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_list_of_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson(route('user.index'));

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
