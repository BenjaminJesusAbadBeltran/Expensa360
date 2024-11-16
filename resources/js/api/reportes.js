import request from '@/utils/request';
import Resource from '@/api/resource';

class ReporteResource extends Resource {
  constructor() {
    super('reportes');
  }

  fetchTables() {
    return request({
      url: '/report/tables',
      method: 'get',
    });
  }
  // Obtener columnas de una tabla seleccionada
  fetchColumns(table) {
    return request({
      url: `/report/tables/${table}/columns`,
      method: 'get',
    });
  }

  fetchTableData(table) {
    return request({
      url: `/report/tables/${table}/data`,
      method: 'get',
    });
  }
  // Enviar los datos seleccionados para generar el reporte
  generateReport(data) {
    return request({
      url: '/report/generate',
      method: 'post',
      data,
      responseType: 'blob', // Para manejar la descarga del PDF
    });
  }
}

export default ReporteResource;
