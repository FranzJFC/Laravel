<?php

namespace Tests\Feature;

use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Notifications\MembershipApplicationSubmitted;
use Database\Seeders\MembershipCategorySeeder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MembershipApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected MembershipLevel $level;

    private function signupData(int $categoryId): array
    {
        return [
            'membership_category_id' => $categoryId, 'membership_level_id' => $this->level->id,
            'full_name' => 'Ana Cruz', 'email' => 'ana@example.test',
            'password' => 'MemberPass123', 'password_confirmation' => 'MemberPass123',
            'latitude' => 14.5995, 'longitude' => 120.9842,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->level = MembershipLevel::create(['name' => 'Basic', 'slug' => 'basic', 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);
    }

    public function test_application_is_stored_and_email_notification_is_sent(): void
    {
        Notification::fake();
        $this->seed(MembershipCategorySeeder::class);
        $category = MembershipCategory::where('name', 'Regular')->firstOrFail();

        $response = $this->post(route('register.store'), $this->signupData($category->id));

        $response->assertRedirect(route('member.dashboard'))->assertSessionHas('success');
        $this->assertDatabaseHas('membership_applications', [
            'email' => 'ana@example.test', 'status' => 'pending',
            'membership_category_id' => $category->id,
            'latitude' => 14.5995, 'longitude' => 120.9842,
        ]);
        $application = MembershipApplication::firstOrFail();
        $this->assertMatchesRegularExpression('/^MEM-[A-Z0-9]{10}$/', $application->reference_no);
        $this->assertNotNull($application->verification_sent_at);
        Notification::assertSentOnDemand(MembershipApplicationSubmitted::class,
            function ($notification, $channels, $notifiable) use ($application) {
                return $notifiable->routes['mail'] === 'ana@example.test'
                    && $channels === ['mail']
                    && $notification instanceof ShouldQueue
                    && $notification->afterCommit === true
                    && $notification->application->is($application);
            });
    }

    public function test_form_displays_only_active_categories_and_leaflet(): void
    {
        $this->seed(MembershipCategorySeeder::class);
        MembershipCategory::create(['name' => 'Inactive category', 'slug' => 'inactive', 'is_active' => false]);
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('membership.create'))->assertRedirect(route('register'));
        $this->get(route('register'))->assertOk()
            ->assertSee('Regular')->assertSee('500.00')->assertSee('Student')->assertSee('Basic')
            ->assertSee('leaflet@1.9.4')->assertSee('OpenStreetMap')->assertDontSee('Inactive category');
    }

    public function test_invalid_application_is_not_stored_or_notified(): void
    {
        Notification::fake();
        $this->postJson(route('membership.store'), [
            'membership_category_id' => 999, 'full_name' => str_repeat('a', 151),
            'email' => 'invalid', 'phone' => str_repeat('1', 31),
            'address' => str_repeat('a', 256), 'latitude' => 91, 'longitude' => -181,
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'membership_category_id', 'full_name', 'email', 'phone', 'address', 'latitude', 'longitude',
        ]);
        $this->assertDatabaseCount('membership_applications', 0);
        Notification::assertNothingSent();
    }

    public function test_notification_job_is_deferred_until_commit_and_discarded_on_rollback(): void
    {
        config(['queue.default' => 'database']);
        $this->seed(MembershipCategorySeeder::class);
        $category = MembershipCategory::firstOrFail();
        $jobsBefore = DB::table('jobs')->count();
        $level = DB::transactionLevel();
        DB::beginTransaction();
        try {
            $this->post(route('register.store'), $this->signupData($category->id))
                ->assertRedirect(route('member.dashboard'));
            $this->assertDatabaseCount('membership_applications', 1);
            $this->assertSame($jobsBefore, DB::table('jobs')->count());
        } finally {
            DB::rollBack($level);
        }
        $this->assertDatabaseCount('membership_applications', 0);
        $this->assertSame($jobsBefore, DB::table('jobs')->count());
    }

    public function test_weather_endpoint_returns_open_meteo_response_and_caches_rounded_coordinates(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::response(['current' => [
            'temperature_2m' => 30.5, 'relative_humidity_2m' => 75, 'weather_code' => 2,
        ]])]);
        $expected = ['place' => 'Selected location', 'temperature' => 30.5, 'description' => 'Partly cloudy', 'humidity' => 75];
        $this->getJson('/weather?lat=14.5995&lon=120.9842')->assertOk()->assertExactJson($expected);
        $this->getJson('/weather?lat=14.5996&lon=120.9843')->assertOk()->assertExactJson($expected);
        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://api.open-meteo.com/v1/forecast?')
            && $request['latitude'] == 14.5995 && $request['longitude'] == 120.9842
            && $request['current'] === 'temperature_2m,relative_humidity_2m,weather_code'
            && $request['timezone'] === 'auto');
        $this->travel(11)->minutes();
        $this->getJson('/weather?lat=14.5995&lon=120.9842')->assertOk();
        Http::assertSentCount(2);
    }

    public function test_invalid_weather_coordinates_are_rejected(): void
    {
        Http::fake();
        foreach ([['91', '181'], ['-91', '-181'], ['abc', 'nope']] as [$lat, $lon]) {
            $this->getJson("/weather?lat={$lat}&lon={$lon}")
                ->assertUnprocessable()->assertJsonValidationErrors(['lat', 'lon']);
        }
        $this->getJson('/weather')->assertUnprocessable()->assertJsonValidationErrors(['lat', 'lon']);
        Http::assertNothingSent();
    }

    public function test_weather_failures_are_graceful_and_not_cached(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::sequence()
            ->push([], 500)->push(['current' => []])
            ->push(['current' => ['temperature_2m' => 29, 'relative_humidity_2m' => 80, 'weather_code' => 0]])]);
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->getJson('/weather?lat=14.5995&lon=120.9842')->assertStatus(503)
                ->assertExactJson(['error' => 'Weather is currently unavailable.']);
        }
        $this->getJson('/weather?lat=14.5995&lon=120.9842')->assertOk()->assertJsonPath('description', 'Clear sky');
        Http::assertSentCount(3);
    }

    public function test_weather_connection_failure_is_graceful(): void
    {
        Http::fake(['api.open-meteo.com/*' => Http::failedConnection()]);
        $this->getJson('/weather?lat=14.5995&lon=120.9842')->assertStatus(503)
            ->assertJsonPath('error', 'Weather is currently unavailable.');
    }

    public function test_wmo_codes_have_readable_descriptions_and_unknown_fallback(): void
    {
        $descriptions = [0 => 'Clear sky', 1 => 'Mainly clear', 2 => 'Partly cloudy', 3 => 'Overcast',
            45 => 'Fog', 48 => 'Fog', 51 => 'Drizzle', 57 => 'Drizzle', 61 => 'Rain', 67 => 'Rain',
            71 => 'Snow', 77 => 'Snow', 80 => 'Rain showers', 82 => 'Rain showers',
            85 => 'Snow showers', 86 => 'Snow showers', 95 => 'Thunderstorm', 99 => 'Thunderstorm', 100 => 'Unknown',
        ];
        $sequence = Http::sequence();
        foreach ($descriptions as $code => $description) {
            $sequence->push(['current' => [
                'temperature_2m' => 25, 'relative_humidity_2m' => 70, 'weather_code' => $code,
            ]]);
        }
        Http::fake(['api.open-meteo.com/*' => $sequence]);
        foreach ($descriptions as $description) {
            Cache::flush();
            $this->getJson('/weather?lat=14.5995&lon=120.9842')->assertOk()->assertJsonPath('description', $description);
        }
    }

    public function test_public_endpoints_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson(route('membership.store'), [])->assertUnprocessable();
        }
        $this->postJson(route('membership.store'), [])->assertStatus(429);
        for ($attempt = 0; $attempt < 60; $attempt++) {
            $this->getJson('/weather')->assertUnprocessable();
        }
        $this->getJson('/weather')->assertStatus(429);
    }
}
