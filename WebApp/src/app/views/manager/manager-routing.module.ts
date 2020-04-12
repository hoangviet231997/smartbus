import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { VehiclesComponent } from './vehicles/vehicles.component';
import { DashboardComponent } from './dashboard/dashboard.component';
import { ProfilesComponent } from './profiles/profiles.component';
import { ApplicationsComponent } from './applications/applications.component';
import { StaticComponent } from './static/static.component';
import { ModuleAppsComponent } from './module-apps/module-apps.component';
import { SettingGlobalComponent } from './setting-global/setting-global.component';
import { DictatesTransportsComponent } from './dictates-transports/dictates-transports.component';
import { SubDashboardComponent } from './sub-dashboard/sub-dashboard.component';
import { DevicesComponent } from './devices/devices.component';
import { DeviceStatusComponent } from './device-status/device-status.component';
import { DeviceLocationsComponent } from './device-locations/device-locations.component';
import { from } from 'rxjs';

const routes: Routes = [
  {
    path: '',
    children: [
      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full',
      },
      {
        path: 'dashboard',
        component: DashboardComponent
      },
      {
        path: 'sub-dashboard',
        component: SubDashboardComponent
      },
      {
        path: 'static',
        component: StaticComponent
      },
      {
        path: 'profile',
        component: ProfilesComponent
      },
      {
        path: 'devices',
        component: DevicesComponent
      },
      {
        path: 'device-status',
        component: DeviceStatusComponent
      },
      {
        path: 'device-locations',
        component: DeviceLocationsComponent
      },
      {
        path: 'reports',
        children: [
          {
            path: '',
            loadChildren: './reports/reports.module#ReportsModule'
          }
        ]
      },
      {
        path: 'routes',
        children: [
          {
            path: '',
            loadChildren: './routes/routes.module#RoutesModule'
          }
        ]
      },
      {
        path: 'tickets',
        children: [
          {
            path: '',
            loadChildren: './tickets/tickets.module#TicketsModule'
          }
        ]
      },
      {
        path: 'users',
        children: [
          {
            path: '',
            loadChildren: './users/users.module#UsersModule'
          }
        ]
      },
      {
        path: 'vehicles',
        component: VehiclesComponent
      },
      {
        path: 'applications',
        component: ApplicationsComponent
      },
      {
        path: 'module-apps',
        component: ModuleAppsComponent
      },
      {
        path: 'setting-global',
        component: SettingGlobalComponent
      },
      {
        path: 'dictates-transports',
        component: DictatesTransportsComponent
      },
      {
        path: 'memberships',
        children: [
          {
            path: '',
            loadChildren: './memberships/memberships.module#MembershipsModule'
          }
        ]
      },
      {
        path: 'news',
        children: [
          {
            path: '',
            loadChildren: './news/news.module#NewsModule'
          }
        ]
      },
      {
        path: "notifies",
        children: [
          {
            path: '',
            loadChildren: './notifies/notifies.module#NotifiesModule'
          }
        ]
      }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ManagerRoutingModule { }
