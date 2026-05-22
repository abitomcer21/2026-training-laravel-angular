import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: 'login',
    loadComponent: () => import('./pages/login/login.page').then(m => m.LoginPage)
  },
  {
    path: '',
    redirectTo: 'login',
    pathMatch: 'full',
  },
  {
    path: 'dashboard',
    loadComponent: () => import('./pages/dashboard/dashboard.page').then( m => m.DashboardPage)
  },
  {
    path: 'point-of-sale',
    loadComponent: () => import('./pages/punto-venta/punto-venta.page').then(m => m.PuntoVentaPage)
  },
  {
  path: 'point-of-sale',
  loadComponent: () => import('./pages/punto-venta/punto-venta.page').then(m => m.PuntoVentaPage),
  children: [
    {
      path: 'panel-mesas',
      loadComponent: () => import('./features/punto-venta/componentes/mesas/panel-mesas.component').then(m => m.MesasComponent)
    }
  ]
}
];