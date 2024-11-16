<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item"
                @keyup.enter.native="handleFilter"
      />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus"
                 @click="handleCreate"
      >
        {{ $t('table.add') }}
      </el-button>
      <el-checkbox v-model="filterStatus" class="filter-item" style="margin-left: 10px;" @change="filterByStatus">
        Pagos Eliminados
      </el-checkbox>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          {{ scope.row.idCajaChica }}
        </template>
      </el-table-column>
      <el-table-column align="center" label="Saldo Inicial">
        <template slot-scope="scope">
          {{ scope.row.saldoInicial }}
        </template>
      </el-table-column>
      <el-table-column align="center" label="Saldo Actual">
        <template slot-scope="scope">
          {{ scope.row.saldoActual }}
        </template>
      </el-table-column>
      <el-table-column align="center" label="Saldo Final">
        <template slot-scope="scope">
          {{ scope.row.saldoFinal }}
        </template>
      </el-table-column>
      <el-table-column align="center" label="Fecha Inicial">
        <template slot-scope="scope">
          {{ scope.row.fecha_Inicial }}
        </template>
      </el-table-column>
      <el-table-column align="center" label="Fecha Final">
        <template slot-scope="scope">
          {{ scope.row.fecha_Final }}
        </template>
      </el-table-column>
      <el-table-column align="center" label="Actions" width="200">
        <template slot-scope="scope">
          <el-button size="mini" type="primary"
                     @click="handleUpdate(scope.row.idCajaChica)"
          >Edit</el-button>
          <el-button v-show="scope.row.status == 'Borrado'" size="mini" type="success"
                     @click="handleRestore(scope.row)"
          >Restore</el-button>
          <el-button v-show="scope.row.status !== 'Borrado'" size="mini" type="danger"
                     @click="handleDelete(scope.row.idCajaChica)"
          >Delete</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
                @pagination="getList"
    />
    <el-dialog :title="textMap[dialogStatus]" :visible.sync="dialogFormVisible">
      <el-form ref="cajaChicaForm" :rules="rules" :model="newCajaChica" label-position="left" label-width="120px"
               style="width: 400px; margin-left:50px;"
      >
        <el-form-item :label="$t('Saldo Inicial')" prop="saldoInicial">
          <el-input v-model="newCajaChica.saldoInicial" />
        </el-form-item>
        <el-form-item :label="$t('Saldo Actual')" prop="saldoActual">
          <el-input v-model="newCajaChica.saldoActual" />
        </el-form-item>
        <el-form-item :label="$t('Saldo Final')" prop="saldoFinal">
          <el-input v-model="newCajaChica.saldoFinal" />
        </el-form-item>
        <el-form-item :label="$t('Fecha Inicial')" prop="fecha_Inicial">
          <el-date-picker v-model="newCajaChica.fecha_Inicial" type="date" placeholder="Select start date"
                          value-format="yyyy-MM-dd"
          />
        </el-form-item>
        <el-form-item :label="$t('Fecha Final')" prop="fecha_Final">
          <el-date-picker v-model="newCajaChica.fecha_Final" type="date" placeholder="Select end date"
                          value-format="yyyy-MM-dd"
          />
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">Cancel</el-button>
        <el-button type="primary" @click="dialogStatus === 'create' ? createData() : updateData()">Confirmar</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import CajaChicaResource from '@/api/caja-chica';
import waves from '@/directive/waves'; // Waves directive

const cajaChicaResource = new CajaChicaResource();

export default {
  name: 'CajaChicaList',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      filterStatus: false,
      total: 0,
      loading: true,
      query: {
        keyword: '',
        page: 1,
        limit: 15,
      },
      newCajaChica: {
        saldoInicial: '',
        saldoActual: '',
        saldoFinal: '',
        fecha_Inicial: '',
        fecha_Final: '',
        status: 'Activo',
      },
      dialogFormVisible: false,
      dialogStatus: '',
      list: [],
      textMap: {
        create: 'Create',
        update: 'Update',
      },
      formLabelWidth: '150px',
      rules: {
        saldoInicial: [{ required: true, message: 'Saldo Inicial is required', trigger: 'blur' }],
        saldoActual: [{ required: true, message: 'Saldo Actual is required', trigger: 'blur' }],
        saldoFinal: [{ required: true, message: 'Saldo Final is required', trigger: 'blur' }],
        fecha_Inicial: [{ required: true, message: 'Fecha Inicial is required', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.resetNewCajaChica();
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await cajaChicaResource.list(this.query);
      this.list = data;
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    filterByStatus() {
      this.query.status = this.filterStatus ? 'Borrado' : null;
      this.getList();
    },
    handleCreate() {
      this.resetNewCajaChica();
      this.dialogStatus = 'create';
      this.dialogFormVisible = true;
      this.dialogTitle = this.textMap.create;
      this.$nextTick(() => {
        this.$refs['cajaChicaForm'].clearValidate();
      });
    },
    async createData() {
      this.$refs['cajaChicaForm'].validate(async(valid) => {
        if (valid) {
          this.loading = true;

          // Use the dates directly from el-date-picker
          const cajaChicaData = {
            ...this.newCajaChica,
            fecha_Inicial: this.newCajaChica.fecha_Inicial,
            fecha_Final: this.newCajaChica.fecha_Final,
          };

          // Debug: Check the date values
          console.log('fecha_Inicial:', this.newCajaChica.fecha_Inicial);
          console.log('fecha_Final:', this.newCajaChica.fecha_Final);

          cajaChicaResource
            .store(cajaChicaData)
            .then(response => {
              this.$message({
                message: 'New Caja Chica has been created successfully.',
                type: 'success',
                duration: 5000,
              });
              this.resetNewCajaChica();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.error(error); // Log the error
              this.$message({
                message: 'An error occurred while creating the Caja Chica.',
                type: 'error',
                duration: 5000,
              });
            })
            .finally(() => {
              this.loading = false;
            });
        }
      });
    },
    async updateData() {
      this.$refs['cajaChicaForm'].validate(async(valid) => {
        if (valid) {
          // Formatear las fechas antes de enviarlas al backend
          const formattedCajaChica = {
            ...this.newCajaChica,
            fecha_Inicial: new Date(this.newCajaChica.fecha_Inicial).toISOString().split('T')[0],
            fecha_Final: new Date(this.newCajaChica.fecha_Final).toISOString().split('T')[0],
          };
          await cajaChicaResource.update(this.newCajaChica.idCajaChica, formattedCajaChica);
          this.dialogFormVisible = false;
          this.getList();
        }
      });
    },
    async handleUpdate(idCajaChica) {
      try {
        const response = await cajaChicaResource.get(idCajaChica);
        console.log('Respuesta de la API:', response);

        if (response && response.data) {
          this.newCajaChica = Object.assign({}, response.data);
          console.log('Datos de newCajaChica:', this.newCajaChica);

          this.dialogStatus = 'update';
          this.dialogFormVisible = true;
          this.$nextTick(() => {
            this.$refs['cajaChicaForm'].clearValidate();
          });
        } else {
          this.$message.error('Failed to fetch data: No data found');
          console.error('No data found in response:', response);
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
        console.error('Error al obtener datos:', error);
      }
    },
    async handleDelete(idCajaChica) {
      this.$confirm('This will permanently delete the caja chica. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        try {
          await cajaChicaResource.destroy(idCajaChica);
          this.getList();
        } catch (error) {
          this.$message.error('An error occurred while deleting data');
        }
      }).catch(() => {
        this.$message.info('Delete cancelled');
      });
    },
    async handleRestore(cajaChica) {
      this.$confirm(`Esta seguro de restaurar ${cajaChica.saldoActual}?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        try {
          const updatedExpensa = { ...cajaChica, status: 'Activo' };
          await cajaChicaResource.update(cajaChica.idCajaChica, updatedExpensa);
          this.getList();
        } catch (error) {
          this.$message.error('An error occurred while recovering data');
        }
      }).catch(() => {
        this.$message.info('Restore cancelled');
      });
    },
    resetNewCajaChica() {
      this.newCajaChica = {
        saldoInicial: '',
        saldoActual: '',
        saldoFinal: '',
        fecha_Inicial: '',
        fecha_Final: '',
        status: 'Activo',
      };
    },
  },
};
</script>

<style lang="scss" scoped>
.edit-input {
  padding-right: 100px;
}

.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}

.dialog-footer {
  text-align: left;
  padding-top: 0;
  margin-left: 150px;
}

.app-container {
  flex: 1;
  justify-content: space-between;
  font-size: 14px;
  padding-right: 8px;

  .block {
    float: left;
    min-width: 250px;
  }

  .clear-left {
    clear: left;
  }
}
</style>
