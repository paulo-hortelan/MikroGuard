<?php

namespace App\RouterOS;

use App\RouterOS\Data\Peer;
use App\RouterOS\Data\WireGuardInterface;
use RouterOS\Query;

class WireGuard extends RouterOS
{
    public static function getWireGuardInterface(): WireGuardInterface
    {
        $routerOS = new self;

        $response = $routerOS->client->query('/interface/wireguard/print', ['name', config('services.wireguard.interface')])->read();

        if ($response) {
            return new WireGuardInterface($response[0]['public-key']);
        }

        throw new \Exception('Could not find WireGuard interface');
    }

    public static function getPeer(string $publicKey): ?array
    {
        $routerOS = new self;

        $query = new Query('/interface/wireguard/peers/print');
        $query->where('public-key', $publicKey)
            ->where('interface', config('services.wireguard.interface'));

        $response = $routerOS->client->query($query)->read();

        if (! count($response)) {
            return null;
        }

        return $response[0];
    }

    public static function getPeers(): array
    {
        $routerOS = new self;

        $query = new Query('/interface/wireguard/peers/print');
        $query->where('interface', config('services.wireguard.interface'));

        $response = $routerOS->client->query($query)->read();

        if (! count($response)) {
            return [];
        }

        $peers = [];

        foreach ($response as $peer) {
            $peers[$peer['public-key']] = $peer;
        }

        return $peers;
    }

    public static function createPeer(Peer $peer): void
    {
        try {
            $routerOS = new self;
            $resource = Resource::getRouterResource();
            $useName = version_compare($resource->version, '7.15') >= 0;

            $query = new Query('/interface/wireguard/peers/add');
            $query->equal('allowed-address', $peer->allowedAddress)
                ->equal('interface', $peer->interface)
                ->equal('public-key', $peer->publicKey)
                ->equal('client-address', $peer->allowedAddress)
                ->equal($useName ? 'name' : 'comment', $peer->name);

            if (! empty($peer->clientEndpoint)) {
                $query = $query->equal('client-endpoint', $peer->clientEndpoint);
            }

            if (! empty($peer->presharedKey)) {
                $query = $query->equal('preshared-key', $peer->presharedKey);
            }

            if (! empty($peer->privateKey)) {
                $query = $query->equal('private-key', $peer->privateKey);
            }

            if (config('services.wireguard.persistent_keepalive')) {
                $query->equal('persistent-keepalive', config('services.wireguard.persistent_keepalive'));
            }

            $query = $query->equal('is-responder', 'yes');
            $query = $query->equal('endpoint-port', 23231);
            $query = $query->equal('client-dns', '192.168.100.254');
            $query = $query->equal('client-keepalive', 5);
            $query = $query->equal('client-listen-port', 23231);

            \Illuminate\Support\Facades\Log::info('Attempting to create WireGuard peer', [
                'peer_name' => $peer->name,
                'public_key' => $peer->publicKey,
                'allowed_address' => $peer->allowedAddress,
                'interface' => $peer->interface,
                'query' => $query->getQuery(),
            ]);

            $response = $routerOS->client->query($query)->read();

            \Illuminate\Support\Facades\Log::info('WireGuard peer created successfully', [
                'peer_name' => $peer->name,
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create WireGuard peer', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'peer_name' => $peer->name,
                'public_key' => $peer->publicKey,
                'allowed_address' => $peer->allowedAddress,
            ]);

            throw $e;
        }
    }

    public static function deletePeer(string $publicKey): void
    {
        $routerOS = new self;

        $peer = self::getPeer($publicKey);

        if ($peer) {
            $query = new Query('/interface/wireguard/peers/remove');
            $query->equal('.id', $peer['.id']);

            $routerOS->client->query($query)->read();
        }
    }
}
