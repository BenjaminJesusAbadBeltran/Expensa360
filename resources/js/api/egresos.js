import Resource from '@/api/resource';

class EgresoResource extends Resource {
  constructor() {
    super('egresos');
  }
}

export { EgresoResource as default };
