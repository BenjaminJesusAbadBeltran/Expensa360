<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="query.keyword" :placeholder="$t('table.keyword')" style="width: 200px;" class="filter-item"
                @keyup.enter.native="handleFilter"
      />
      <el-select v-model="query.role" :placeholder="$t('table.role')" clearable style="width: 150px" class="filter-item"
                 @change="handleFilter"
      >
        <el-option v-for="item in roles" :key="item" :label="item | uppercaseFirst" :value="item" />
      </el-select>
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus"
                 @click="handleCreate"
      >
        {{ $t('table.add') }}
      </el-button>
      <el-button v-waves :loading="downloading" class="filter-item" type="primary" icon="el-icon-download"
                 @click="handleDownload"
      >
        {{ $t('table.export') }}
      </el-button>
      <el-checkbox v-model="filterStatus" class="filter-item" style="margin-left: 10px;" @change="filterByStatus">
        Usuarios Eliminados
      </el-checkbox>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <!-- <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column> -->

      <el-table-column align="center" label="Nomnre" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.nombre }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Apellido Paterno" width="150">
        <template slot-scope="scope">
          <span>{{ scope.row.apellidoPaterno }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Apellido Materno" width="150">
        <template slot-scope="scope">
          <span>{{ scope.row.apellidoMaterno }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Telefono" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.telefono }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Correo">
        <template slot-scope="scope">
          <span>{{ scope.row.email }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Roles" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.roles.join(', ') }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Acciones" width="280">
        <template slot-scope="scope">
          <el-button type="primary" size="small" icon="el-icon-edit" @click="handleEdit(scope.row.idUsuario);" />
          <el-button v-if="!scope.row.roles.includes('super_admin')" v-permission="['manage permission']" type="warning"
                     size="small" icon="el-icon-edit" @click="handleEditPermissions(scope.row.idUsuario);"
          >
            Permissions
          </el-button>
          <el-button v-if="scope.row.roles.includes('socio') || scope.row.roles.includes('inquilino')"
                     v-permission="['manage user']" type="danger" size="small" icon="el-icon-delete"
                     @click="handleDelete(scope.row.idUsuario, scope.row.nombre);"
          />
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="query.page" :limit.sync="query.limit"
                @pagination="getList"
    />
    <el-dialog :visible.sync="dialogPermissionVisible" :title="'Edit Permissions - ' + currentUser.nombre">
      <div v-if="currentUser.nombre" v-loading="dialogPermissionLoading" class="form-container">
        <div class="permissions-container">
          <div class="block">
            <el-form :model="currentUser" label-width="80px" label-position="top">
              <el-form-item label="Menus">
                <el-tree ref="menuPermissions" :data="normalizedMenuPermissions"
                         :default-checked-keys="permissionKeys(userMenuPermissions)" :props="permissionProps" show-checkbox
                         node-key="id" class="permission-tree"
                />
              </el-form-item>
            </el-form>
          </div>
          <div class="block">
            <el-form :model="currentUser" label-width="80px" label-position="top">
              <el-form-item label="Permissions">
                <el-tree ref="otherPermissions" :data="normalizedOtherPermissions"
                         :default-checked-keys="permissionKeys(userOtherPermissions)" :props="permissionProps" show-checkbox
                         node-key="id" class="permission-tree"
                />
              </el-form-item>
            </el-form>
          </div>
          <div class="clear-left" />
        </div>
        <div style="text-align:right;">
          <el-button type="danger" @click="dialogPermissionVisible = false">
            {{ $t('permission.cancel') }}
          </el-button>
          <el-button type="primary" @click="confirmPermission">
            {{ $t('permission.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="'Create new user'" :visible.sync="dialogFormVisible">
      <div v-loading="userCreating" class="form-container">
        <el-form ref="userForm" :rules="rules" :model="newUser" label-position="left" label-width="200px"
                 style="max-width: 500px;"
        >
          <el-form-item :label="$t('user.role')" prop="role">
            <el-select v-model="newUser.role" class="filter-item" placeholder="Please select role">
              <el-option v-for="item in nonAdminRoles" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('user.nombre')" prop="nombre">
            <el-input v-model="newUser.nombre" />
          </el-form-item>
          <el-form-item :label="$t('user.apellidoPaterno')" prop="apellidoPaterno">
            <el-input v-model="newUser.apellidoPaterno" />
          </el-form-item>
          <el-form-item :label="$t('user.apellidoMaterno')" prop="apellidoMaterno">
            <el-input v-model="newUser.apellidoMaterno" />
          </el-form-item>
          <el-form-item :label="$t('user.email')" prop="email">
            <el-input v-model="newUser.email" />
          </el-form-item>
          <el-form-item :label="$t('user.telefono')" prop="telefono">
            <el-input v-model="newUser.telefono" />
          </el-form-item>
          <el-form-item v-if="dialogStatus === 'create'" :label="$t('user.password')" prop="password">
            <el-input v-model="newUser.password" show-password />
          </el-form-item>
          <el-form-item v-if="dialogStatus === 'create'" :label="$t('user.confirmPassword')" prop="confirmPassword">
            <el-input v-model="newUser.confirmPassword" show-password />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="createUser()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import UserResource from '@/api/user';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive
import checkPermission from '@/utils/permission'; // Permission checking

const userResource = new UserResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'UserList',
  components: { Pagination },
  directives: { waves, permission },
  data() {
    var validateConfirmPassword = (value, callback) => {
      if (value !== this.newUser.password) {
        callback(new Error('Password is mismatched!'));
      } else {
        callback();
      }
    };
    return {
      dialogStatus: '',
      filterStatus: false,
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      userCreating: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
        status: 'Activo',
      },
      roles: ['super_admin', 'directivo', 'admin', 'socio', 'inquilino'],
      nonAdminRoles: ['socio', 'inquilino', 'admin'],
      newUser: { status: 'Activo' },
      dialogFormVisible: false,
      dialogPermissionVisible: false,
      dialogPermissionLoading: false,
      currentUserId: 0,
      currentUser: {
        nombre: '',
        permissions: [],
        rolePermissions: [],
      },
      rules: {
        role: [{ required: true, message: 'Role is required', trigger: 'change' }],
        nombre: [{ required: true, message: 'Nombre is required', trigger: 'blur' }],
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
        ],
        password: [{ required: true, message: 'Password is required', trigger: 'blur' }],
        confirmPassword: [{ validator: validateConfirmPassword, trigger: 'blur' }],
      },
      permissionProps: {
        children: 'children',
        label: 'name',
        disabled: 'disabled',
      },
      permissions: [],
      menuPermissions: [],
      otherPermissions: [],
    };
  },
  computed: {
    normalizedMenuPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach(permission => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1, // Just a faked ID
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).menu,
      };

      tmp = this.menuPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
      const userPermissions = {
        id: 0, // Faked ID
        name: 'Extra menus',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    normalizedOtherPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach(permission => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1,
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).other,
      };

      tmp = this.otherPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
      const userPermissions = {
        id: 0,
        name: 'Extra permissions',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    userMenuPermissions() {
      return this.classifyPermissions(this.userPermissions).menu;
    },
    userOtherPermissions() {
      return this.classifyPermissions(this.userPermissions).other;
    },
    userPermissions() {
      return this.currentUser.permissions.role.concat(this.currentUser.permissions.user);
    },
  },
  created() {
    this.resetNewUser();
    this.getList();
    if (checkPermission(['manage permission'])) {
      this.getPermissions();
    }
  },
  methods: {
    checkPermission,
    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },

    filterByStatus() {
      this.query.status = this.filterStatus ? 'Inactivo' : null;
      this.getList();
    },
    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await userResource.list(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewUser();
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['userForm'].clearValidate();
      });
    },
    async handleEdit(idUsuario) {
      try {
        const response = await userResource.get(idUsuario);
        if (response && response.data) {
          this.newUser = Object.assign({}, response.data);
          this.dialogTitle = this.$t('table.edit');
          this.dialogStatus = 'update';
          this.$nextTick(() => {
            this.$refs['userForm'].clearValidate();
          });
          this.dialogFormVisible = true;
        } else {
          this.$message.error('Failed to fetch data');
        }
      } catch (error) {
        this.$message.error('An error occurred while fetching data');
      }
    },
    async updateData() {
      this.$refs['userForm'].validate(async(valid) => {
        if (valid) {
          try {
            await userResource.update(this.newUser.idUsuario, this.newUser);
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
    async handleDelete(idUsuario, nombre) {
      this.$confirm('This will permanently delete user ' + nombre + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async() => {
        userResource.destroy(idUsuario).then(() => {
          this.$message({
            type: 'success',
            message: 'Delete completed',
          });
          this.handleFilter();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled',
        });
      });
    },
    async handleEditPermissions(idUsuario) {
      this.currentUserId = idUsuario;
      this.dialogPermissionLoading = true;
      this.dialogPermissionVisible = true;
      const found = this.list.find(user => user.idUsuario === idUsuario);
      if (found) {
        const { data } = await userResource.permissions(idUsuario);
        this.currentUser = {
          idUsuario: found.idUsuario,
          nombre: found.nombre,
          permissions: data,
        };
        this.dialogPermissionLoading = false;
        this.$nextTick(() => {
          this.$refs.menuPermissions.setCheckedKeys(this.permissionKeys(this.userMenuPermissions));
          this.$refs.otherPermissions.setCheckedKeys(this.permissionKeys(this.userOtherPermissions));
        });
      } else {
        this.dialogPermissionLoading = false;
        this.$message.error('User not found');
      }
    },
    createUser() {
      this.$refs['userForm'].validate((valid) => {
        if (valid) {
          this.newUser.roles = [this.newUser.role];
          this.userCreating = true;
          userResource
            .store(this.newUser)
            .then(() => {
              this.$message({
                message: 'New user ' + this.newUser.nombre + '(' + this.newUser.email + ') has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewUser();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.userCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    resetNewUser() {
      this.newUser = {
        nombre: '',
        email: '',
        password: '',
        confirmPassword: '',
        role: 'Socio',
        status: 'Activo',
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['idUsuario', 'user_id', 'name', 'email', 'role'];
        const filterVal = ['index', 'idUsuario', 'name', 'email', 'role'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'user-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]));
    },
    permissionKeys(permissions) {
      return permissions.map(permssion => permssion.id);
    },
    classifyPermissions(permissions) {
      const all = []; const menu = []; const other = [];
      permissions.forEach(permission => {
        const permissionName = permission.name;
        all.push(permission);
        if (permissionName.startsWith('view menu')) {
          menu.push(this.normalizeMenuPermission(permission));
        } else {
          other.push(this.normalizePermission(permission));
        }
      });
      return { all, menu, other };
    },

    normalizeMenuPermission(permission) {
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name.substring(10)), disabled: permission.disabled || false };
    },

    normalizePermission(permission) {
      const disabled = permission.disabled || permission.name === 'manage permission';
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name), disabled: disabled };
    },

    confirmPermission() {
      const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
      const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
      const checkedPermissions = checkedMenu.concat(checkedOther);
      this.dialogPermissionLoading = true;

      userResource.updatePermission(this.currentUserId, { permissions: checkedPermissions }).then(() => {
        this.$message({
          message: 'Permissions has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        });
        this.dialogPermissionLoading = false;
        this.dialogPermissionVisible = false;
      });
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
</style>
