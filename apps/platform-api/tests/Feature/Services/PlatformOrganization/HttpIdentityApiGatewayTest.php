<?php

declare(strict_types=1);

namespace Tests\Feature\Services\PlatformOrganization;

use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiAccessDenied;
use App\Services\PlatformOrganization\Application\Exceptions\IdentityApiUnavailable;
use App\Services\PlatformOrganization\Infrastructure\Services\HttpIdentityApiGateway;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class HttpIdentityApiGatewayTest extends TestCase
{
    private HttpIdentityApiGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.platform_onboarding.identity_api_url' => 'http://identity.test']);
        config(['services.platform_onboarding.identity_client_id' => 'service-client-id']);
        config(['services.platform_onboarding.identity_client_secret' => 'service-client-secret']);
        $this->gateway = new HttpIdentityApiGateway();
        Http::preventStrayRequests();
    }

    public function test_current_identity_uses_the_callers_bearer_and_returns_the_login_project_context(): void
    {
        Http::fake(['identity.test/api/v1/identity/auth/me' => Http::response([
            'data' => [
                'user' => ['id' => 'subject-1', 'email' => 'Member@Example.com'],
                'project' => ['slug' => 'talred-platform'],
            ],
        ])]);

        $identity = $this->gateway->currentIdentity('user-access-token');

        $this->assertSame([
            'user_id' => 'subject-1',
            'email' => 'member@example.com',
            'project_slug' => 'talred-platform',
        ], $identity);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'http://identity.test/api/v1/identity/auth/me'
            && $request->hasHeader('Authorization', 'Bearer user-access-token'));
    }

    public function test_project_list_distinguishes_missing_access_from_upstream_failure(): void
    {
        Http::fakeSequence('identity.test/api/v1/identity/projects')
            ->push([], 403)
            ->push([], 503);
        try {
            $this->gateway->listProjects('user-access-token');
            self::fail('Expected the Identity access denial to be surfaced.');
        } catch (IdentityApiAccessDenied) {
            self::assertTrue(true);
        }

        $this->expectException(IdentityApiUnavailable::class);
        $this->gateway->listProjects('user-access-token');
    }

    public function test_project_detail_read_uses_generic_access_needed_and_preserves_unavailable_state(): void
    {
        Http::fakeSequence('identity.test/api/v1/identity/projects/project-1')
            ->push([], 404)
            ->push([], 503);
        $this->assertSame(['status' => 'access_needed'], $this->gateway->projectDetails('user-access-token', 'project-1'));

        $this->assertSame(['status' => 'unavailable'], $this->gateway->projectDetails('user-access-token', 'project-1'));
    }
}
