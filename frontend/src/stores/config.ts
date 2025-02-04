import { defineStore } from 'pinia'
import { http } from '@/utils'

export interface Config {
  peer_name: string
  peer_private_key: string
  peer_public_key: string
  peer_preshared_key: string|null
  peer_persistent_keepalive?: number
  server_name: string
  server_public_key: string
  endpoint: string
  dns: string
  allowed_ips: string
  address: string
  rx: number
  tx: number
  last_handshake: string|null
  last_connected_from: string
  client_endpoint: string|null
}


export const useConfigStore = defineStore({
  id: 'config',
  state: () => ({
    config: <Config|null>null,
  }),

  getters: {
  },

  actions: {
    async getConfig(userId: string): Promise<any> {
      try {
        const response = await http.get(`config/${userId}`);
        if (!response || !response.data || !response.data.data) {
          throw new Error('Invalid response structure');
        }
        this.config = response.data.data;
        return response;
      } catch (error) {
        console.error('Error fetching config:', error);
        throw error;
      }
    },

    async createConfig(userId: string): Promise<any> {
      try {
        const response = await http.post(`config/${userId}`);
        console.log('Response:', response); // Log the full response
        if (!response || !response.data || !response.data.data) {
          throw new Error('Invalid response structure');
        }
        this.config = response.data.data;
        return response;
      } catch (error) {
        console.error('Error creating config:', error);
        throw error;
      }
    },

    async deleteConfig(userId: string): Promise<any> {
      await http.delete(`config/${userId}`)

      this.resetConfig()
    },

    resetConfig (): void {
      this.config = null
    }
  }
})
