import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AdminRoutingModule } from './admin-routing.module';
import { DashboardComponent } from './dashboard/dashboard.component';
import { ConfigurationComponent } from './configuration/configuration.component';
import { CompaniesComponent } from './companies/companies.component';
import { UsersComponent } from './users/users.component';
import { GroupCompaniesComponent } from './group-companies/group-companies.component';
import { ModalModule, PaginationModule } from 'ngx-bootstrap';
import { TimepickerModule } from 'ngx-bootstrap';
import { BsDatepickerModule } from 'ngx-bootstrap';
import { AgmCoreModule } from '@agm/core';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { SelectModule } from 'ng2-select';
import { AuthenticationModule } from '../../authentication/authentication.module';
import { SharedModule } from '../../shared/shared.module';
import { ModuleAppsComponent } from './module-apps/module-apps.component';
import { DecodeOnlinesComponent } from './decode-onlines/decode-onlines.component';
import { ActivityLogsComponent } from './activity-logs/activity-logs.component';
import { NotifyTypesComponent } from './notify-types/notify-types.component';

@NgModule({
  imports: [
    CommonModule,
    AdminRoutingModule,
    ModalModule,
    PaginationModule.forRoot(),
    TimepickerModule,
    BsDatepickerModule.forRoot(),
    FormsModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    SelectModule,
    AuthenticationModule,
    SharedModule,
    AgmCoreModule
  ],
  exports: [
    AuthenticationModule,
  ],
  declarations: [DashboardComponent, ConfigurationComponent, CompaniesComponent, UsersComponent, ModuleAppsComponent, DecodeOnlinesComponent, GroupCompaniesComponent, ActivityLogsComponent, NotifyTypesComponent]
})
export class AdminModule { }
