import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { RolesRoutingModule } from './roles-routing.module';
import { ManagePermissionsComponent } from './manage-permissions/manage-permissions.component';
import { ManageRolesComponent, FilterPipe } from './manage-roles/manage-roles.component';
import { ModalModule, PaginationModule } from 'ngx-bootstrap';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { SharedModule } from '../../../shared/shared.module';
import { SelectModule } from 'ng2-select';
import { ManageCategoriesComponent } from './manage-categories/manage-categories.component';
import { ManagePermissionV2Component } from './manage-permission-v2/manage-permission-v2.component';

@NgModule({
  imports: [
    CommonModule,
    RolesRoutingModule,
    ModalModule,
    PaginationModule.forRoot(),
    FormsModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    SharedModule,
    SelectModule
  ],
  declarations: [ManagePermissionsComponent, ManageRolesComponent, FilterPipe, ManageCategoriesComponent, ManagePermissionV2Component]
})
export class RolesModule { }
