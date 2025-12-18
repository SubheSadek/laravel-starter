<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can create company.
     */
    public function test_user_can_create_company(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('api/company/create-company', [
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'phone' => '+8801717171717',
            'website' => 'https://test.com',
            'address' => 'Test Address',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('json_data', fn ($value) => ! empty($value));
    }

    /**
     * Test user can create company with valid data.
     */
    #[DataProvider('validCompanyDataProvider')]
    public function test_can_create_company_with_valid_data(array $data): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('api/company/create-company', $data);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('json_data', fn ($value) => ! empty($value));
    }

    /**
     * Test user can create company with valid data.
     */
    public static function validCompanyDataProvider(): array
    {
        return [
            'only required name' => [
                ['name' => 'Company A'],
            ],
            'with valid email' => [
                ['name' => 'Company B', 'email' => 'test@example.com'],
            ],
            'with null optional fields' => [
                ['name' => 'Company C', 'email' => null, 'phone' => null],
            ],
            'with valid website https' => [
                ['name' => 'Company D', 'website' => 'https://example.com'],
            ],
            'with valid website http' => [
                ['name' => 'Company E', 'website' => 'http://example.com'],
            ],
            'with subdomain website' => [
                ['name' => 'Company F', 'website' => 'https://sub.example.com'],
            ],
            'with long valid address' => [
                ['name' => 'Company G', 'address' => str_repeat('a', 500)],
                'max length address',
            ],
            'with phone number' => [
                ['name' => 'Company H', 'phone' => '+1234567890'],
                'valid phone',
            ],
        ];
    }

    /**
     * Test user can create company with invalid data.
     */
    #[DataProvider('invalidNameDataProvider')]
    public function test_name_validation_fails(array $data): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/company/create-company', $data);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('messages', fn ($value) => ! empty($value));
    }

    /**
     * Test user can create company with invalid data.
     */
    public static function invalidNameDataProvider(): array
    {
        return [
            'missing name' => [
                ['email' => 'test@example.com'],
            ],
            'empty name' => [
                ['name' => ''],
            ],
            'name too long' => [
                ['name' => str_repeat('a', 256)],
            ],
        ];
    }

    /**
     * Test user can create company with invalid data.
     */
    public function test_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        Company::create(['name' => 'Unique Company']);

        $response = $this->actingAs($user)->postJson('/api/company/create-company', [
            'name' => 'Unique Company',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('messages', fn ($value) => ! empty($value));
    }

    /**
     * Test user can update company with all fields.
     */
    public function test_can_update_company_with_all_fields(): void
    {
        $user = User::factory()->create();

        $company = Company::create([
            'name' => 'Old Company Name',
            'email' => 'old@example.com',
            'phone' => '1234567890',
            'website' => 'https://old.com',
            'address' => 'Old Address',
        ]);

        $updateData = [
            'name' => 'New Company Name',
            'email' => 'new@example.com',
            'phone' => '+9876543210',
            'website' => 'https://new.com',
            'address' => 'New Address, New City',
        ];

        $response = $this->actingAs($user)->putJson("/api/company/update-company/{$company->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'json_data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'website',
                    'address',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Company updated successfully!',
                'json_data' => [
                    'name' => 'New Company Name',
                    'email' => 'new@example.com',
                ],
            ]);

        $this->assertDatabaseHas('companies', array_merge(['id' => $company->id], $updateData));
    }

    /**
     * Test user can update company with all fields.
     */
    public function test_returns_error_when_company_not_found(): void
    {
        $user = User::factory()->create();
        $nonExistentId = 99999;

        $response = $this->actingAs($user)->putJson("/api/company/update-company/{$nonExistentId}", [
            'name' => 'Test Company',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test user cannot update company name to existing company name.
     */
    public function test_cannot_update_name_to_existing_company_name(): void
    {
        $user = User::factory()->create();
        $company1 = Company::create(['name' => 'Company One']);
        $company2 = Company::create(['name' => 'Company Two']);

        $response = $this->actingAs($user)->putJson("/api/company/update-company/{$company2->id}", [
            'name' => 'Company One', // Try to use company1's name
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        // company2 name should remain unchanged
        $this->assertDatabaseHas('companies', [
            'id' => $company2->id,
            'name' => 'Company Two',
        ]);
    }

    /**
     * Test user can update company with valid data.
     */
    #[DataProvider('validCompanyDataProvider')]
    public function test_can_update_company_with_valid_data(array $updateData): void
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'Company One']);

        $response = $this->actingAs($user)->putJson("/api/company/update-company/{$company->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('json_data', fn ($value) => ! empty($value));

        $this->assertDatabaseHas('companies', array_merge(['id' => $company->id], $updateData));
    }

    /**
     * Test user cannot update company with invalid data.
     */
    #[DataProvider('invalidNameDataProvider')]
    public function test_update_validation_fails(array $updateData): void
    {
        $user = User::factory()->create();
        $company = Company::create(['name' => 'Original Company']);

        $response = $this->actingAs($user)->putJson("/api/company/update-company/{$company->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('messages', fn ($value) => ! empty($value));

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Original Company',
        ]);
    }

    /**
     * Test user can delete company.
     */
    public function test_can_delete_existing_company(): void
    {
        $user = User::factory()->create();
        $company = Company::create([
            'name' => 'Company To Delete',
            'email' => 'delete@example.com',
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/company/delete-company/{$company->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('companies', [
            'id' => $company->id,
        ]);
    }

    /**
     * Test user cannot delete non-existent company.
     */
    public function test_returns_error_when_deleting_non_existent_company(): void
    {
        $user = User::factory()->create();
        $nonExistentId = 99999;

        $response = $this->actingAs($user)->deleteJson("/api/company/delete-company/{$nonExistentId}");

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }
}
