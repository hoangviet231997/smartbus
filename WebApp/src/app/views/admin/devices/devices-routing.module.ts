import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { DeviceModelsComponent } from './device-models/device-models.component';
import { DevicesComponent } from './devices/devices.component';
import { DeviceFirmwareVersionComponent } from './device-firmware-version/device-firmware-version.component'
import { SharedModule } from '../../../shared/shared.module';

const routes: Routes = [
  {
    path: '',
    children: [
      {
        path: 'models',
        component: DeviceModelsComponent
      },
      {
        path: 'devices',
        component: DevicesComponent
      },
      {
        path: 'firmware-version',
        component: DeviceFirmwareVersionComponent
      }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class DevicesRoutingModule { }
