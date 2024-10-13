<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" style="width: 400px;" placeholder="Search by ID" class="filter-item"
        @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus"
        @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column prop="idPago" label="ID" width="80" />
      <el-table-column prop="idMetodoPago" label="Payment Method ID" />
      <el-table-column prop="idCajaChica" label="Petty Cash ID" />
      <el-table-column prop="monto" label="Amount" />
      <el-table-column prop="fechaPago" label="Payment Date" />
      <el-table-column label="Actions" width="180">
        <template slot-scope="scope">
          <el-button size="mini" type="primary" @click="handleEdit(scope.row.idPago)">Edit</el-button>
          <el-button size="mini" type="danger" @click="handleDelete(scope.row.idPago)">Delete</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getList" />

    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form :model="newPago" ref="pagoForm" :rules="rules">
          <el-form-item label="Payment Method ID" :label-width="formLabelWidth" prop="idMetodoPago">
            <el-input v-model="newPago.idMetodoPago"></el-input>
          </el-form-item>
          <el-form-item label="Petty Cash ID" :label-width="formLabelWidth" prop="idCajaChica">
            <el-input v-model="newPago.idCajaChica"></el-input>
          </el-form-item>
          <el-form-item label="Amount" :label-width="formLabelWidth" prop="monto">
            <el-input v-model="newPago.monto"></el-input>
          </el-form-item>
          <el-form-item label="Payment Date" :label-width="formLabelWidth" prop="fechaPago">
            <el-date-picker v-model="newPago.fechaPago" type="datetime" placeholder="Select date"></el-date-picker>
          </el-form-item>
          <el-form-item label="Status" prop="idStatus" :label-width="formLabelWidth">
            <el-select v-model="newPago.idStatus" placeholder="Select Status">
              <el-option label="Activo" :value="1"></el-option>
              <el-option label="Inactivo" :value="0"></el-option>
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
import PagoResource from '@/api/pagos'; // Asegúrate de que la ruta sea correcta
import waves from '@/directive/waves';

const pagoResource = new PagoResource();

export default {
  name: 'Pagos',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      dialogFormVisible: false,
      dialogTitle: '',
      dialogStatus: '',
      newPago: {
        idPago: null,
        idMetodoPago: '',
        idCajaChica: '',
        monto: '',
        fechaPago: '',
        idStatus: 1,
      },
      list: [],
      query: {
        keyword: '',
        page: 1,
        limit: 15,
      },
      total: 0,
      loading: false,
      formLabelWidth: '120px',
      rules: {
        idMetodoPago: [{ required: true, message: 'Payment Method ID is required', trigger: 'blur' }],
        idCajaChica: [{ required: true, message: 'Petty Cash ID is required', trigger: 'blur' }],
        monto: [{ required: true, message: 'Amount is required', trigger: 'blur' }],
        fechaPago: [{ required: true, message: 'Payment Date is required', trigger: 'blur' }],
        idStatus: [{ required: true, message: 'Status is required', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.resetNewPago();
    this.getList();
  },
  methods: {
    async handleCreate() {
      this.resetNewPago();
      this.dialogTitle = this.$t('table.add');
      this.dialogStatus = 'create';
      this.dialogFormVisible = true;
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
    async createData() {
      this.$refs['pagoForm'].validate(async (valid) => {
        if (valid) {
          // Convertir fechaPago al formato correcto
          this.newPago.fechaPago = new Date(this.newPago.fechaPago).toISOString().slice(0, 19).replace('T', ' ');

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
    resetNewPago() {
      this.newPago = {
        idPago: null,
        idMetodoPago: '',
        idCajaChica: '',
        monto: '',
        fechaPago: '',
        idStatus: 1,
      };
    },
    async getList() {
      this.loading = true;
      const { data, meta } = await pagoResource.list(this.query);
      this.list = data;
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
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