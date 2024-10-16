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
        Pagos Eliminados
      </el-checkbox>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column prop="idPago" label="ID" width="80" />
      <el-table-column prop="nombreMetodoPago" label="Metodo de Pago" />
      <el-table-column prop="idCajaChica" label="Caja chica" />
      <el-table-column prop="monto" label="Pago" />
      <el-table-column prop="fechaPago" label="Fecha del Pago" />
      <el-table-column label="Pago realizado por:">
        <template slot-scope="scope">
          <span v-if="scope.row.usuarios && scope.row.usuarios.length">
            <span v-for="(user, index) in scope.row.usuarios" :key="user.idUsuario">
              {{ user.nombre }}<span v-if="index < scope.row.usuarios.length - 1">, </span>
            </span>
          </span>
          <span v-else>No asignado</span>
        </template>
      </el-table-column>
      <el-table-column label="Acciones" width="180">
        <template slot-scope="scope">
          <el-button size="mini" type="warning" @click="handleEdit(scope.row.idPago)">Editar</el-button>
          <el-button v-show="scope.row.idStatus == 0" size="mini" type="success"
            @click="handleRestore(scope.row)">Restore</el-button>
          <el-button v-show="scope.row.idStatus !== 0" size="mini" type="danger"
            @click="handleDelete(scope.row.idPago, scope.row.monto)">Eliminar</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getList" />

    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form :model="newPago" ref="pagoForm" :rules="rules">
          <el-form-item label="Metodo de Pago" :label-width="formLabelWidth" prop="idMetodoPago">
            <el-select v-model="newPago.idMetodoPago" placeholder="Seleccione un metodo de pago">
              <el-option v-for="metodo in metodosPago" :key="metodo.idMetodo" :label="metodo.nombre"
                :value="metodo.idMetodo" />
            </el-select>
          </el-form-item>
          <el-form-item label="Caja Chica" :label-width="formLabelWidth" prop="idCajaChica">
            <el-select v-model="newPago.idCajaChica" placeholder="Seleccione una caja chica">
              <el-option v-for="caja in cajasChicas" :key="caja.idCajaChica" :label="caja.nombre"
                :value="caja.idCajaChica" />
            </el-select>
          </el-form-item>
          <el-form-item label="Monto abonado" :label-width="formLabelWidth" prop="monto">
            <el-input v-model="newPago.monto"></el-input>
          </el-form-item>
          <el-form-item label="Fecha del Pago" :label-width="formLabelWidth" prop="fechaPago">
            <el-date-picker v-model="newPago.fechaPago" type="datetime" placeholder="Select date"></el-date-picker>
          </el-form-item>
          <el-form-item label="Usuarios" :label-width="formLabelWidth" prop="usuarios">
            <el-select v-model="newPago.usuarios" multiple placeholder="Seleccione usuarios">
              <el-option v-for="user in users" :key="user.idUsuario" :label="user.nombre"
                :value="user.idUsuario"></el-option>
            </el-select>
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
import CajaChicaResource from '@/api/caja-chica';
import UserResource from '@/api/user';
import PagoResource from '@/api/pagos'; // Asegúrate de que la ruta sea correcta
import waves from '@/directive/waves';

const pagoResource = new PagoResource();
const metodoPagoResource = new MetodoPagoResource();
const cajaChicaResource = new CajaChicaResource();
const userResource = new UserResource();

export default {
  name: 'Pagos',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      users: [],
      filterStatus: false,
      dialogFormVisible: false,
      dialogTitle: '',
      dialogStatus: '',
      newPago: {
        idMetodoPago: '',
        idCajaChica: '',
        monto: '',
        fechaPago: '',
        idStatus: 1,
        usuarios: [],
      },
      list: [],
      query: {
        keyword: '',
        page: 1,
        limit: 15,
      },
      metodosPago: [],
      cajasChicas: [],
      total: 0,
      loading: false,
      formLabelWidth: '150px',
      rules: {
        idMetodoPago: [{ required: true, message: 'Payment Method ID is required', trigger: 'blur' }],
        idCajaChica: [{ required: true, message: 'Petty Cash ID is required', trigger: 'blur' }],
        monto: [{ required: true, message: 'Amount is required', trigger: 'blur' }],
        fechaPago: [{ required: true, message: 'Payment Date is required', trigger: 'blur' }],
        idStatus: [{ required: true, message: 'Status is required', trigger: 'blur' }],
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
    this.getCajasChicas();
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
      const cajaChica = await cajaChicaResource.list();
      const metodoMap = metodoPago.data.reduce((map, metodo) => {
        map[metodo.idMetodo] = metodo.nombre;
        return map;
      }, {});
      const cajaMap = cajaChica.data.reduce((map, caja) => {
        map[caja.idCajaChica] = caja.nombre;
        return map;
      }, {});

      this.list = data.map(pago => ({
        ...pago,
        nombreMetodoPago: metodoMap[pago.idMetodoPago] || 'N/A',
        nombreCajaChica: cajaMap[pago.idCajaChica] || 'N/A',
      }));

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
    async getCajasChicas() {
      try {
        const response = await cajaChicaResource.list();
        if (response && response.data) {
          this.cajasChicas = response.data;
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
      this.query.idStatus = this.filterStatus ? 0 : null;
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
      this.$refs['pagoForm'].validate(async (valid) => {
        if (valid) {
          try {
            await pagoResource.store(this.newPago);
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
      this.$refs['pagoForm'].validate(async (valid) => {
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
      }).then(async () => {
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
      this.$confirm(`Esta seguro de restaurar el pago de ${pago.monto}?`, 'Warning', {
      confirmButtonText: 'OK',
      cancelButtonText: 'Cancel',
      type: 'warning',
      }).then(async () => {
      try {
        const updatedPago = { ...pago, idStatus: 1 };
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
        idCajaChica: '',
        monto: '',
        fechaPago: '',
        idStatus: 1,
        usuarios: [],
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