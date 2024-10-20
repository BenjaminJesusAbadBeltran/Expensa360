<template>
  <div v-loading.fullscreen.lock="fullscreenLoading" class="main-article"
    element-loading-text="Reporte Generador por VueJS">
    <div class="article__heading">
      <div class="article__heading__title">
        {{ article.title }}
      </div>
    </div>
    <div ref="content" class="node-article-content" v-html="article.content" />
  </div>
</template>

<script>
export default {
  data() {
    return {
      article: '',
      fullscreenLoading: false,
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      const query = this.$route.query;
      // Aquí deberías hacer la llamada a tu API para generar el reporte con los datos de query
      // Por simplicidad, vamos a usar datos estáticos
      const data = {
        title: 'Reporte Generado',
        content: `<table class="payment-report">
          <thead>
            <tr>
              <th colspan="2">Reporte de ${query.table}</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Atributos</td>
              <td>${query.attributes.join(', ')}</td>
            </tr>
            <tr>
              <td>Fecha Desde</td>
              <td>${query.dateFrom}</td>
            </tr>
            <tr>
              <td>Fecha Hasta</td>
              <td>${query.dateTo}</td>
            </tr>
          </tbody>
        </table>`
      };
      this.article = data;
      document.title = data.title;
      setTimeout(() => {
        this.fullscreenLoading = false;
        this.$nextTick(() => {
          this.addPDFMargins();
          window.print();
        });
      }, 3000);
    },
    addPDFMargins() {
      const content = this.$refs.content;
      // Add a class to the content to apply margins for printing
      content.classList.add('pdf-margins');
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss">
/* Estilos existentes */
</style>