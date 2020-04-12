import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes, PreloadAllModules } from '@angular/router';
import { AuthLayoutComponent } from './layouts/auth-layout/auth-layout.component';
import { AdminLayoutComponent } from './layouts/admin-layout/admin-layout.component';
import { ManagerLayoutComponent } from './layouts/manager-layout/manager-layout.component';
import { AuthGuard } from './guards/auth.guard';
import { RoleAdminGuard } from './guards/role-admin.guard';
import { RoleManagerGuard } from './guards/role-manager.guard';

const routes: Routes = [
  {
    path: '',
    redirectTo: '/auth/signin',
    pathMatch: 'full'
  },
  /**
   * AUTHENTICATION LAYOUT
   */
  {
    path: '',
    component: AuthLayoutComponent,
    children: [
      {
        path: 'auth',
        loadChildren: './authentication/authentication.module#AuthenticationModule'
      },
      //  {
      //   path: 'error',
      //   loadChildren: './error/error.module#ErrorModule'
      // }
    ]
  },
  /**
   * ADMIN LAYOUT
   */
  {
    path: '',
    component: AdminLayoutComponent,
    canActivate: [AuthGuard, RoleAdminGuard],
    children: [
      {
        path: 'admin',
        loadChildren: './views/admin/admin.module#AdminModule'
      }
    ]
  },
  /**
   * MANAGER LAYOUT
   */
  {
    path: '',
    component: ManagerLayoutComponent,
    canActivate: [AuthGuard, RoleManagerGuard],
    children: [
      {
        path: 'manager',
        loadChildren: './views/manager/manager.module#ManagerModule'
      }
    ]
  },
  { // 404
    path: '**',
    redirectTo: 'error/404'
  }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { useHash: true, preloadingStrategy: PreloadAllModules})],
  exports: [ RouterModule ]
})
export class AppRoutingModule { }
