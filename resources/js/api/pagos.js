import Resource from '@/api/resource';

class PagoResource extends Resource {
  constructor() {
    super('pagos');
  }
}

export { PagoResource as default };
