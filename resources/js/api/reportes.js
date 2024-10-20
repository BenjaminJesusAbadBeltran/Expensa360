import Resource from '@/api/resource';

class ReportesResource extends Resource {
  constructor() {
    super('reportes');
  }

  fetchTables() {
    return this.request({
      url: '/tablas',
      method: 'get'
    });
  }
}

export { ReportesResource as default };
