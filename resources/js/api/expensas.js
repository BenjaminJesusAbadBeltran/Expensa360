import request from '@/utils/request';
import Resource from '@/api/resource';

class ExpensaResource extends Resource {
  constructor() {
    super('expensas');
  }
}

export function getProperties(propertyId = null) {
  const params = propertyId ? { idPropiedad: propertyId } : {};
  return request({
    url: '/properties', // Asegúrate de que esta URL es correcta
    method: 'get',
    params: params,
  });
}

export { ExpensaResource as default };
