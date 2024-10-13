const title = 'Plantilla de PDF con Vue.js';

const content = `<table class="payment-report">
  <thead>
    <tr>
      <th colspan="2">Reporte de Pagos de Expensas</th>
    </tr>
  </thead>
  <tbody>
    <!-- Información General del Pago -->
    <tr>
      <td colspan="2" class="section-header">Información General</td>
    </tr>
    <tr>
      <td>ID de Pago</td>
      <td>{{ $this->id }}</td>
    </tr>
    <tr>
      <td>Monto</td>
      <td>{{ $this->monto }} USD</td>
    </tr>
    <tr>
      <td>Fecha de Pago</td>
      <td>{{ $this->fechaPago }}</td>
    </tr>

    <!-- Información del Método de Pago -->
    <tr>
      <td colspan="2" class="section-header">Detalles del Método de Pago</td>
    </tr>
    <tr>
      <td>ID del Método de Pago</td>
      <td>{{ $this->idMetodoPago }}</td>
    </tr>
    <tr>
      <td>Método de Pago</td>
      <td>{{ new MetodoPagoResource($this->whenLoaded('metodoPago')) }}</td>
    </tr>

    <!-- Información de la Caja Chica -->
    <tr>
      <td colspan="2" class="section-header">Detalles de Caja Chica</td>
    </tr>
    <tr>
      <td>ID de Caja Chica</td>
      <td>{{ $this->idCajaChica }}</td>
    </tr>
    <tr>
      <td>Caja Chica Asociada</td>
      <td>{{ new CajaChicaResource($this->whenLoaded('cajaChica')) }}</td>
    </tr>

    <!-- Información de los Usuarios -->
    <tr>
      <td colspan="2" class="section-header">Usuarios Relacionados</td>
    </tr>
    <tr>
      <td>Usuarios Involucrados</td>
      <td>{{ UserResource::collection($this->whenLoaded('usuarios')) }}</td>
    </tr>

    <!-- Totales y Resumen -->
    <tr>
      <td colspan="2" class="section-header">Resumen del Pago</td>
    </tr>
    <tr>
      <td>Total Pagado</td>
      <td>{{ $this->monto }} USD</td>
    </tr>
    <tr>
      <td>Estado del Pago</td>
      <td>{{ $this->idStatus ? 'Completado' : 'Pendiente' }}</td>
    </tr>

    <!-- Timestamp e Información Extra -->
    <tr>
      <td colspan="2" class="section-header">Metadatos</td>
    </tr>
    <tr>
      <td>Fecha de Creación</td>
      <td>{{ $this->created_at }}</td>
    </tr>
    <tr>
      <td>Última Actualización</td>
      <td>{{ $this->updated_at }}</td>
    </tr>
  </tbody>
</table>`;

const data = {
  title,
  content,
};

export default data;
