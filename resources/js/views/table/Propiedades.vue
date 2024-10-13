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
      <el-table-column prop="idPropiedad" label="ID" width="80" />
      <el-table-column prop="nombre" label="Nombre" />
      <el-table-column prop="numero" label="Número" />
      <el-table-column prop="piso" label="Piso" />
      <el-table-column prop="tipo_propiedad" label="Tipo de Propiedad" />
      <el-table-column label="Acciones" width="180">
        <template slot-scope="scope">
          <el-button size="mini" type="primary" @click="handleUpdate(scope.row.idPropiedad)">Editar</el-button>
          <el-button size="mini" type="danger"
            @click="handleDelete(scope.row.idPropiedad, scope.row.nombre)">Eliminar</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getList" />

    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form ref="propertyForm" :rules="rules" :model="newProperty" label-position="left" label-width="70px"
          style="width: 400px; margin-left:50px;">
          <el-form-item :label="$t('property.numero')" prop="numero">
            <el-input v-model="newProperty.numero" />
          </el-form-item>
          <el-form-item :label="$t('property.piso')" prop="piso">
            <el-input v-model="newProperty.piso" />
          </el-form-item>
          <el-form-item :label="$t('property.nombre')" prop="nombre">
            <el-input v-model="newProperty.nombre" />
          </el-form-item>
          <el-form-item :label="$t('Tipo de Prop.')" prop="tipo_propiedad">
            <el-input v-model="newProperty.tipo_propiedad" />
          </el-form-item>
          <el-form-item label="Status" prop="idStatus" :label-width="formLabelWidth">
            <el-select v-model="newProperty.idStatus" placeholder="Select Status">
              <el-option label="Activo" :value="1"></el-option>
              <el-option label="Inactivo" :value="0"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">Cancelar</el-button>
          <el-button type="primary"
            @click="dialogStatus === 'create' ? createProperty() : updateProperty()">Guardar</el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import PropiedadResource from '@/api/propiedades';
import waves from '@/directive/waves'; // Waves directive

const propiedadResource = new PropiedadResource();

export default {
  name: 'PropertyList',
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
      newProperty: {},
      textMap: {
        create: 'Crear',
        update: 'Actualizar',
      },
      rules: {
        numero: [{ required: true, message: 'El número es obligatorio', trigger: 'blur' }],
        piso: [{ required: true, message: 'El piso es obligatorio', trigger: 'blur' }],
        nombre: [{ required: true, message: 'El nombre es obligatorio', trigger: 'blur' }],
        tipo_propiedad: [{ required: true, message: 'El tipo de propiedad es obligatorio', trigger: 'blur' }],
        idStatus: [{ required: true, message: 'El estado es obligatorio', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.resetNewProperty();
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await propiedadResource.list(this.query);
      this.list = data;
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewProperty();
      this.dialogStatus = 'create';
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['propertyForm'].clearValidate();
      });
    },
    async createProperty() {
      this.$refs['propertyForm'].validate((valid) => {
        if (valid) {
          this.loading = true;
          propiedadResource
            .store(this.newProperty)
            .then(response => {
              this.$message({
                message: 'New MetodoPago ' + this.newProperty.nombre + ' has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewProperty();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.loading = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    async handleUpdate(idPropiedad) {
      try {
        const response = await propiedadResource.get(idPropiedad);
        if (response && response.data) {
          this.newProperty = Object.assign({}, response.data); // copy obj
          this.dialogStatus = 'update';
          this.dialogTitle = this.textMap.update;
          this.dialogFormVisible = true;
          this.$nextTick(() => {
            this.$refs['propertyForm'].clearValidate();
          });
        } else {
          this.$message.error('Failed to fetch data');
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
      }
    },
    updateProperty() {
      this.$refs['propertyForm'].validate(async valid => {
        if (valid) {
          const tempData = Object.assign({}, this.newProperty);

          try {
            const response = await propiedadResource.update(tempData.idPropiedad, tempData);

            for (const v of this.list) {
              if (v.idPropiedad === this.newProperty.idPropiedad) {
                const index = this.list.indexOf(v);
                this.list.splice(index, 1, this.newProperty);
                break;
              }
            }
            this.dialogFormVisible = false;
            this.$notify({
              title: 'Success',
              message: 'Updated successfully',
              type: 'success',
              duration: 2000,
            });
          } catch (error) {
            this.$message.error('An error occurred while updating the property');
            console.error('Error al actualizar la propiedad:', error);
          }
        }
      });
    },
    handleDelete(id, nombre) {
      this.$confirm(`Esto eliminará permanentemente la propiedad ${nombre}. ¿Continuar?`, 'Advertencia', {
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
        type: 'warning',
      }).then(async () => {
        try {
          const response = await propiedadResource.destroy(id);
          if (response && response.message) {
            this.$message({
              message: 'MetodoPago deleted successfully',
              type: 'success',
            });
            this.getList(); // Actualiza la lista de métodos de pago
          } else {
            this.$message.error('Failed to delete data');
          }
        } catch (error) {
          this.$message.error('An error occurred while deleting data');
        }
      });
    },
    resetNewProperty() {
      this.newProperty = {
        numero: '',
        piso: '',
        nombre: '',
        tipo_propiedad: '',
        idStatus: 1,
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
