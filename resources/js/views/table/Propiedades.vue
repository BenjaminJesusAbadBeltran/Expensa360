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
        Expensas Eliminadas
      </el-checkbox>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column prop="idPropiedad" label="ID" width="80" />
      <el-table-column prop="nombre" label="Nombre" />
      <el-table-column prop="numero" label="Número" />
      <el-table-column prop="piso" label="Piso" />
      <el-table-column prop="tipo_propiedad" label="Tipo de Propiedad" />
      <el-table-column label="Usuarios">
        <template slot-scope="scope">
          <el-tag v-for="usuario in scope.row.usuarios" :key="usuario.idUsuario" type="info" style="margin-right: 5px;">
            {{ usuario.nombre }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Acciones" width="180">
        <template slot-scope="scope">
          <el-button v-show="scope.row.status !== 'Borrado'" size="mini" type="warning"
                     @click="handleUpdate(scope.row.idPropiedad)"
          >Editar</el-button>
          <el-button v-show="scope.row.status == 'Borrado'" size="mini" type="success"
                     @click="handleRestore(scope.row)"
          >Restore</el-button>
          <el-button v-show="scope.row.status !== 'Borrado'" size="mini" type="danger"
                     @click="handleDelete(scope.row.idPropiedad, scope.row.nombre)"
          >Eliminar</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
                @pagination="getList"
    />
    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form ref="propertyForm" :rules="rules" :model="newProperty" label-position="left"
                 style="width: 400px; margin-left:50px;"
        >
          <el-form-item :label="$t('Numero')" :label-width="formLabelWidth" prop="numero">
            <el-input v-model="newProperty.numero" />
          </el-form-item>
          <el-form-item :label="$t('Piso')" :label-width="formLabelWidth" prop="piso">
            <el-select v-model="newProperty.piso" placeholder="Seleccione piso">
              <el-option v-for="piso in ['1', '2', '3', '4', '5']" :key="piso" :label="piso" :value="piso" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('Nombre o Alias')" :label-width="formLabelWidth" prop="nombre">
            <el-input v-model="newProperty.nombre" />
          </el-form-item>
          <el-form-item :label="$t('Tipo de Propiedad')" :label-width="formLabelWidth" prop="tipo_propiedad">
            <el-select v-model="newProperty.tipo_propiedad" placeholder="Seleccione tipo de propiedad">
              <el-option label="Departamento" value="Departamento" />
              <el-option label="Parqueo" value="Parqueo" />
              <el-option label="Baulera" value="Baulera" />
            </el-select>
          </el-form-item>
          <el-form-item label="Usuarios" :label-width="formLabelWidth" prop="usuarios">
            <el-select v-model="newProperty.usuarios" multiple placeholder="Seleccione usuarios">
              <el-option v-for="user in usuarios" :key="user.idUsuario" :label="user.nombre"
                         :value="user.idUsuario"
              />
            </el-select>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">Cancelar</el-button>
          <el-button type="primary"
                     @click="dialogStatus === 'create' ? createProperty() : updateProperty()"
          >Guardar</el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import PropiedadResource from '@/api/propiedades';
import UserResource from '@/api/user';
import waves from '@/directive/waves'; // Waves directive

const propiedadResource = new PropiedadResource();
const usuarioResource = new UserResource();

export default {
  name: 'PropiedadList',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      filterStatus: false,
      formLabelWidth: '200px',
      query: {
        keyword: '',
        page: 1,
        limit: 15,
      },
      list: [],
      total: 0,
      loading: false,
      dialogFormVisible: false,
      dialogTitle: '',
      dialogStatus: '',
      newProperty: {
        numero: '',
        piso: '',
        nombre: '',
        tipo_propiedad: '',
        status: 'Activo',
        usuarios: [],
      },
      usuarios: [],
      userMap: {}, // Declarar userMap en el estado del componente
      rules: {
        numero: [{ required: true, message: 'Número es obligatorio', trigger: 'blur' }],
        piso: [{ required: true, message: 'Piso es obligatorio', trigger: 'blur' }],
        nombre: [{ required: true, message: 'Nombre es obligatorio', trigger: 'blur' }],
        tipo_propiedad: [{ required: true, message: 'Tipo de Propiedad es obligatorio', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.getList();
    this.getUsuarios();
  },
  methods: {
    async getList() {
      this.loading = true;
      try {
        const { data, meta } = await propiedadResource.list(this.query);
        this.list = data;
        this.total = meta.total;
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    filterByStatus() {
      this.query.status = this.filterStatus ? 'Borrado' : null;
      this.getList();
    },
    async getUsuarios() {
      try {
        const response = await usuarioResource.list();
        this.usuarios = response.data;
      } catch (error) {
        console.error(error);
      }
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewProperty();
      this.dialogStatus = 'create';
      this.dialogFormVisible = true;
      this.dialogTitle = 'Crear Propiedad';
      this.$nextTick(() => {
        this.$refs['propertyForm'].clearValidate();
      });
    },
    async createProperty() {
      this.$refs['propertyForm'].validate(async(valid) => {
        if (valid) {
          this.loading = true;
          try {
            await propiedadResource.store(this.newProperty);
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
    async handleUpdate(idPropiedad) {
      try {
        const response = await propiedadResource.get(idPropiedad);
        const propiedad = response.data;

        // Asegúrate de mapear los usuarios para que sean solo los IDs
        propiedad.usuarios = propiedad.usuarios.map(usuario => usuario.idUsuario);

        this.newProperty = propiedad;
        this.dialogStatus = 'update';
        this.dialogFormVisible = true;
        this.dialogTitle = 'Editar Propiedad';
        this.$nextTick(() => {
          this.$refs['propertyForm'].clearValidate();
        });
      } catch (error) {
        console.error(error);
      }
    },

    async updateProperty() {
      this.$refs['propertyForm'].validate(async(valid) => {
        if (valid) {
          this.loading = true;
          try {
            await propiedadResource.update(this.newProperty.idPropiedad, this.newProperty);
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
    async handleDelete(idPropiedad, nombre) {
      try {
        await this.$confirm(`Esto eliminará permanentemente la propiedad ${nombre}. ¿Continuar?`, 'Advertencia', {
          confirmButtonText: 'Sí',
          cancelButtonText: 'No',
          type: 'warning',
        });
        this.loading = true;
        await propiedadResource.destroy(idPropiedad);
        this.getList();
      } catch (error) {
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async handleRestore(propiedad) {
      this.$confirm(`¿Está seguro de restaurar la propiedad ${propiedad.nombre}?`, 'Advertencia', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancelar',
        type: 'warning',
      }).then(async() => {
        try {
          const updatedPropiedad = { ...propiedad, status: 'Activo', usuarios: propiedad.usuarios.map(usuario => usuario.idUsuario) };
          await propiedadResource.update(propiedad.idPropiedad, updatedPropiedad);
          this.getList();
        } catch (error) {
          console.error('Error during property restoration:', error);
          this.$message.error('Ocurrió un error al restaurar la propiedad');
        }
      }).catch(() => {
        this.$message.info('Restauración cancelada');
      });
    },

    resetNewProperty() {
      this.newProperty = {
        numero: '',
        piso: '',
        nombre: '',
        tipo_propiedad: '',
        status: 'Activo',
        usuarios: [],
      };
    },
  },
};
</script>

<style scoped>
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
