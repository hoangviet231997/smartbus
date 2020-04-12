import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { ConfigurationComponent } from './configuration/configuration.component';
import { DashboardComponent } from './dashboard/dashboard.component';
import { ModuleAppsComponent } from './module-apps/module-apps.component';
import { CompaniesComponent } from './companies/companies.component';
import { UsersComponent } from './users/users.component';
import { DecodeOnlinesComponent } from './decode-onlines/decode-onlines.component';
import { GroupCompaniesComponent } from './group-companies/group-companies.component';
import { ActivityLogsComponent } from './activity-logs/activity-logs.component';
import { NotifyTypesComponent } from './notify-types/notify-types.component';

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
        path: 'configuration',
        component: ConfigurationComponent
      },
      {
        path: 'decode-online',
        component: DecodeOnlinesComponent
      },
      {
        path: 'dashboard',
        component: DashboardComponent
      },
      {
        path: 'devices',
        children: [
          {
            path: '',
            loadChildren: './devices/devices.module#DevicesModule'
          }
        ]
      },
      {
        path: 'module-apps',
        component: ModuleAppsComponent
      },
      {
        path: 'companies',
        component: CompaniesComponent
      },
      {
        path: 'roles',
        children: [
          {
            path: '',
            loadChildren: './roles/roles.module#RolesModule'
          }
        ]
      },
      {
        path: 'cards',
        children: [
          {
            path: '',
            loadChildren: './cards/cards.module#CardsModule'
          }
        ]
      },
      {
        path: 'partners',
        children: [
          {
            path: '',
            loadChildren: './partners/partners.module#PartnersModule'
          }
        ]
      },
      {
        path: 'users',
        component: UsersComponent
      },
      {
        path: 'group-companies',
        component: GroupCompaniesComponent
      },
      {
        path: 'activity-logs',
        component: ActivityLogsComponent
      },
      {
        path: 'notify-types',
        component: NotifyTypesComponent
      },
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminRoutingModule { }
