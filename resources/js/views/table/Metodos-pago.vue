<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" style="width: 400px;" placeholder="Buscar por Tipo de Cuenta"
                class="filter-item" @keyup.enter.native="handleFilter"
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
        Metodos de Pago Eliminados
      </el-checkbox>
    </div>
    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 80%; ">
      <el-table-column prop="idMetodo" label="ID" width="80" />
      <el-table-column prop="nombre" label="Tipo de Cuenta" width="220" />
      <el-table-column prop="cuenta" label="Numero de Cuenta" width="250" />
      <el-table-column label="Acciones" width="300">
        <template slot-scope="scope">
          <el-button size="mini" type="warning" @click="handleEdit(scope.row.idMetodo)">Edit</el-button>
          <el-button v-show="scope.row.status == 'Borrado'" size="mini" type="success"
                     @click="handleRestore(scope.row)"
          >Restore</el-button>
          <el-button v-show="scope.row.status !== 'Borrado'" size="mini" type="danger"
                     @click="handleDelete(scope.row.idMetodo, scope.row.nombre)"
          >Eliminar</el-button>
          <el-button size="mini" type="info" @click="showImage(scope.row.imagen)">Show Image</el-button>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
                @pagination="getList"
    />
    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form ref="metodoPagoForm" :model="newMetodoPago" :rules="rules">
          <el-form-item label="Tipo de Cuenta" prop="nombre" :label-width="formLabelWidth">
            <el-input v-model="newMetodoPago.nombre" autocomplete="off" />
          </el-form-item>
          <el-form-item label="Numero de Cuenta" prop="cuenta" :label-width="formLabelWidth">
            <el-input v-model="newMetodoPago.cuenta" autocomplete="off" />
          </el-form-item>
          <el-form-item label="Imagen" prop="imagen" :label-width="formLabelWidth">
            <input type="file" @change="handleImageChange">
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">Cancel</el-button>
          <el-button type="primary" @click="dialogStatus === 'create' ? createData() : updateData()">Confirm</el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :visible.sync="imageDialogVisible" title="Image">
      <img :src="imageSrc" alt="Image" style="width: 100%;">
      <div slot="footer" class="dialog-footer">
        <el-button @click="imageDialogVisible = false">Close</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination';
import MetodoPagoResource from '@/api/metodos-pago';
import waves from '@/directive/waves';

const metodoPagoResource = new MetodoPagoResource();

export default {
  name: 'MetodosPago',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      filterStatus: false,
      list: [],
      total: 0,
      loading: true,
      dialogFormVisible: false,
      dialogTitle: '',
      dialogStatus: '',
      query: {
        keyword: '',
        page: 1,
        limit: 15,
      },
      newMetodoPago: {},
      rules: {
        nombre: [{ required: true, message: 'The nombre field is required.', trigger: 'blur' }],
        cuenta: [{ required: true, message: 'The cuenta field is required.', trigger: 'blur' }],
      },
      formLabelWidth: '200px',
      textMap: {
        update: 'Edit',
        create: 'Create',
      },
      imageDialogVisible: false,
      imageSrc: '',
    };
  },
  created() {
    this.resetNewMetodoPago();
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await metodoPagoResource.list(this.query);
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
      this.resetNewMetodoPago();
      this.dialogStatus = 'create';
      this.dialogTitle = this.textMap.create;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['metodoPagoForm'].clearValidate();
      });
    },
    async createData() {
      this.$refs['metodoPagoForm'].validate((valid) => {
        if (valid) {
          this.loading = true;
          metodoPagoResource
            .store(this.newMetodoPago)
            .then(response => {
              this.$message({
                message: 'New MetodoPago ' + this.newMetodoPago.nombre + ' has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewMetodoPago();
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
    async handleEdit(idMetodo) {
      try {
        const response = await metodoPagoResource.get(idMetodo);
        if (response && response.data) {
          this.newMetodoPago = Object.assign({}, response.data); // copy obj
          this.dialogStatus = 'update';
          this.dialogTitle = this.textMap.update;
          this.dialogFormVisible = true;
          this.$nextTick(() => {
            this.$refs['metodoPagoForm'].clearValidate();
          });
        } else {
          this.$message.error('Failed to fetch data');
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
      }
    },
    updateData() {
      this.$refs['metodoPagoForm'].validate(async valid => {
        if (valid) {
          const tempData = Object.assign({}, this.newMetodoPago);
          await metodoPagoResource.update(tempData.idMetodo, tempData);
          for (const v of this.list) {
            if (v.idMetodo === this.newMetodoPago.idMetodo) {
              const index = this.list.indexOf(v);
              this.list.splice(index, 1, this.newMetodoPago);
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
          this.getList();
        }
      });
    },
    async handleDelete(idMetodo, nombre) {
      this.$confirm(`This will permanently delete MetodoPago ${nombre}. Continue?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        try {
          console.log('Deleting MetodoPago with ID:', idMetodo);
          const response = await metodoPagoResource.destroy(idMetodo);
          console.log('Response from API:', response);
          if (response && response.data) {
            this.$message({
              message: 'MetodoPago deleted successfully',
              type: 'success',
            });
            this.getList(); // Actualiza la lista de métodos de pago
          } else {
            this.$message.error('Failed to delete data');
          }
        } catch (error) {
          console.error('Error while deleting MetodoPago:', error);
          this.$message.error('An error occurred while deleting data');
        }
      });
    },
    resetNewMetodoPago() {
      this.newMetodoPago = {
        nombre: '',
        cuenta: '',
        imagen: '',
        status: 'Activo',
      };
    },
    handleImageChange(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.newMetodoPago.imagen = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    showImage(image) {
      if (!image) {
        this.$message.error('No image available');
        return;
      }
      if (image.startsWith('data:image')) {
        this.imageSrc = image;
      } else {
        this.imageSrc = `/storage/${image}`;
      }
      this.imageDialogVisible = true;
    },
    async handleRestore(metodoPago) {
      this.$confirm(`Esta seguro de restaurar ${metodoPago.nombre}?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        try {
          const response = await metodoPagoResource.get(metodoPago.idMetodo);
          if (response && response.data) {
            const updatedMetodoPago = { ...response.data, status: 'Activo' };
            await metodoPagoResource.update(metodoPago.idMetodo, updatedMetodoPago);
            this.getList();
            this.$message({
              message: 'MetodoPago restored successfully',
              type: 'success',
            });
          } else {
            this.$message.error('Failed to fetch data');
          }
        } catch (error) {
          this.$message.error('An error occurred while recovering data');
        }
      }).catch(() => {
        this.$message.info('Restore cancelled');
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.app-container {
  padding: 40px;
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
