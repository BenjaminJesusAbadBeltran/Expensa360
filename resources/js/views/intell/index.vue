<template>
  <div>
    <h2>Pronóstico de Ingresos</h2>
    <table>
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Ingreso Estimado</th>
          <th>Rango Inferior</th>
          <th>Rango Superior</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="ingreso in pronostico" :key="ingreso.ds">
          <td>{{ ingreso.ds }}</td>
          <td>{{ ingreso.yhat }}</td>
          <td>{{ ingreso.yhat_lower }}</td>
          <td>{{ ingreso.yhat_upper }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      pronostico: [],
    };
  },
  mounted() {
    axios.get('/api/pronostico-ingresos')
      .then(response => {
        this.pronostico = response.data;
      })
      .catch(error => {
        console.error('Hubo un error al obtener el pronóstico:', error);
      });
  },
};
</script>
