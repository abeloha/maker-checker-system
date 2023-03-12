<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ChangeRequest;
use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RequestTest extends TestCase
{
    public function testMakeCreateRequest()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            '/v1/api/requests',
            [
                'type' => 'create',
                'data' => json_encode([
                    "first_name" => "Abel",
                    "last_name" => "Abel",
                    "email" => "abel@example.com"
                ])
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => true,
            ])
            ->assertJsonPath('data.type', 'create');
            ;
    }

    public function testMakeUpdateRequest()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            '/v1/api/requests',
            [
                'type' => 'update',
                'data' => json_encode([
                    "first_name" => "Abel",
                    "last_name" => "Abel",
                    "email" => "abel@example.com"
                ]),
                'user_id' => $user->id,
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => true,
            ])
            ->assertJsonPath('data.type', 'update')
            ->assertJsonPath('data.user.id', $user->id)
            ;
    }

    public function testMakeDeleteRequest()
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin)->postJson(
            '/v1/api/requests',
            [
                'type' => 'delete',
                'user_id' => $user->id,
            ]
        );

        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => true,
            ])
            ->assertJsonPath('data.type', 'delete')
            ->assertJsonPath('data.user.id', $user->id)
            ;
    }

    public function testViewRequests()
    {
        $admin1 = Admin::factory()->create();
        $admin2 = Admin::factory()->create();

        $request = ChangeRequest::create(
            [
                'type' => 'create',
                'data' => json_encode([
                    "first_name" => "Abel",
                    "last_name" => "Abel",
                    "email" => "abel@example.com"
                ]),
                'admin_id' => $admin1->id,
            ]
        );

        $response = $this->actingAs($admin2)->get(
            '/v1/api/requests');

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => true,
            ]);
            $response->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'data',
                        'user',
                        'requested_by'
                    ]
                ],
            ]);
    }


    public function testApproveCreateRequest()
    {
        $admin1 = Admin::factory()->create();
        $admin2 = Admin::factory()->create();

        $email = uniqid().'@mail.com';

        $request = ChangeRequest::create(
            [
                'type' => 'create',
                'data' => json_encode([
                    "first_name" => "Abel",
                    "last_name" => "Onuoha",
                    "email" => $email,
                ]),
                'admin_id' => $admin1->id,
            ]
        );

        $response = $this->actingAs($admin2)->post(
            "/v1/api/requests/$request->id/approve");


        $response->assertStatus(200);
        $this->assertDatabaseMissing('change_requests', ['id' => $request->id]);
        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);


    }

    public function testApproveUpdateRequest()
    {
        $admin1 = Admin::factory()->create();
        $admin2 = Admin::factory()->create();

        $user = User::factory()->create();

        $email = uniqid().'@mail.com';

        $request = ChangeRequest::create(
            [
                'type' => 'update',
                'data' => json_encode([
                    "first_name" => "Abel",
                    "last_name" => "Onuoha",
                    "email" => $email,
                ]),
                'admin_id' => $admin1->id,
                'user_id' => $user->id,
            ]
        );

        $response = $this->actingAs($admin2)->post(
            "/v1/api/requests/$request->id/approve");


        $response->assertStatus(200);
        $this->assertDatabaseMissing('change_requests', ['id' => $request->id]);
        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);


    }

    public function testApproveDeleteRequest()
    {
        $admin1 = Admin::factory()->create();
        $admin2 = Admin::factory()->create();

        $user = User::factory()->create();


        $request = ChangeRequest::create(
            [
                'type' => 'delete',
                'admin_id' => $admin1->id,
                'user_id' => $user->id,
            ]
        );

        $response = $this->actingAs($admin2)->post(
            "/v1/api/requests/$request->id/approve");


        $response->assertStatus(200);
        $this->assertDatabaseMissing('change_requests', ['id' => $request->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

    }

    public function testCanNotApproveMyOwnRequest()
    {
        $admin1 = Admin::factory()->create();

        $email = uniqid().'@mail.com';

        $request = ChangeRequest::create(
            [
                'type' => 'create',
                'data' => json_encode([
                    "first_name" => "Abel",
                    "last_name" => "Onuoha",
                    "email" => $email,
                ]),
                'admin_id' => $admin1->id,
            ]
        );

        $response = $this->actingAs($admin1)->post(
            "/v1/api/requests/$request->id/approve");


        $response->assertStatus(403);


    }

    public function testDeclineRequest()
    {
        $admin1 = Admin::factory()->create();
        $admin2 = Admin::factory()->create();

        $user = User::factory()->create();


        $request = ChangeRequest::create(
            [
                'type' => 'delete',
                'admin_id' => $admin1->id,
                'user_id' => $user->id,
            ]
        );

        $response = $this->actingAs($admin2)->post(
            "/v1/api/requests/$request->id/decline");


        $response->assertStatus(200);
        $this->assertDatabaseMissing('change_requests', ['id' => $request->id]);
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
        ]);

    }

    public function testCanNotDeclineMyOwnRequest()
    {

        $admin1 = Admin::factory()->create();

        $email = uniqid().'@mail.com';

        $request = ChangeRequest::create(
            [
                'type' => 'create',
                'data' => json_encode([
                    "first_name" => "Abel",
                    "last_name" => "Onuoha",
                    "email" => $email,
                ]),
                'admin_id' => $admin1->id,
            ]
        );

        $response = $this->actingAs($admin1)->post(
            "/v1/api/requests/$request->id/decline");


        $response->assertStatus(403);

    }

}
