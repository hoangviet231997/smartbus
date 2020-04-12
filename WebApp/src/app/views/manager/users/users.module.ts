import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { UsersRoutingModule } from './users-routing.module';
import { UsersListComponent } from './users-list/users-list.component';
import { UserFormComponent } from './user-form/user-form.component';

import { ModalModule, PaginationModule } from 'ngx-bootstrap';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { SelectModule } from 'ng2-select';
import { BsDatepickerModule } from 'ngx-bootstrap';
import { SharedModule } from '../../../shared/shared.module';
import { FilterdataPipe } from './users-list/users-list.component';

@NgModule({
  imports: [
    CommonModule,
    UsersRoutingModule,
    ModalModule,
    PaginationModule.forRoot(),
    FormsModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    SelectModule,
    BsDatepickerModule.forRoot(),
    SharedModule
  ],
  declarations: [UsersListComponent, UserFormComponent, FilterdataPipe]
})
export class UsersModule { }
