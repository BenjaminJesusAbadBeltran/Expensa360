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
      <el-table-column prop="idServicio" label="ID" width="80" />
      <el-table-column prop="montoPagar" label="Monto" />
      <el-table-column prop="fechaMedicion" label="Fecha de Medición" />
      <el-table-column prop="medicion" label="Medición" />
      <el-table-column prop="previaMedicion" label="Medición Previa" />
      <el-table-column prop="idStatus" label="Estado" />
      <el-table-column label="Acciones" width="180">
        <template slot-scope="scope">
          <el-button size="mini" type="primary" @click="handleUpdate(scope.row.id)">Editar</el-button>
          <el-button size="mini" type="danger" @click="handleDelete(scope.row.id, scope.row.monto)">Eliminar</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getList" />

    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form ref="servicioAguaForm" :rules="rules" :model="newServicioAgua" label-position="left" label-width="120px">
          <el-form-item label="Monto" prop="monto">
            <el-input v-model="newServicioAgua.montoPagar"></el-input>
          </el-form-item>
          <el-form-item label="Fecha de Medición" prop="fechaMedicion">
            <el-date-picker v-model="newServicioAgua.fechaMedicion" type="datetime" placeholder="Selecciona una fecha" :default-value="new Date()"></el-date-picker>
          </el-form-item>
          <el-form-item label="Medición" prop="medicion">
            <el-input v-model="newServicioAgua.medicion"></el-input>
          </el-form-item>
          <el-form-item label="Medición Previa" prop="previaMedicion">
            <el-input v-model="newServicioAgua.previaMedicion"></el-input>
          </el-form-item>
          <el-form-item label="Status" prop="idStatus" :label-width="formLabelWidth">
            <el-select v-model="newServicioAgua.idStatus" placeholder="Select Status">
              <el-option label="Activo" :value="1"></el-option>
              <el-option label="Inactivo" :value="0"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">Cancelar</el-button>
          <el-button type="primary" @click="dialogStatus === 'create' ? createServicioAgua() : updateServicioAgua()">Guardar</el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import ServicioAguaResource from '@/api/servicio-agua'; // Create this API resource similar to PropiedadResource
import waves from '@/directive/waves'; // Waves directive

const servicioAguaResource = new ServicioAguaResource();

export default {
  name: 'ServicioAguaList',
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
      newServicioAgua: {},
      textMap: {
        create: 'Crear',
        update: 'Actualizar',
      },
      rules: {
        montoPagar: [{ required: true, message: 'El monto es obligatorio', trigger: 'blur' }],
        fechaMedicion: [{ required: true, message: 'La fecha de medición es obligatoria', trigger: 'blur' }],
        medicion: [{ required: true, message: 'La medición es obligatoria', trigger: 'blur' }],
        previaMedicion: [{ required: true, message: 'La medición previa es obligatoria', trigger: 'blur' }],
        idStatus: [{ required: true, message: 'El estado es obligatorio', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.resetNewServicioAgua();
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await servicioAguaResource.list(this.query);
      this.list = data;
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewServicioAgua();
      this.dialogStatus = 'create';
      this.dialogFormVisible = true;
      this.dialogTitle = this.textMap.create;
      this.$nextTick(() => {
        this.$refs['servicioAguaForm'].clearValidate();
      });
    },
    async createServicioAgua() {
      this.$refs['servicioAguaForm'].validate(async (valid) => {
        if (valid) {

          this.newServicioAgua.fechaMedicion = new Date(this.newServicioAgua.fechaMedicion).toISOString().slice(0, 19).replace('T', ' ');

          await servicioAguaResource.store(this.newServicioAgua);
          this.dialogFormVisible = false;
          this.getList();
        }
      });
    },
    async handleUpdate(id) {
      try {
        const response = await servicioAguaResource.get(id);
        if (response && response.data) {
          this.newServicioAgua = response.data;
          this.dialogStatus = 'update';
          this.dialogFormVisible = true;
          this.dialogTitle = this.textMap.update;
          this.$nextTick(() => {
            this.$refs['servicioAguaForm'].clearValidate();
          });
        }
      } catch (error) {
        console.error(error);
      }
    },
    async updateServicioAgua() {
      this.$refs['servicioAguaForm'].validate(async (valid) => {
        if (valid) {
          await servicioAguaResource.update(this.newServicioAgua.id, this.newServicioAgua);
          this.dialogFormVisible = false;
          this.getList();
        }
      });
    },
    async handleDelete(id, monto) {
      this.$confirm(`Esto eliminará permanentemente el servicio de agua con monto ${monto}. ¿Continuar?`, 'Advertencia', {
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
        type: 'warning',
      }).then(async () => {
        await servicioAguaResource.destroy(id);
        this.getList();
      }).catch(() => {});
    },
    resetNewServicioAgua() {
      this.newServicioAgua = {
        montoPagar: '',
        fechaMedicion: '',
        medicion: '',
        previaMedicion: '',
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