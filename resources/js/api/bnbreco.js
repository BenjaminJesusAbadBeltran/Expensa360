import axios from 'axios';

class BalanceResource {
    constructor() {
        this.baseUrl = 'https://www.bnb.com.bo/PortalBNB/Api';
    }

    async obtenerToken() {
        try {
            const response = await axios.post(`${this.baseUrl}/ConsumirServicioQRToken`, {
                // Aquí puedes agregar los parámetros necesarios para obtener el token
            });
            if (response.data.Correcto) {
                return response.data.Mensaje; // Asumiendo que el token está en el campo "Mensaje"
            } else {
                throw new Error('Error al obtener el token');
            }
        } catch (error) {
            console.error('Error al obtener el token:', error);
            throw error;
        }
    }

    async obtenerBalance() {
        try {
            const token = await this.obtenerToken();
            const response = await axios.post(`${this.baseUrl}/ConsumirServicio`, {
                // Aquí puedes agregar los parámetros necesarios para consultar el balance
            }, {
                headers: {
                    'Authorization': `Bearer ${token}` // Usar el token en el encabezado de autorización
                }
            });
            if (response.data.Correcto) {
                const balanceData = JSON.parse(response.data.Mensaje);
                return balanceData.account;
            } else {
                throw new Error('Error al obtener el balance');
            }
        } catch (error) {
            console.error('Error al obtener el balance:', error);
            throw error;
        }
    }
}

export { BalanceResource as default };