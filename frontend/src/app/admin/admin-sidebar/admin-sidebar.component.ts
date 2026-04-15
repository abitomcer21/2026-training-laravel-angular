import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';

@Component({
  selector: 'app-admin-sidebar',
  standalone: true,
  templateUrl: './admin-sidebar.component.html',
  styleUrl: './admin-sidebar.component.css'
})
export class AdminSidebarComponent {
  navItems = [
    {
      path: '/admin/families', label: 'Familias',
      svg: `<svg viewBox="0 0 24 24" stroke-width="1.8" fill="none" stroke="currentColor"><path d="M3 6h18M3 12h18M3 18h18"/><circle cx="7" cy="6" r="1.5"/><circle cx="7" cy="12" r="1.5"/><circle cx="7" cy="18" r="1.5"/></svg>`
    },
    {
      path: '/admin/products', label: 'Productos',
      svg: `<svg viewBox="0 0 24 24" stroke-width="1.8" fill="none" stroke="currentColor"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`
    },
    {
      path: '/admin/users', label: 'Usuarios',
      svg: `<svg viewBox="0 0 24 24" stroke-width="1.8" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`
    },
    {
      path: '/admin/zones', label: 'Zonas',
      svg: `<svg viewBox="0 0 24 24" stroke-width="1.8" fill="none" stroke="currentColor"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>`
    },
    {
      path: '/admin/tables', label: 'Mesas',
      svg: `<svg viewBox="0 0 24 24" stroke-width="1.8" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`
    },
  ];
}