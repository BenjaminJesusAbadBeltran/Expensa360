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
      <el-checkbox v-model="filterStatus" @change="filterByStatus" class="filter-item" style="margin-left: 10px;">
        Expensas Eliminadas
      </el-checkbox>
    </div>

    <template>
      <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
        <el-table-column prop="idExpensa" label="ID" width="80" />
        <el-table-column prop="nombrePropiedad" label="Nombre de Propiedad" />
        <el-table-column prop="montoPagar" label="Monto a Pagar" />
        <el-table-column prop="fechaVencimiento" label="Fecha de Vencimiento" />
        <el-table-column label="Acciones" width="280">
          <template slot-scope="scope">
            <el-button size="mini" type="primary" @click="handleEdit(scope.row.idExpensa)">Editar</el-button>
            <el-button v-show="scope.row.idStatus == 0" size="mini" type="success"
              @click="handleRestore(scope.row)">Restore</el-button>
            <el-button v-show="scope.row.idStatus !== 0" size="mini" type="danger"
              @click="handleDelete(scope.row.idExpensa, scope.row.montoPagar)">Eliminar</el-button>
          </template>
        </el-table-column>
        <el-table-column label="Usuarios Asignados">
  <template slot-scope="scope">
    <span v-if="scope.row.usuarios && scope.row.usuarios.length">
      <span v-for="(user, index) in scope.row.usuarios" :key="user.idUsuario">
        {{ user.nombre }}<span v-if="index < scope.row.usuarios.length - 1">, </span>
      </span>
    </span>
    <span v-else>No asignado</span>
  </template>
</el-table-column>
      </el-table>
    </template>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
      @pagination="getList" />

    <el-dialog :visible.sync="dialogFormVisible" :title="dialogTitle">
      <div v-loading="loading" class="form-container">
        <el-form :model="newExpensa" ref="expensaForm" :rules="rules">
          <<el-form-item label="Propiedad" :label-width="formLabelWidth" prop="idPropiedad">
            <el-select v-model="newExpensa.idPropiedad" placeholder="Seleccione una propiedad">
              <el-option
                v-for="property in properties"
                :key="property.idPropiedad"
                :label="property.nombre"
                :value="property.idPropiedad"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="Expensa" :label-width="formLabelWidth" prop="montoPagar">
            <el-input v-model="newExpensa.montoPagar" />
          </el-form-item>
          <el-form-item label="Vencimiento" :label-width="formLabelWidth" prop="fechaVencimiento">
            <el-date-picker v-model="newExpensa.fechaVencimiento" type="date" placeholder="Elija una fecha" />
          </el-form-item>
          <el-form-item label="Usuarios" :label-width="formLabelWidth" prop="usuarios">
            <el-select v-model="newExpensa.usuarios" multiple placeholder="Seleccione usuarios">
              <el-option v-for="user in users" :key="user.idUsuario" :label="user.nombre" :value="user.idUsuario"></el-option>
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
import ExpensaResource from '@/api/expensas';
import PropertyResource from '@/api/propiedades';
import UserResource from '@/api/user';
import waves from '@/directive/waves';

const userResource = new UserResource();
const propertyResource = new PropertyResource();
const expensaResource = new ExpensaResource();

export default {
  name: 'Expensas',
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      users: [], // Definir users
      filterStatus: false,
      list: null,
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
      newExpensa: {
        idPropiedad: '',
        montoPagar: '',
        fechaVencimiento: '',
        idStatus: 1,
        usuarios: [], // Definir la propiedad usuarios dentro de newExpensa
      },
      properties: [],
      rules: {
        idPropiedad: [{ required: true, message: 'The property ID field is required.', trigger: 'blur' }],
        montoPagar: [{ required: true, message: 'The amount to pay field is required.', trigger: 'blur' }],
        fechaVencimiento: [{ required: true, message: 'The due date field is required.', trigger: 'blur' }],
        idStatus: [{ required: true, message: 'The status field is required.', trigger: 'blur' }],
      },
      formLabelWidth: '120px',
      textMap: {
        update: 'Edit',
        create: 'Create',
      },
    };
  },
  created() {
    this.resetNewExpensa();
    this.getList();
    this.getProperties();
    this.getUsers(); // Llamar a getUsers
  },
  mounted() {
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data, meta } = await expensaResource.list(this.query);
      const properties = await propertyResource.list(); // Obtener todas las propiedades
      const propertyMap = properties.data.reduce((map, property) => {
        map[property.idPropiedad] = property.nombre;
        return map;
      }, {});

      // Añadir el nombre de la propiedad a cada expensa
      this.list = data.map(expensa => ({
        ...expensa,
        nombrePropiedad: propertyMap[expensa.idPropiedad] || 'N/A',
      }));
      this.total = meta.total;
      this.loading = false;
    },
    async getProperties() {
      try {
        const response = await propertyResource.list(); // Llamar a la API para obtener todas las propiedades
        this.properties = response.data;
      } catch (error) {
        this.$message.error('An error occurred while fetching properties');
      }
    },
    async getUsers() {
      try {
        const response = await userResource.list(); // Llamar a la API para obtener todos los usuarios
        this.users = response.data;
      } catch (error) {
        this.$message.error('An error occurred while fetching users');
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
    handleCreate() {
      this.resetNewExpensa();
      this.dialogStatus = 'create';
      this.dialogTitle = this.textMap.create;
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['expensaForm'].clearValidate();
      });
    },
    async createData() {
      this.$refs['expensaForm'].validate((valid) => {
        if (valid) {
          this.loading = true;
          expensaResource
            .store(this.newExpensa)
            .then(() => {
              this.$message({
                message: 'New Expensa has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewExpensa();
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
    async handleEdit(idExpensa) {
      try {
        const response = await expensaResource.get(idExpensa);
        if (response && response.data) {
          this.newExpensa = response.data;
          this.dialogStatus = 'update';
          this.dialogTitle = this.textMap.update;
          this.dialogFormVisible = true;
          this.$nextTick(() => {
            this.$refs['expensaForm'].clearValidate();
          });
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
      }
    },
    updateData() {
      this.$refs['expensaForm'].validate(async valid => {
        if (valid) {
          expensaResource.update(this.newExpensa.idExpensa, this.newExpensa).then(() => {
            this.dialogFormVisible = false;
            this.getList();
          });
        }
      });
    },
    async handleDelete(id, montoPagar) {
      this.$confirm(`This will permanently delete Expensa ${montoPagar}. Continue?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async () => {
        try {
          await expensaResource.destroy(id);
          this.getList();
        } catch (error) {
          this.$message.error('An error occurred while deleting data');
        }
      });
    },
    async handleRestore(expensa) {
      this.$confirm(`Esta seguro de restaurar ${expensa.montoPagar}?`, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async () => {
        try {
          const updatedExpensa = { ...expensa, idStatus: 1 };
          await expensaResource.update(expensa.idExpensa, updatedExpensa);
          this.getList();
        } catch (error) {
          this.$message.error('An error occurred while recovering data');
        }
      }).catch(() => {
        this.$message.info('Restore cancelled');
      });
    },
    resetNewExpensa() {
      this.newExpensa = {
        idPropiedad: '',
        montoPagar: '',
        fechaVencimiento: '',
        idStatus: 1,
        usuarios: [], // Añadir esta línea
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
