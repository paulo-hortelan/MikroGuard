import type { Config } from '@/stores/config'

const generateString = (config: Config) => {

  return  `
[Interface]
#${config.peer_name}
ListenPort = 51820
PrivateKey = ${config.peer_private_key}
Address = ${config.address}
DNS = ${config.dns}

[Peer]
#${config.server_name}
PublicKey = ${config.server_public_key}
AllowedIPs = ${config.allowed_ips}
Endpoint = ${config.client_endpoint}:13231
`.trim();

}

export { generateString }
