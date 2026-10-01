<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustCloudflareConnectingIp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrustCloudflareConnectingIpTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_header_is_ignored_while_the_flag_is_off(): void
    {
        config(['app.trust_cf_connecting_ip' => false]);

        $this->assertSame('10.0.1.8', $this->addressAfter([
            'REMOTE_ADDR' => '10.0.1.8',
            'CF-Connecting-IP' => '203.0.113.8',
            'X-Forwarded-For' => '198.51.100.4',
        ], '198.51.100.4'));
    }

    public function test_a_private_proxy_is_replaced_by_the_cloudflare_client_ip(): void
    {
        config(['app.trust_cf_connecting_ip' => true]);

        foreach (['10.0.6.5', '172.16.0.2', '172.31.255.9', '192.168.1.20'] as $proxy) {
            $this->assertSame('203.0.113.8', $this->addressAfter([
                'REMOTE_ADDR' => $proxy,
                'CF-Connecting-IP' => '203.0.113.8',
                'X-Forwarded-For' => '198.51.100.4',
            ], null));
        }
    }

    public function test_a_public_remote_address_cannot_supply_the_header(): void
    {
        config(['app.trust_cf_connecting_ip' => true]);

        $this->assertSame('8.8.8.8', $this->addressAfter([
            'REMOTE_ADDR' => '8.8.8.8',
            'CF-Connecting-IP' => '203.0.113.8',
            'X-Forwarded-For' => '198.51.100.4',
        ], '198.51.100.4'));

        $this->assertSame('172.15.0.1', $this->addressAfter([
            'REMOTE_ADDR' => '172.15.0.1',
            'CF-Connecting-IP' => '203.0.113.8',
        ], null));
    }

    public function test_a_malformed_connecting_ip_is_ignored(): void
    {
        config(['app.trust_cf_connecting_ip' => true]);

        $this->assertSame('10.0.0.4', $this->addressAfter([
            'REMOTE_ADDR' => '10.0.0.4',
            'CF-Connecting-IP' => '203.0.113.8, 198.51.100.1',
        ], null));
    }

    /**
     * @param  array<string, string>  $server
     */
    private function addressAfter(array $server, ?string $expectedForwardedFor): string
    {
        $request = Request::create('/login', 'GET', server: [
            'REMOTE_ADDR' => $server['REMOTE_ADDR'],
        ]);
        $request->headers->set('CF-Connecting-IP', $server['CF-Connecting-IP']);

        if (array_key_exists('X-Forwarded-For', $server)) {
            $request->headers->set('X-Forwarded-For', $server['X-Forwarded-For']);
        }

        $seen = '';

        (new TrustCloudflareConnectingIp)->handle($request, function (Request $request) use (&$seen, $expectedForwardedFor) {
            $seen = (string) $request->server->get('REMOTE_ADDR');
            $this->assertSame($expectedForwardedFor, $request->headers->get('X-Forwarded-For'));

            return response('ok');
        });

        return $seen;
    }
}
