import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { RoutesRoutingModule } from './routes-routing.module';
import { RoutesListComponent } from './routes-list/routes-list.component';
import { RoutesFormComponent } from './routes-form/routes-form.component';

import { ModalModule, PaginationModule, BsDatepickerModule, TimepickerModule } from 'ngx-bootstrap';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { SelectModule } from 'ng2-select';
import { SharedModule } from '../../../shared/shared.module';
import { AgmCoreModule } from '@agm/core';
import { GroupBusStationsComponent } from './group-bus-stations/group-bus-stations.component';
import { ColorPickerModule } from 'ngx-color-picker';

@NgModule({
  imports: [
    CommonModule,
    RoutesRoutingModule,
    ModalModule,
    TimepickerModule.forRoot(),
    PaginationModule.forRoot(),
    FormsModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    SelectModule,
    BsDatepickerModule.forRoot(),
    SharedModule,
    AgmCoreModule.forRoot({
      apiKey: 'AIzaSyAvBRaNvfUTrqh7pO6Iiv_7svGETNXBr1c'
    }),
    ColorPickerModule
  ],
  declarations: [RoutesListComponent, RoutesFormComponent, GroupBusStationsComponent]
})
export class RoutesModule { }
