<template>
  <div class="app-container">
    <el-card class="box-card" style="margin-top: 15px;">
      <div class="content">
        <p>Seleccione los detalles para generar el reporte en formato PDF.</p>
        <el-form :model="form" ref="form" label-width="120px">
          <el-form-item label="Tabla">
            <el-select v-model="form.table" placeholder="Seleccione una tabla" @change="onTableChange">
              <el-option v-for="table in tables" :key="table" :label="table" :value="table"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="Atributos">
            <el-select v-model="form.attributes" multiple placeholder="Seleccione atributos">
              <el-option v-for="attribute in attributes" :key="attribute" :label="attribute" :value="attribute"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="Fecha Desde">
            <el-date-picker v-model="form.dateFrom" type="date" placeholder="Seleccione fecha de inicio"></el-date-picker>
          </el-form-item>
          <el-form-item label="Fecha Hasta">
            <el-date-picker v-model="form.dateTo" type="date" placeholder="Seleccione fecha de fin"></el-date-picker>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" icon="el-icon-download" @click="generateReport">
              Generar Reporte PDF
            </el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
  </div>
</template>

<script>
import ReporteResource from '@/api/reportes';

export default {
  data() {
    return {
      form: {
        table: '',
        attributes: [],
        dateFrom: '',
        dateTo: ''
      },
      tables: [],
      attributes: []
    };
  },
  created() {
    this.fetchTables();
  },
  methods: {
    fetchTables() {
      const reportesResource = new ReporteResource();
      reportesResource.fetchTables()
        .then(response => {
          this.tables = response.data;
        })
        .catch(error => {
          console.error('Error fetching tables:', error);
        });
    },
    onTableChange(table) {
      // Aquí deberías cargar los atributos de la tabla seleccionada desde tu API o algún otro método
      // Por simplicidad, vamos a usar datos estáticos
      this.attributes = ['atributo1', 'atributo2', 'atributo3'];
    },
    generateReport() {
      this.$router.push({ name: 'Download', query: this.form });
    }
  }
};
</script>