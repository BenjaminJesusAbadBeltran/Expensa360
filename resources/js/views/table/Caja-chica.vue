<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item"
        @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus"
        @click="handleCreate">
        {{ $t('table.add') }}
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          {{ scope.row.idCajaChica }}
        </template>
      </el-table-column>
      <el-table-column align="center" label="Total">
        <template slot-scope="scope">
          {{ scope.row.total }}
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="350">
        <template slot-scope="scope">
          <el-button size="mini" type="primary" @click="handleUpdate(scope.row.idCajaChica)">Edit</el-button>
          <el-button size="mini" type="danger"
            @click="handleDelete(scope.row.idCajaChica, scope.row.total)">Delete</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getList" />

    <el-dialog :title="textMap[dialogStatus]" :visible.sync="dialogFormVisible">
      <el-form ref="cajaChicaForm" :rules="rules" :model="newCajaChica" label-position="left" label-width="70px"
        style="width: 400px; margin-left:50px;">
        <el-form-item :label="$t('Total')" prop="total">
          <el-input v-model="newCajaChica.total"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">Cancel</el-button>
        <el-button type="primary" @click="createOrUpdateCajaChica">{{ dialogStatus === 'create' ? 'Create' : 'Update'
          }}</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import CajaChicaResource from '@/api/caja-chica';
import waves from '@/directive/waves'; // Waves directive

const cajaChicaResource = new CajaChicaResource();

export default {
  name: 'CajaChicaList',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      list: null,
      total: 0,
      loading: true,
      query: {
        keyword: '',
        page: 1,
        limit: 15,
      },
      newCajaChica: {},
      dialogFormVisible: false,
      dialogStatus: '',
      textMap: {
        create: 'Create',
        update: 'Update',
      },
      rules: {
        total: [{ required: true, message: 'Total is required', trigger: 'blur' }],
      },
    };
  },
  created() {
    this.resetNewCajaChica();
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await cajaChicaResource.list(this.query);
      this.list = data;
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewCajaChica();
      this.dialogStatus = 'create';
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['cajaChicaForm'].clearValidate();
      });
    },
    createOrUpdateCajaChica() {
      this.$refs['cajaChicaForm'].validate(async (valid) => {
        if (valid) {
          if (this.dialogStatus === 'create') {
            await cajaChicaResource.store(this.newCajaChica);
          } else {
            await cajaChicaResource.update(this.newCajaChica.idCajaChica, this.newCajaChica);
          }
          this.dialogFormVisible = false;
          this.getList();  
        }
      });
    },
    async handleUpdate(idCajaChica) {
      try {
        const response = await cajaChicaResource.get(idCajaChica);
        console.log('Respuesta de la API:', response);

        if (response && response.data) {
          this.newCajaChica = Object.assign({}, response.data);
          console.log('Datos de newCajaChica:', this.newCajaChica);

          this.dialogStatus = 'update';
          this.dialogFormVisible = true;
          this.$nextTick(() => {
            this.$refs['cajaChicaForm'].clearValidate();
          });
        } else {
          this.$message.error('Failed to fetch data: No data found');
          console.error('No data found in response:', response);
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
        console.error('Error al obtener datos:', error);
      }
    },
    async handleDelete(id, total) {
      this.$confirm('This will permanently delete the caja chica with total ' + total + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async () => {
        try {
          const response = await cajaChicaResource.destroy(id);
          if (response && response.message) {
            this.$message({
              message: 'CajaChica deleted successfully',
              type: 'success',
            });

            // Eliminar el registro de la lista localmente
            this.list = this.list.filter(item => item.id !== id);

            // Actualizar la lista de caja chica
            this.getList();
          } else {
            this.$message.error('Failed to delete data');
            console.error('No message found in response:', response);
          }
        } catch (error) {
          this.$message.error('An error occurred while deleting data');
          console.error('Error al eliminar datos:', error);
        }
      });
    },
    resetNewCajaChica() {
      this.newCajaChica = {
        total: '',
      };
    },
  },
};
</script>

<style lang="scss" scoped>
.edit-input {
  padding-right: 100px;
}

.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}

.dialog-footer {
  text-align: left;
  padding-top: 0;
  margin-left: 150px;
}

.app-container {
  flex: 1;
  justify-content: space-between;
  font-size: 14px;
  padding-right: 8px;

  .block {
    float: left;
    min-width: 250px;
  }

  .clear-left {
    clear: left;
  }
}
</style></template>