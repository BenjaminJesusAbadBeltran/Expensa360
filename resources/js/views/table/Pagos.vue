<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" style="width: 400px;" placeholder="Search by ID" class="filter-item"
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
      <el-table-column prop="idPago" label="ID" width="80" />
      <el-table-column prop="nombreMetodoPago" label="Metodo de Pago" />
      <el-table-column prop="nombreUsuario" label="Usuario" />
      <el-table-column prop="nombrePropiedad" label="Propiedad" />
      <el-table-column prop="montoTotal" label="Monto Total" />
      <el-table-column prop="fechaPago" label="Fecha de Pago" />
      <el-table-column prop="observaciones" label="Observaciones" />
      <el-table-column label="Acciones" width="180">
        <template slot-scope="scope">
          <el-button size="mini" type="warning" @click="handleEdit(scope.row.idPago)">Editar</el-button>
          <el-button v-show="scope.row.status == 'Borrado'" size="mini" type="success"
                     @click="handleRestore(scope.row)"
          >Restore</el-button>
          <el-button v-show="scope.row.status !== 'Borrado'" size="mini" type="danger"
                     @click="handleDelete(scope.row.idPago, scope.row.montoTotal)"
          >Eliminar</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
                @pagination="getList"
    />
    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form ref="pagoForm" :model="newPago" :rules="rules">
          <el-form-item label="Metodo de Pago" :label-width="formLabelWidth" prop="idMetodoPago">
            <el-select v-model="newPago.idMetodoPago" placeholder="Seleccione un metodo de pago">
              <el-option v-for="metodo in metodosPago" :key="metodo.idMetodo" :label="metodo.nombre"
                         :value="metodo.idMetodo"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="Propiedad" :label-width="formLabelWidth" prop="idPropiedad">
            <el-select v-model="newPago.idPropiedad" placeholder="Seleccione una propiedad">
              <el-option v-for="propiedad in propiedades" :key="propiedad.idPropiedad" :label="propiedad.nombre"
                         :value="propiedad.idPropiedad"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="Usuario" :label-width="formLabelWidth" prop="idUsuario">
            <el-select v-model="newPago.idUsuario" placeholder="Seleccione un usuario">
              <el-option v-for="user in users" :key="user.idUsuario" :label="user.nombre" :value="user.idUsuario" />
            </el-select>
          </el-form-item>
          <el-form-item label="Monto total" :label-width="formLabelWidth" prop="montoTotal">
            <el-input v-model="newPago.montoTotal" />
          </el-form-item>
          <el-form-item label="Fecha del Pago" :label-width="formLabelWidth" prop="fechaPago">
            <el-date-picker v-model="newPago.fechaPago" type="datetime" placeholder="Select date" />
          </el-form-item>
          <el-form-item label="Observaciones" :label-width="formLabelWidth" prop="observaciones">
            <el-input v-model="newPago.observaciones" />
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
import MetodoPagoResource from '@/api/metodos-pago';
import PropiedadResource from '@/api/propiedades';
import UserResource from '@/api/user';
import PagoResource from '@/api/pagos'; // Asegúrate de que la ruta sea correcta
import waves from '@/directive/waves';

const pagoResource = new PagoResource();
const metodoPagoResource = new MetodoPagoResource();
const propiedadResource = new PropiedadResource();
const userResource = new UserResource();

export default {
  name: 'Pagos',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      filterStatus: false,
      dialogFormVisible: false,
      dialogTitle: '',
      dialogStatus: '',
      newPago: {
        idMetodoPago: '',
        idUsuario: '',
        idPropiedad: '',
        montoTotal: '',
        fechaPago: '',
        observaciones: '',
        status: 'Activo',
      },
      list: [],
      query: {
        keyword: '',
        page: 1,
        limit: 15,
      },
      metodosPago: [],
      propiedades: [],
      users: [],
      total: 0,
      loading: false,
      formLabelWidth: '150px',
      rules: {
        idMetodoPago: [{ required: true, message: 'Payment Method ID is required', trigger: 'blur' }],
        idUsuario: [{ required: true, message: 'User ID is required', trigger: 'blur' }],
        idPropiedad: [{ required: true, message: 'Property ID is required', trigger: 'blur' }],
        montoTotal: [{ required: true, message: 'Amount is required', trigger: 'blur' }],
        fechaPago: [{ required: true, message: 'Payment Date is required', trigger: 'blur' }],
        status: [{ required: true, message: 'Status is required', trigger: 'blur' }],
      },
      textMap: {
        update: 'Edit',
        create: 'Create',
      },
    };
  },
  created() {
    this.resetNewPago();
    this.getList();
    this.getMetodosPago();
    this.getPropiedades();
    this.getUsers();
  },
  mounted(){
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await pagoResource.list(this.query);
      const metodoPago = await metodoPagoResource.list();
      const propiedades = await propiedadResource.list();
      const users = await userResource.list();
      const metodoMap = metodoPago.data.reduce((map, metodo) => {
        map[metodo.idMetodo] = metodo.nombre;
        return map;
      }, {});
      const cajaMap = propiedades.data.reduce((map, propiedad) => {
        map[propiedad.idPropiedad] = propiedad.nombre;
        return map;
      }, {});
      const userMap = users.data.reduce((map, user) => {
        map[user.idUsuario] = user.nombre;
        return map;
      }, {});

      this.list = data.map(pago => ({
        ...pago,
        nombreMetodoPago: metodoMap[pago.idMetodoPago] || 'N/A',
        nombrePropiedad: cajaMap[pago.idPropiedad] || 'N/A',
        nombreUsuario: userMap[pago.idUsuario] || 'N/A',
      }));
      this.list.data;
      this.total = meta.total;
      this.loading = false;
    },
    async getMetodosPago() {
      try {
        const response = await metodoPagoResource.list();
        if (response && response.data) {
          this.metodosPago = response.data;
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
      }
    },
    async getPropiedades() {
      try {
        const response = await propiedadResource.list();
        if (response && response.data) {
          this.propiedades = response.data;
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
      }
    },
    async getUsers() {
      try {
        const response = await userResource.list();
        if (response && response.data) {
          this.users = response.data;
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
      }
    },
    filterByStatus() {
      this.query.status = this.filterStatus ? 'Borrado' : null;
      this.getList();
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    async handleCreate() {
      this.resetNewPago();
      this.dialogTitle = this.textMap.create;
      this.dialogStatus = 'create';
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['pagoForm'].clearValidate();
      });
    },
    async createData() {
      this.$refs['pagoForm'].validate(async(valid) => {
        if (valid) {
          try {
            await pagoResource.store(this.newPago)
              .then(response => {
                this.$message({
                  message: 'New Pago ' + this.newPago.montoTotal + ' has been created successfully.',
                  type: 'success',
                  duration: 5 * 1000,
                });
                this.resetNewPago();
                this.dialogFormVisible = false;
                this.getList();
              });
          } catch (error) {
            this.$message.error('An error occurred while saving data');
          }
        }
      });
    },
    async handleEdit(idPago) {
      try {
        const response = await pagoResource.get(idPago);
        if (response && response.data) {
          this.newPago = Object.assign({}, response.data);
          this.dialogTitle = this.$t('table.edit');
          this.dialogStatus = 'update';
          this.$nextTick(() => {
            this.$refs['pagoForm'].clearValidate();
          });
          this.dialogFormVisible = true;
        } else {
          this.$message.error('Failed to fetch data');
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
      }
    },
    async updateData() {
      this.$refs['pagoForm'].validate(async(valid) => {
        if (valid) {
          // Convertir fechaPago al formato correcto
          this.newPago.fechaPago = new Date(this.newPago.fechaPago).toISOString().slice(0, 19).replace('T', ' ');

          try {
            await pagoResource.update(this.newPago.idPago, this.newPago);
            this.dialogFormVisible = false;
            this.getList();
            this.$message({
              message: this.$t('common.success'),
              type: 'success',
            });
          } catch (error) {
            this.$message.error('An error occurred while saving data');
          }
        }
      });
    },
    async handleDelete(idPago) {
      this.$confirm(`This will permanently delete Pago ${idPago}. Continue?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        try {
          await pagoResource.destroy(idPago);
          this.getList();
          this.$message({
            message: this.$t('common.success'),
            type: 'success',
          });
        } catch (error) {
          this.$message.error('An error occurred while deleting data');
        }
      });
    },
    async handleRestore(pago) {
      this.$confirm(`Esta seguro de restaurar el pago de ${pago.montoTotal}?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        try {
          const updatedPago = { ...pago, status: 'Activo' };
          await pagoResource.update(pago.idPago, updatedPago);
          this.getList();
        } catch (error) {
          this.$message.error('An error occurred while recovering data');
        }
      }).catch(() => {
        this.$message.info('Restore cancelled');
      });
    },
    resetNewPago() {
      this.newPago = {
        idPago: null,
        idMetodoPago: '',
        idUsuario: '',
        idPropiedad: '',
        montoTotal: '',
        fechaPago: '',
        observaciones: '',
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
