<?php

namespace App\Listeners;

use App\Events\ConfigCreated;
use App\RouterOS\Data\Peer;
use App\RouterOS\WireGuard;

class CreateWireGuardPeer
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ConfigCreated $event): void
    {
        $peer = new Peer(
            $event->config->peer_name,
            $event->config->address,
            $event->config->peer_public_key,
            $event->config->peer_private_key,
            config('services.wireguard.interface'),
            config('services.wireguard.preshared_key_enabled') ? $event->config->peer_preshared_key : null,
            config('services.wireguard.client_endpoint'),
        );

        WireGuard::createPeer($peer);
    }
}
