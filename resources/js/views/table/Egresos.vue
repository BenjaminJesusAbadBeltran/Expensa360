<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item"
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
      <el-table-column prop="idEgreso" label="ID" width="80" />
      <el-table-column prop="idCajaChica" label="ID Caja Chica" />
      <el-table-column prop="monto" label="Monto" />
      <el-table-column prop="idStatus" label="Estado" />
      <el-table-column label="Acciones" width="180">
        <template slot-scope="scope">
          <el-button size="mini" type="primary" @click="handleUpdate(scope.row.idEgreso)">Editar</el-button>
          <el-button size="mini" type="danger" @click="handleDelete(scope.row.idEgreso, scope.row.idEgreso)">Eliminar</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getList" />

    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form ref="egresoForm" :rules="rules" :model="newEgreso" label-position="left" label-width="70px">
          <el-form-item label="ID Caja Chica" prop="idCajaChica">
            <el-input v-model="newEgreso.idCajaChica"></el-input>
          </el-form-item>
          <el-form-item label="Monto" prop="monto">
            <el-input v-model="newEgreso.monto"></el-input>
          </el-form-item>
          <el-form-item label="Status" prop="idStatus" :label-width="formLabelWidth">
            <el-select v-model="newEgreso.idStatus" placeholder="Select Status">
              <el-option label="Activo" :value="1"></el-option>
              <el-option label="Inactivo" :value="0"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">Cancelar</el-button>
          <el-button type="primary" @click="dialogStatus === 'create' ? createEgreso() : updateEgreso()">Guardar</el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import EgresoResource from '@/api/egresos';
import waves from '@/directive/waves'; // Waves directive

const egresoResource = new EgresoResource();

export default {
  name: 'EgresoList',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
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
      newEgreso: {},
      textMap: {
        create: 'Crear',
        update: 'Actualizar',
      },
      rules: {
        idCajaChica: [{ required: true, message: 'El ID de Caja Chica es obligatorio', trigger: 'blur' }],
        monto: [{ required: true, message: 'El monto es obligatorio', trigger: 'blur' }],
        idStatus: [{ required: true, message: 'El estado es obligatorio', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.resetNewEgreso();
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await egresoResource.list(this.query);
      this.list = data;
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
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
      this.$refs['egresoForm'].validate(async (valid) => {
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
      this.$refs['egresoForm'].validate(async (valid) => {
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
      }).then(async () => {
        await egresoResource.destroy(idEgreso);
        this.getList();
      }).catch(() => {});
    },
    resetNewEgreso() {
      this.newEgreso = {
        idCajaChica: '',
        monto: '',
        idStatus: '',
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