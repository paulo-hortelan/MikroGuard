import type { Config } from '@/stores/config'

const generateString = (config: Config) => {

  return  `
[Interface]
#${config.peer_name}
ListenPort = 23231
PrivateKey = ${config.peer_private_key}
Address = ${config.address}/32
DNS = ${config.dns}

[Peer]
#${config.server_name}
PublicKey = ${config.server_public_key}
AllowedIPs = ${config.allowed_ips}
Endpoint = ${config.client_endpoint}:23231
PersistentKeepalive = 5
`.trim();

}

export { generateString }
