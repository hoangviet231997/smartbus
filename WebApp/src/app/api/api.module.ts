import { NgModule } from '@angular/core';
import { HttpClientModule } from '@angular/common/http';
import { ApiConfiguration } from './api-configuration';

import { AdminActivityLogsService } from './services/admin-activity-logs.service';
import { AdminCategoriesService } from './services/admin-categories.service';
import { AdminCompaniesService } from './services/admin-companies.service';
import { AdminDevicesService } from './services/admin-devices.service';
import { AdminGeneralSettingService } from './services/admin-general-setting.service';
import { AdminGroupKeyService } from './services/admin-group-key.service';
import { AdminModuleAppsService } from './services/admin-module-apps.service';
import { AdminNotifiesService } from './services/admin-notifies.service';
import { AdminPartnersService } from './services/admin-partners.service';
import { AdminPermissionsService } from './services/admin-permissions.service';
import { AdminPermissionsV2Service } from './services/admin-permissions-v2.service';
import { AdminCardsService } from './services/admin-cards.service';
import { AdminRolesService } from './services/admin-roles.service';
import { AdminUsersService } from './services/admin-users.service';
import { AuthService } from './services/auth.service';
import { GetMembershipsService } from './services/get-memberships.service';
import { MembershipsAppService } from './services/memberships-app.service';
import { TopupService } from './services/topup.service';
import { AppTicketsService } from './services/app-tickets.service';
import { ApplicatonGetService } from './services/applicaton-get.service';
import { ApplicatonUpdateService } from './services/applicaton-update.service';
import { MachineGetService } from './services/machine-get.service';
import { MachinePrepaidcardsService } from './services/machine-prepaidcards.service';
import { MachineShiftsService } from './services/machine-shifts.service';
import { MachineTicketAllocatesService } from './services/machine-ticket-allocates.service';
import { MachineUpdateService } from './services/machine-update.service';
import { ManagerAppNotifyService } from './services/manager-app-notify.service';
import { ManagerAppsService } from './services/manager-apps.service';
import { ManagerBusStationsService } from './services/manager-bus-stations.service';
import { ManagerCateNewsService } from './services/manager-cate-news.service';
import { ManagerCompaniesService } from './services/manager-companies.service';
import { ManagerDashboardsService } from './services/manager-dashboards.service';
import { ManagerDenominationsService } from './services/manager-denominations.service';
import { ManagerDevicesService } from './services/manager-devices.service';
import { ManagerHistoryShiftsService } from './services/manager-history-shifts.service';
import { ManagerLayoutService } from './services/manager-layout.service';
import { ManagerMembershiptypesService } from './services/manager-membershiptypes.service';
import { ManagerMembershipsService } from './services/manager-memberships.service';
import { ManagerMembershipsTmpService } from './services/manager-memberships-tmp.service';
import { ManagerModuleCompanyService } from './services/manager-module-company.service';
import { ManagerNewsService } from './services/manager-news.service';
import { ManagerNotifiesService } from './services/manager-notifies.service';
import { ManagerPrepaidcardsService } from './services/manager-prepaidcards.service';
import { ManagerReportsService } from './services/manager-reports.service';
import { ManagerRfidcardService } from './services/manager-rfidcard.service';
import { ManagerRolesService } from './services/manager-roles.service';
import { ManagerRoutesService } from './services/manager-routes.service';
import { ManagerSettingGlobalService } from './services/manager-setting-global.service';
import { ManagerShiftsService } from './services/manager-shifts.service';
import { ManagerSubscriptionTypesService } from './services/manager-subscription-types.service';
import { ManagerTicketTypesService } from './services/manager-ticket-types.service';
import { ManagerUsersService } from './services/manager-users.service';
import { ManagerVehiclesService } from './services/manager-vehicles.service';
import { MobilePrepaidcardsService } from './services/mobile-prepaidcards.service';

/**
 * Module that provides instances for all API services
 */
@NgModule({
  imports: [
    HttpClientModule
  ],
  exports: [
    HttpClientModule
  ],
  declarations: [],
  providers: [
    ApiConfiguration,
   AdminActivityLogsService,
   AdminCategoriesService,
   AdminCompaniesService,
   AdminDevicesService,
   AdminGeneralSettingService,
   AdminGroupKeyService,
   AdminModuleAppsService,
   AdminNotifiesService,
   AdminPartnersService,
   AdminPermissionsService,
   AdminPermissionsV2Service,
   AdminCardsService,
   AdminRolesService,
   AdminUsersService,
   AuthService,
   GetMembershipsService,
   MembershipsAppService,
   TopupService,
   AppTicketsService,
   ApplicatonGetService,
   ApplicatonUpdateService,
   MachineGetService,
   MachinePrepaidcardsService,
   MachineShiftsService,
   MachineTicketAllocatesService,
   MachineUpdateService,
   ManagerAppNotifyService,
   ManagerAppsService,
   ManagerBusStationsService,
   ManagerCateNewsService,
   ManagerCompaniesService,
   ManagerDashboardsService,
   ManagerDenominationsService,
   ManagerDevicesService,
   ManagerHistoryShiftsService,
   ManagerLayoutService,
   ManagerMembershiptypesService,
   ManagerMembershipsService,
   ManagerMembershipsTmpService,
   ManagerModuleCompanyService,
   ManagerNewsService,
   ManagerNotifiesService,
   ManagerPrepaidcardsService,
   ManagerReportsService,
   ManagerRfidcardService,
   ManagerRolesService,
   ManagerRoutesService,
   ManagerSettingGlobalService,
   ManagerShiftsService,
   ManagerSubscriptionTypesService,
   ManagerTicketTypesService,
   ManagerUsersService,
   ManagerVehiclesService,
   MobilePrepaidcardsService
  ],
})
export class ApiModule { }
