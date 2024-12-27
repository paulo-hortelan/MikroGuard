import type { Config } from '@/stores/config'

const generateString = (config: Config) => {

  return  `
[Interface]
#${config.peer_name}
ListenPort = 51820
PrivateKey = ${config.peer_private_key}
Address = ${config.address}

[Peer]
#${config.server_name}
PublicKey = ${config.server_public_key}
AllowedIPs = ${config.allowed_ips}
Endpoint = ${config.client_endpoint}
`.trim();

}

export { generateString }
