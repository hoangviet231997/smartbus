import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DevicesRoutingModule } from './devices-routing.module';
import { DeviceModelsComponent } from './device-models/device-models.component';
import { DevicesComponent } from './devices/devices.component';
import { ModalModule, PaginationModule } from 'ngx-bootstrap';
import { ApiModule } from '../../../api/api.module';
import { FormsModule } from '@angular/forms';
import { LaddaModule } from 'angular2-ladda';
import { SelectModule } from 'ng2-select';
import { AuthenticationModule } from '../../../authentication/authentication.module';
import { SharedModule } from '../../../shared/shared.module';
import { DeviceFirmwareVersionComponent } from './device-firmware-version/device-firmware-version.component';

@NgModule({
  imports: [
    CommonModule,
    DevicesRoutingModule,
    ModalModule,
    PaginationModule,
    ApiModule,
    FormsModule,
    LaddaModule.forRoot({
      style: 'slide-left',
    }),
    SelectModule,
    AuthenticationModule,
    SharedModule
  ],
  exports: [
    AuthenticationModule
  ],
  declarations: [DeviceModelsComponent, DevicesComponent, DeviceFirmwareVersionComponent]
})
export class DevicesModule { }
