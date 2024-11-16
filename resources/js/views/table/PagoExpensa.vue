<template>
  <div class="payment-steps">
    <h2>Realizar el pago de una Expensa</h2>

    <!-- Selección de Usuario -->
    <div class="user-selection">
      <h3 class="selection-title">Seleccionar Usuario</h3>
      <el-select v-model="selectedUser" placeholder="Selecciona un usuario" class="user-select">
        <el-option v-for="user in users" :key="user.id" :label="user.nombre" :value="user.idUsuario" />
      </el-select>
    </div>

    <!-- Selección de Propiedad -->
    <div v-if="selectedUser" class="property-selection">
      <h3 class="selection-title">Seleccionar Propiedad</h3>
      <el-select v-model="selectedProperty" placeholder="Selecciona una propiedad" class="properties-select"
                 @change="cargarPropiedades"
      >
        <el-option v-for="propiedad in properties" :key="propiedad.idPropiedad" :label="propiedad.nombre"
                   :value="propiedad.idPropiedad"
        />
      </el-select>
    </div>

    <el-table v-if="mostrarTabla && propiedades.length > 0" :data="formattedProperties" style="width: 100%">
      <el-table-column v-for="(mes, index) in meses" :key="index" :label="mes" width="220">
        <template slot-scope="scope">
          <div v-if="scope.row[index + 1]">
            <div :class="getClass(scope.row[index + 1])">
              <!-- Monto a pagar -->
              <p>Monto: {{ scope.row[index + 1].monto }}</p>
              <!-- Campo para ingresar el monto (dinámico) -->
              <p>Seleccionar Monto:
                <el-input
                  v-model="scope.row[index + 1].montoSeleccionado"
                  :disabled="scope.row[index + 1].montoPendiente === 0 || scope.row[index + 1].montoPagado === scope.row[index + 1].monto"
                  :max="scope.row[index + 1].montoPendiente"
                  size="small"
                  @input="validateMonto(scope.row[index + 1])"
                />
              </p>
            </div>
          </div>
          <div v-else>
            <p>No disponible</p>
          </div>
        </template>
      </el-table-column>
    </el-table>
    <div v-else-if="mostrarTabla">
      <p>No tiene expensas</p>
    </div>
  </div>
</template>

<script>
import UserResource from '@/api/user';
import { getProperties } from '@/api/expensas';
// import DetallePagoResource from '@/api/detallePago';
import waves from '@/directive/waves'; // Import the waves directive

// const detallePagoResource = new DetallePagoResource();
const userResource = new UserResource();

export default {
  directives: {
    waves, // Register the waves directive
  },
  data() {
    return {
      meses: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      selectedProperty: null,
      currentStep: 0,
      selectedUser: null, // ID del usuario seleccionado
      mostrarTabla: false,
      users: [], // Lista de usuarios
      propiedades: [],
      properties: [], // Asumiendo que estás llenando esta lista con tus propiedades
      expenses: [], // Expensas de la propiedad seleccionada
      selectedPaymentMethod: null,
      totalAmount: 0,
      paymentMethods: [], // Métodos de pago disponibles
    };
  },
  computed: {
    formattedProperties() {
      const formatted = [];
      this.propiedades.forEach(propiedad => {
        const mes = new Date(propiedad.mes_gestion).getMonth() + 1;
        if (!formatted[propiedad.idPropiedad]) {
          formatted[propiedad.idPropiedad] = {};
        }
        formatted[propiedad.idPropiedad][mes] = propiedad;
      });
      return Object.values(formatted);
    },
  },
  watch: {
    selectedUser(newUser) {
      this.properties = []; // Limpia las propiedades cuando se selecciona un nuevo usuario
      this.selectedProperty = null; // Reinicia la selección de propiedad
      this.fetchUserProperties(); // Llama a la función para obtener las propiedades del nuevo usuario
      this.mostrarTabla = false;
    },
  },
  async created() {
    await this.getUsuarios();
  },
  methods: {
    // Clase para los estilos
    getClass(row) {
      const monto = parseFloat(row.monto);
      const montoPagado = parseFloat(row.montoPagado);

      if (montoPagado === monto) {
        return 'green';
      } else if (montoPagado > 0 && montoPagado < monto) {
        return 'yellow';
      } else {
        return 'red';
      }
    },

    // Validar que el monto seleccionado no exceda el monto pendiente
    validateMonto(row) {
      const montoSeleccionado = parseFloat(row.montoSeleccionado) || 0;
      if (montoSeleccionado > row.montoPendiente) {
        row.montoSeleccionado = row.montoPendiente;
      } else if (montoSeleccionado < 0) {
        row.montoSeleccionado = 0;
      }
    },
    async getUsuarios() {
      try {
        const response = await userResource.list();
        this.users = response.data;
      } catch (error) {
        console.error('Error al obtener usuarios:', error);
      }
    },
    async fetchUserProperties() {
      if (this.selectedUser) {
        try {
          const response = await userResource.list({ idUsuario: this.selectedUser });
          const users = response.data && response.data.data ? response.data.data : response.data;
          const user = users.find(user => user.idUsuario === this.selectedUser);
          this.properties = user ? user.propiedades : [];
        } catch (error) {
          console.error('Error al obtener propiedades del usuario:', error);
        }
      }
    },
    async cargarPropiedades() {
      try {
        this.propiedades = []; // Limpia las propiedades antes de cargar nuevas
        this.mostrarTabla = false; // Oculta la tabla mientras se cargan los datos
        console.log('ID de propiedad que se envía:', this.selectedProperty);
        const response = await getProperties(this.selectedProperty);
        console.log('Respuesta del servidor:', response);
        if (response.data && response.data.length > 0) {
          const filteredData = response.data.filter(expensa => expensa.status !== 'Borrado');
          this.$set(this, 'propiedades', filteredData);
        } else {
          console.warn('No se encontraron propiedades para el ID proporcionado');
          this.$set(this, 'propiedades', []);
        }
        this.mostrarTabla = true;
      } catch (error) {
        console.error('Error al cargar propiedades:', error);
        this.$set(this, 'propiedades', []);
        this.mostrarTabla = true;
      }
    },
  },
};
</script>

<style scoped>
.user-selection,
.property-selection,
.view-button-container {
  margin-top: 20px;
  padding: 10px;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.selection-title {
  font-size: 1.2em;
  font-weight: bold;
  margin-bottom: 10px;
}

.user-select,
.properties-select {
  width: 100%;
}

.view-button-container {
  display: flex;
  justify-content: center;
}

.view-button {
  width: 100%;
  max-width: 200px;
}

.payment-steps {
  padding: 20px;
}

.step-content {
  margin-top: 20px;
}

.user-properties {
  margin-top: 20px;
  padding: 10px;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.properties-title {
  font-size: 1.2em;
  font-weight: bold;
  margin-bottom: 10px;
}

.properties-select {
  width: 100%;
}
.green {
  background-color: #d4edda;
  color: #155724;
}

.yellow {
  background-color: #fff3cd;
  color: #856404;
}

.red {
  background-color: #f8d7da;
  color: #721c24;
}
</style>
