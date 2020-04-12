import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { setTheme } from 'ngx-bootstrap/utils';
setTheme('bs4');

import { ManagerRoutingModule } from './manager-routing.module';
import { DashboardComponent } from './dashboard/dashboard.component';
import { VehiclesComponent } from './vehicles/vehicles.component';
import { AuthenticationModule } from '../../authentication/authentication.module';
import { ProfilesComponent } from './profiles/profiles.component';
import { ApplicationsComponent } from './applications/applications.component';
import { ModuleAppsComponent } from './module-apps/module-apps.component';
import { SettingGlobalComponent } from './setting-global/setting-global.component';
import { DictatesTransportsComponent, FilterUserPipe } from './dictates-transports/dictates-transports.component';
import { SubDashboardComponent } from './sub-dashboard/sub-dashboard.component';
import { StaticComponent } from './static/static.component';
import { DeviceChartComponent } from './device-chart/device-chart.component';
import { DevicesComponent } from './devices/devices.component';
import { DeviceStatusComponent } from './device-status/device-status.component';
import { DeviceLocationsComponent } from './device-locations/device-locations.component';

import { ModalModule, PaginationModule } from 'ngx-bootstrap';
import { TimepickerModule } from 'ngx-bootstrap';
import { BsDatepickerModule, BsDatepickerConfig } from 'ngx-bootstrap';
import { AgmCoreModule } from '@agm/core';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { QRCodeModule } from 'angularx-qrcode';
import { SelectModule } from 'ng2-select';
import { SharedModule } from '../../shared/shared.module';
import { ChartModule } from 'angular-highcharts';
import { NgxSpinnerModule } from 'ngx-spinner';
import { TabsModule } from 'ngx-bootstrap';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

@NgModule({
  imports: [
    CommonModule,
    ManagerRoutingModule,
    AuthenticationModule,
    ModalModule,
    PaginationModule.forRoot(),
    TimepickerModule,
    BsDatepickerModule.forRoot(),
    FormsModule,
    TabsModule.forRoot(),
    NgbModule.forRoot(),
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    SelectModule,
    QRCodeModule,
    SharedModule,
    AgmCoreModule,
    ChartModule,
    NgxSpinnerModule
  ],

  declarations: [
    DashboardComponent,
    VehiclesComponent,
    ProfilesComponent,
    ApplicationsComponent,
    StaticComponent,
    ModuleAppsComponent,
    SettingGlobalComponent,
    DictatesTransportsComponent,
    FilterUserPipe,
    SubDashboardComponent,
    DeviceChartComponent,
    DevicesComponent,
    DeviceStatusComponent,
    DeviceLocationsComponent
  ]
})
export class ManagerModule { }
