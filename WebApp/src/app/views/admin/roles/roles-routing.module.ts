import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { ManageRolesComponent } from './manage-roles/manage-roles.component';
import { ManagePermissionsComponent } from './manage-permissions/manage-permissions.component';
import { ManageCategoriesComponent } from './manage-categories/manage-categories.component';
import { ManagePermissionV2Component } from './manage-permission-v2/manage-permission-v2.component';
import { from } from 'rxjs';

const routes: Routes = [
  {
    path: '',
    children: [
      {
        path: 'manage-roles',
        component: ManageRolesComponent
      },
      {
        path: 'manage-permissions',
        component: ManagePermissionsComponent
      },
      {
        path: 'manage-categories',
        component: ManageCategoriesComponent
      },
      {
        path:'manage-permission-v2',
        component: ManagePermissionV2Component
      }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class RolesRoutingModule { }
