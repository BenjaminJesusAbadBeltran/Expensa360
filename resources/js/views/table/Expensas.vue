<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" style="width: 400px;" placeholder="Search by ID" class="filter-item"
        @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus"
        @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
      <el-checkbox v-model="filterStatus" @change="filterByStatus" class="filter-item" style="margin-left: 10px;">
        Expensas Eliminadas
      </el-checkbox>
    </div>

    <template>
      <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
        <el-table-column prop="idExpensa" label="ID" width="80" />
        <el-table-column prop="nombrePropiedad" label="Nombre de Propiedad" />
        <el-table-column prop="monto" label="Monto" />
        <el-table-column label="Mes">
        <template slot-scope="scope">
          {{ getMonthInLetters(scope.row.mes) }}
        </template>
      </el-table-column>
        <el-table-column label="Acciones" width="280">
          <template slot-scope="scope">
          <el-button v-show="scope.row.status !== 'Borrado'" size="mini" type="warning" @click="handleUpdate(scope.row.idExpensa)">Editar</el-button>
          <el-button v-show="scope.row.status == 'Borrado'" size="mini" type="success"
            @click="handleRestore(scope.row)">Restore
          </el-button>
          <el-button v-show="scope.row.status !== 'Borrado'" size="mini" type="danger"
            @click="handleDelete(scope.row.idExpensa, scope.row.monto)">Eliminar</el-button>
        </template>
        </el-table-column>
      </el-table>
    </template>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getList" />

    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form :model="newExpensa" ref="expensaForm" :rules="rules">
          <el-form-item label="Propiedad" :label-width="formLabelWidth" prop="idPropiedad">
            <el-select v-model="newExpensa.idPropiedad" placeholder="Seleccione una propiedad">
              <el-option v-for="property in properties" :key="property.idPropiedad" :label="property.nombre"
                :value="property.idPropiedad" />
            </el-select>
          </el-form-item>
          <el-form-item label="Expensa" :label-width="formLabelWidth" prop="monto">
            <el-input v-model="newExpensa.monto" />
          </el-form-item>
          <el-form-item label="Mes" :label-width="formLabelWidth" prop="mes">
            <el-date-picker v-model="newExpensa.mes" type="month" placeholder="Seleccione el mes" />
          </el-form-item>
        </el-form>

        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">Cancel</el-button>
          <el-button type="primary" @click="dialogStatus === 'create' ? createData() : updateData()">Confirm</el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination';
import ExpensaResource from '@/api/expensas';
import PropertyResource from '@/api/propiedades';
import waves from '@/directive/waves';

const propertyResource = new PropertyResource();
const expensaResource = new ExpensaResource();

export default {
  name: 'Expensas',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      filterStatus: false,
      list: null,
      total: 0,
      loading: true,
      dialogFormVisible: false,
      dialogTitle: '',
      dialogStatus: '',
      query: {
        keyword: '',
        page: 1,
        limit: 15,
      },
      newExpensa: {
        idPropiedad: '',
        monto: '',
        mes: '',
        status: 'Activo',
      },
      properties: [],
      rules: {
        idPropiedad: [{ required: true, message: 'The property ID field is required.', trigger: 'blur' }],
      },
      formLabelWidth: '120px',
      textMap: {
        update: 'Edit',
        create: 'Create',
      },
    };
  },
  created() {
    this.resetNewExpensa();
    this.getList();
    this.getProperties();
  },
  mounted() {
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await expensaResource.list(this.query);
      const properties = await propertyResource.list(); // Obtener todas las propiedades
      const propertyMap = properties.data.reduce((map, property) => {
        map[property.idPropiedad] = property.nombre;
        return map;
      }, {});

      // Añadir el nombre de la propiedad a cada expensa
      this.list = data.map(expensa => ({
        ...expensa,
        nombrePropiedad: propertyMap[expensa.idPropiedad] || 'N/A',
      }));
      this.total = meta.total;
      this.loading = false;
    },
    async getProperties() {
      try {
        const response = await propertyResource.list(); // Llamar a la API para obtener todas las propiedades
        this.properties = response.data;
      } catch (error) {
        this.$message.error('An error occurred while fetching properties');
      }
    },
    getMonthInLetters(dateString) {
      const date = new Date(dateString);
      const options = { month: 'long' };
      return date.toLocaleDateString('es-ES', options);
    },
    filterByStatus() {
      this.query.status = this.filterStatus ? 'Borrado' : null;
      this.getList();
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewExpensa();
      this.dialogStatus = 'create';
      this.dialogTitle = this.textMap.create;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['expensaForm'].clearValidate();
      });
    },
    async createData() {
      this.$refs['expensaForm'].validate(async (valid) => {
        if (valid) {
          this.loading = true;
          // Formatear la fecha antes de enviarla al backend
          const formattedExpensa = {
            ...this.newExpensa,
            mes: new Date(this.newExpensa.mes).toISOString().split('T')[0],
          };
          try {
            await expensaResource.store(formattedExpensa);
            this.dialogFormVisible = false;
            this.getList();
          } catch (error) {
            console.error(error);
          } finally {
            this.loading = false;
          }
        }
      });
    },
    async handleUpdate(idExpensa) {
      try {
        const response = await expensaResource.get(idExpensa);
        if (response && response.data) {
          this.newExpensa = response.data;
          this.dialogStatus = 'update';
          this.dialogTitle = this.textMap.update;
          this.dialogFormVisible = true;
          this.$nextTick(() => {
            this.$refs['expensaForm'].clearValidate();
          });
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
      }
    },
    updateData() {
      this.$refs['expensaForm'].validate(async (valid) => {
        if (valid) {
          this.loading = true;
          // Formatear la fecha antes de enviarla al backend
          const date = new Date(this.newExpensa.mes);
          date.setDate(2); // Establecer el día al 02
          const formattedExpensa = {
            ...this.newExpensa,
            mes: date.toISOString().split('T')[0], // Formato YYYY-MM-DD
          };
          try {
            await expensaResource.update(this.newExpensa.idExpensa, formattedExpensa);
            this.dialogFormVisible = false;
            this.getList();
          } catch (error) {
            console.error(error);
          } finally {
            this.loading = false;
          }
        }
      });
    },
    async handleDelete(idExpensa, monto) {
      this.$confirm(`This will permanently delete Expensa ${monto}. Continue?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async () => {
        try {
          await expensaResource.destroy(idExpensa);
          this.getList();
        } catch (error) {
          this.$message.error('An error occurred while deleting data');
        }
      });
    },
    async handleRestore(expensa) {
      this.$confirm(`Esta seguro de restaurar ${expensa.monto}?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async () => {
        try {
          const updatedExpensa = { ...expensa, status: 'Activo' };
          await expensaResource.update(expensa.idExpensa, updatedExpensa);
          this.getList();
        } catch (error) {
          this.$message.error('An error occurred while recovering data');
        }
      }).catch(() => {
        this.$message.info('Restore cancelled');
      });
    },
    resetNewExpensa() {
      this.newExpensa = {
        idExpensa: '',
        idPropiedad: '',
        monto: '',
        mes: '',
        status: 'Activo',
      };
    },
  },
};
</script>

<style lang="scss" scoped>
.app-container {
  padding: 20px;
}

.filter-container {
  margin-bottom: 20px;
}

.filter-item {
  margin-right: 10px;
}

.dialog-footer {
  text-align: right;
}
</style>
