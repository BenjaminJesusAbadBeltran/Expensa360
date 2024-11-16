<template>
  <div class="centered-container">
    <el-select v-model="form.selectedTable" placeholder="Select Table" style="margin-bottom: 20px;" @change="getColumns">
      <el-option v-for="table in tables_in_laravel" :key="table" :label="table" :value="table" />
    </el-select>
    <el-select v-model="form.selectedColumns" placeholder="Select Columns" multiple style="margin-bottom: 20px;">
      <el-option v-for="column in columns" :key="column" :label="column" :value="column" />
    </el-select>
    <el-button type="primary" @click="submitReport">Generate Report</el-button>
    <el-alert v-if="pdfGenerated" title="PDF Generated Successfully!" type="success" show-icon style="margin-top: 20px;" />
  </div>
</template>

<script>
import ReporteResource from '@/api/reportes';
import { jsPDF as JsPDF } from 'jspdf';
import 'jspdf-autotable';
const reporteResource = new ReporteResource();

export default {
  data() {
    return {
      tables_in_laravel: [],
      form: {
        selectedTable: '',
        selectedColumns: [],
      },
      columns: [],
      tableData: [],
      pdfGenerated: false,
    };
  },
  mounted() {
    this.getTables();
  },
  methods: {
    getTables() {
      reporteResource.fetchTables().then(response => {
        if (Array.isArray(response) && response.length > 0) {
          this.tables_in_laravel = response;
        } else {
          console.error('Error: El formato de response no es el esperado.');
        }
      }).catch(error => {
        console.error('Error al obtener las tablas:', error);
      });
    },
    getColumns() {
      if (this.form.selectedTable) {
        reporteResource.fetchColumns(this.form.selectedTable).then(response => {
          console.log('Columns Response:', response); // Log the entire response
          if (Array.isArray(response) && response.length > 0) {
            this.columns = response;
            console.log('Columns in Table:', this.columns);
          } else {
            console.error('Error: El formato de response no es el esperado.');
          }
        }).catch(error => {
          console.error('Error al obtener las columnas:', error);
        });
      }
    },
    submitReport() {
      if (this.form.selectedTable && this.form.selectedColumns.length > 0) {
        // Fetch the data for the selected table
        reporteResource.fetchTableData(this.form.selectedTable).then(response => {
          console.log('Table Data Response:', response); // Log the entire response
          if (Array.isArray(response) && response.length > 0) {
            this.tableData = response;
            console.log('Table Data:', this.tableData);

            // Generate the PDF
            const doc = new JsPDF();
            const columns = this.form.selectedColumns.map(col => col);
            const rows = this.tableData.map(row => {
              return this.form.selectedColumns.map(col => row[col]);
            });

            doc.text(`Report for Table: ${this.form.selectedTable}`, 14, 16);
            doc.autoTable({
              head: [columns],
              body: rows,
              startY: 20,
              theme: 'grid',
            });

            doc.save(`${this.form.selectedTable}_report.pdf`);
            this.pdfGenerated = true;
          } else {
            console.error('Error: El formato de response no es el esperado.');
          }
        }).catch(error => {
          console.error('Error al obtener los datos de la tabla:', error);
        });
      } else {
        console.error('Error: Debes seleccionar una tabla y al menos una columna.');
      }
    },
  },
};
</script>

<style>
.centered-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 20px;
}
</style>
