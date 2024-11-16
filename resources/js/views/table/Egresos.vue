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
      <el-table-column prop="idEgreso" label="ID" width="80" />
      <el-table-column prop="idCajaChica" label="ID Caja Chica" />
      <el-table-column prop="concepto" label="Concepto" />
      <el-table-column prop="monto" label="Monto" />
      <el-table-column prop="fechaEgreso" label="Fecha de Egreso" />
      <el-table-column label="Acciones" width="180">
        <template slot-scope="scope">
          <el-button v-show="scope.row.status !== 'Borrado'" size="mini" type="warning"
                     @click="handleUpdate(scope.row.idEgreso)"
          >Editar</el-button>
          <el-button v-show="scope.row.status == 'Borrado'" size="mini" type="success"
                     @click="handleRestore(scope.row)"
          >Restore</el-button>
          <el-button v-show="scope.row.status !== 'Borrado'" size="mini" type="danger"
                     @click="handleDelete(scope.row.idEgreso)"
          >Eliminar</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
                @pagination="getList"
    />
    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form ref="egresoForm" :rules="rules" :model="newEgreso" label-position="left" :label-width="formLabelWidth">
          <el-form-item label="ID Caja Chica" prop="idCajaChica">
            <el-select v-model="newEgreso.idCajaChica" placeholder="Seleccione una Caja Chica">
              <el-option v-for="cajaChica in cajasChicas" :key="cajaChica.idCajaChica" :label="cajaChica.idCajaChica"
                         :value="cajaChica.idCajaChica"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="Concepto" prop="concepto" :label-width="formLabelWidth">
            <el-input v-model="newEgreso.concepto" />
          </el-form-item>
          <el-form-item label="Monto" prop="monto" :label-width="formLabelWidth">
            <el-input v-model="newEgreso.monto" />
          </el-form-item>
          <el-form-item label="Fecha de Egreso" prop="fechaEgreso" :label-width="formLabelWidth">
            <el-date-picker v-model="newEgreso.fechaEgreso" type="date" placeholder="Seleccione una fecha"
                            :default-value="new Date()"
            />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">Cancelar</el-button>
          <el-button type="primary"
                     @click="dialogStatus === 'create' ? createEgreso() : updateEgreso()"
          >Guardar</el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import EgresoResource from '@/api/egresos';
import CajaChicaResource from '@/api/caja-chica';
import waves from '@/directive/waves'; // Waves directive

const egresoResource = new EgresoResource();
const cajaChicaResource = new CajaChicaResource();

export default {
  name: 'EgresoList',
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
        page: 1,
        limit: 20,
        keyword: '',
      },
      newEgreso: {
        idCajaChica: '',
        concepto: '',
        monto: '',
        fechaEgreso: '',
        status: 'Activo',
      },
      cajasChicas: [],
      textMap: {
        create: 'Crear',
        update: 'Actualizar',
      },
      rules: {
        idCajaChica: [{ required: true, message: 'El ID de Caja Chica es obligatorio', trigger: 'blur' }],
        monto: [{ required: true, message: 'El monto es obligatorio', trigger: 'blur' }],
      },
      formLabelWidth: '200px',
    };
  },
  created() {
    this.resetNewEgreso();
    this.getList();
    this.getCajasChicas();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await egresoResource.list(this.query);
      const cajasChicas = await cajaChicaResource.list();

      const cajaChicaMap = cajasChicas.data.reduce((map, caja) => {
        map[caja.idCajaChica] = caja.idCajaChica;
        return map;
      }, {});

      this.list = data.map((egreso) => {
        return {
          ...egreso,
          cajaChica: cajaChicaMap[egreso.idCajaChica],
        };
      });
      this.list = data;
      this.total = meta.total;
      this.loading = false;
    },
    async getCajasChicas() {
      const { data } = await cajaChicaResource.list();
      this.cajasChicas = data;
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
      this.resetNewEgreso();
      this.dialogStatus = 'create';
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['egresoForm'].clearValidate();
      });
    },
    async createEgreso() {
      this.$refs['egresoForm'].validate(async(valid) => {
        if (valid) {
          await egresoResource.store(this.newEgreso);
          this.dialogFormVisible = false;
          this.getList();
        }
      });
    },
    async handleUpdate(idEgreso) {
      try {
        const response = await egresoResource.get(idEgreso);
        if (response && response.data) {
          this.newEgreso = response.data;
          this.dialogStatus = 'update';
          this.dialogFormVisible = true;
          this.dialogTitle = 'Actualizar Egreso';
          this.$nextTick(() => {
            this.$refs['egresoForm'].clearValidate();
          });
        }
      } catch (error) {
        console.error(error);
      }
    },
    async updateEgreso() {
      this.$refs['egresoForm'].validate(async(valid) => {
        if (valid) {
          await egresoResource.update(this.newEgreso.idEgreso, this.newEgreso);
          this.dialogFormVisible = false;
          this.getList();
        }
      });
    },
    async handleDelete(idEgreso, id) {
      this.$confirm(`Esto eliminará permanentemente el egreso ${id}. ¿Continuar?`, 'Advertencia', {
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
        type: 'warning',
      }).then(async() => {
        await egresoResource.destroy(idEgreso);
        this.getList();
      }).catch(() => { });
    },
    async handleRestore(egreso) {
      this.$confirm(`Esta seguro de restaurar el pago de ${egreso.idEgreso}?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        try {
          const updatedEgreso = { ...egreso, status: 'Activo' };
          await egresoResource.update(egreso.idEgreso, updatedEgreso);
          this.getList();
        } catch (error) {
          this.$message.error('An error occurred while recovering data');
        }
      }).catch(() => {
        this.$message.info('Restore cancelled');
      });
    },
    resetNewEgreso() {
      this.newEgreso = {
        idCajaChica: '',
        concepto: '',
        monto: '',
        fechaEgreso: new Date().toISOString().split('T')[0],
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
