import Layout from '@/layout';

const tableRoutes = {
  path: '/table',
  component: Layout,
  redirect: '/table/complex-table',
  name: 'table',
  meta: {
    title: 'Registros de gastos',
    icon: 'table',
    permissions: ['view menu table'],
  },
  children: [
    {
      path: 'expenses',
      component: () => import('@/views/table/Expensas'),
      name: 'Expensas',
      meta: { title: 'Expensas', icon: 'el-icon-money' },
    },
    
    {
      path: 'caja-chica',
      component: () => import('@/views/table/Caja-chica'),
      name: 'Caja chica',
      meta: { title: 'Caja chica', icon: 'el-icon-wallet' },
    },
    {
      path: 'egresos',
      component: () => import('@/views/table/Egresos'),
      name: 'Egresos',
      meta: { title: 'Egresos', icon: 'el-icon-minus' },
    },
    
  ],
};
export default tableRoutes;
