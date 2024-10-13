<template>
  <div class="app-container">
    <el-card class="box-card" style="margin-top: 15px;">
      <div class="content">
        <p>Seleccione los detalles para generar el reporte en formato PDF.</p>
        <el-form :model="form" ref="form" label-width="120px">
          <el-form-item label="Tabla">
            <el-select v-model="form.table" placeholder="Seleccione una tabla">
              <el-option label="Tabla 1" value="table1"></el-option>
              <el-option label="Tabla 2" value="table2"></el-option>
              <el-option label="Tabla 3" value="table3"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="Atributos">
            <el-select v-model="form.attributes" multiple placeholder="Seleccione atributos">
              <el-option label="Atributo 1" value="attribute1"></el-option>
              <el-option label="Atributo 2" value="attribute2"></el-option>
              <el-option label="Atributo 3" value="attribute3"></el-option>
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
export default {
  data() {
    return {
      form: {
        table: '',
        attributes: [],
        dateFrom: '',
        dateTo: ''
      }
    };
  },
  methods: {
    generateReport() {
      // Add logic to generate the report based on the selected details
      console.log('Generating report with the following details:', this.form);
      // For example, navigate to the download page with query params
      this.$router.push({ path: '/pdf/download', query: this.form });
    }
  }
};
</script>

<style scoped>
.app-container {
  padding: 20px;
  background-color: #f5f5f5;
  min-height: 100vh;
}

.box-card {
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.content {
  margin-bottom: 20px;
}
</style>