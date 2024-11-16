<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item"
                @keyup.enter.native="handleFilter"
      />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus"
                 @click="handleCreate"
      >
        {{ $t('table.add') }}
      </el-button>
      <el-checkbox v-model="filterStatus" class="filter-item" style="margin-left: 10px;" @change="filterByStatus">
        Mediciones Eliminadas
      </el-checkbox>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column prop="idServicio" label="ID" width="80" />
      <el-table-column prop="nombrePropiedad" label="Propiedad" />
      <el-table-column prop="montoPagar" label="Monto" />
      <el-table-column prop="fechaMedicion" label="Fecha de Medición" />
      <el-table-column prop="medicion" label="Medición" />
      <el-table-column prop="previaMedicion" label="Medición Previa" />
      <el-table-column label="Acciones" width="180">
        <template slot-scope="scope">
          <el-button size="mini" type="primary" @click="handleUpdate(scope.row.idServicio)">Editar</el-button>
          <el-button v-show="scope.row.status == 'Borrado'" size="mini" type="success"
                     @click="handleRestore(scope.row.idServicio)"
          >Restore
          </el-button>
          <el-button v-show="scope.row.status !== 'Borrado'" size="mini" type="danger"
                     @click="handleDelete(scope.row.idServicio, scope.row.medicion)"
          >Eliminar</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
                @pagination="getList"
    />
    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form ref="servicioAguaForm" :rules="rules" :model="newServicioAgua" label-position="left"
                 label-width="120px"
        >
          <el-form-item label="Propiedad" prop="idPropiedad">
            <el-select v-model="newServicioAgua.idPropiedad" placeholder="Selecciona una propiedad">
              <el-option v-for="propiedad in propiedades" :key="propiedad.idPropiedad" :label="propiedad.nombre"
                         :value="propiedad.idPropiedad"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="Monto" prop="monto">
            <el-input v-model="newServicioAgua.montoPagar" />
          </el-form-item>
          <!-- <el-form-item label="Fecha de Medición" prop="fechaMedicion">
            <el-date-picker v-model="newServicioAgua.fechaMedicion" type="date" placeholder="Selecciona una fecha"
              :default-value="new Date()"></el-date-picker>
          </el-form-item> -->
          <el-form-item label="Medición" prop="medicion">
            <el-input v-model="newServicioAgua.medicion" />
          </el-form-item>
          <el-form-item label="Medición Previa" prop="previaMedicion">
            <el-input v-model="newServicioAgua.previaMedicion" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">Cancelar</el-button>
          <el-button type="primary"
                     @click="dialogStatus === 'create' ? createServicioAgua() : updateServicioAgua()"
          >Guardar</el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import ServicioAguaResource from '@/api/servicio-agua'; // Create this API resource similar to PropiedadResource
import PropiedadResource from '@/api/propiedades'; // Create this API resource similar to PropiedadResource
import waves from '@/directive/waves'; // Waves directive

const servicioAguaResource = new ServicioAguaResource();
const propiedadResource = new PropiedadResource();

export default {
  name: 'ServicioAguaList',
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
      newServicioAgua: {
        montoPagar: '',
        fechaMedicion: '',
        medicion: '',
        previaMedicion: '',
        status: 'Activo',
      },
      propiedades: [],
      textMap: {
        create: 'Crear',
        update: 'Actualizar',
      },
      rules: {
        idPropiedad: [{ required: true, message: 'La propiedad es obligatoria', trigger: 'blur' }],
        montoPagar: [{ required: true, message: 'El monto es obligatorio', trigger: 'blur' }],
        medicion: [{ required: true, message: 'La medición es obligatoria', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.resetNewServicioAgua();
    this.getPropiedades();
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await servicioAguaResource.list(this.query);
      const propiedades = await propiedadResource.list();
      const propertyMap = propiedades.data.reduce((map, propiedad) => {
        map[propiedad.idPropiedad] = propiedad.nombre;
        return map;
      }, {});
      // Añadir el nombre de la propiedad a cada expensa
      this.list = data.map(servicioAgua => ({
        ...servicioAgua,
        nombrePropiedad: propertyMap[servicioAgua.idPropiedad] || 'N/A',
      }));

      this.total = meta.total;
      this.loading = false;
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
    filterByStatus() {
      this.query.status = this.filterStatus ? 'Borrado' : null;
      this.getList();
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
      this.$refs['servicioAguaForm'].validate(async(valid) => {
        if (valid) {
          await servicioAguaResource.store(this.newServicioAgua);
          this.dialogFormVisible = false;
          this.getList();
        }
      });
    },
    async handleUpdate(idServicio) {
      try {
        const response = await servicioAguaResource.get(idServicio);
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
      this.$refs['servicioAguaForm'].validate(async(valid) => {
        if (valid) {
          await servicioAguaResource.update(this.newServicioAgua.idServicio, this.newServicioAgua);
          this.dialogFormVisible = false;
          this.getList();
          this.$message({
            message: this.$t('common.success'),
            type: 'success',
          });
        }
      });
    },
    async handleDelete(idServicio, medicion) {
      this.$confirm(`Esto eliminará permanentemente el servicio de agua con monto ${medicion}. ¿Continuar?`, 'Advertencia', {
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
        type: 'warning',
      }).then(async() => {
        await servicioAguaResource.destroy(idServicio);
        this.getList();
      }).catch(() => { });
    },
    async handleRestore(idServicio) {
      this.$confirm(`Esto restaurará el servicio de agua con ID ${idServicio}. ¿Continuar?`, 'Advertencia', {
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
        type: 'warning',
      }).then(async() => {
        await servicioAguaResource.update(idServicio);
        this.getList();
      }).catch(() => { });
    },
    resetNewServicioAgua() {
      this.newServicioAgua = {
        idPropiedad: '',
        montoPagar: '',
        fechaMedicion: '',
        medicion: '',
        previaMedicion: '',
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
