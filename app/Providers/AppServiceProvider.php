<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\App\Services\PushLogsService::class, function ($app) {
            return new \App\Services\PushLogsService(
                $app->make('\App\Services\IssuedsService')
            );
        });

        $this->app->singleton(\App\Services\MembershipsService::class, function ($app) {
            return new \App\Services\MembershipsService(
                $app->make('\App\Services\RfidCardsService'),
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\TransactionsServiceVersion2'),
                $app->make('\App\Services\UsersService'),
                $app->make('\App\Services\MembershipTypeService'),
                $app->make('\App\Services\PublicFunctionService'),
                $app->make('\App\Services\PartnersService'),
                $app->make('\App\Services\BusStationsService'),
                $app->make('\App\Services\TicketPricesService'),
                $app->make('\App\Services\TicketTypesService')
            );
        });

        $this->app->singleton(\App\Services\MembershipTypeService::class, function ($app) {
            return new \App\Services\MembershipTypeService(
                $app->make('\App\Services\PushLogsService')
            );
        });

        $this->app->singleton(\App\Services\MembershipsSubscriptionService::class, function ($app) {
            return new \App\Services\MembershipsSubscriptionService(
                $app->make('\App\Services\SubscriptionsService')
            );
        });

        $this->app->singleton(\App\Services\SubscriptionsService::class, function ($app) {
            return new \App\Services\SubscriptionsService();
        });

        $this->app->singleton(\App\Services\PrepaidCardsService::class, function ($app) {
            return new \App\Services\PrepaidCardsService(
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\RfidCardsService')
            );
        });

        $this->app->singleton(\App\Services\TransactionsService::class, function ($app) {
            return new \App\Services\TransactionsService(
                $app->make('\App\Services\DevicesService'),
                $app->make('\App\Services\ShiftsService'),
                $app->make('\App\Services\TicketPricesService'),
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\UsersService'),
                $app->make('\App\Services\TicketAllocatesService'),
                $app->make('\App\Services\MembershipsService')
            );
        });

        $this->app->singleton(\App\Services\UsersService::class, function ($app) {
            return new \App\Services\UsersService(
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\RfidCardsService'),
                $app->make('\App\Services\RolesService')
            );
        });

        $this->app->singleton(\App\Services\RfidCardsService::class, function ($app) {
            return new \App\Services\RfidCardsService(
                $app->make('\App\Services\PushLogsService')
            );
        });

        $this->app->singleton(\App\Services\RfidCardsTestServices::class, function ($app) {
            return new \App\Services\RfidCardsTestServices(
                $app->make('\App\Services\UsersService'),
                $app->make('\App\Services\VehiclesService'),
                $app->make('\App\Services\MembershipsService'),
                $app->make('\App\Services\PrepaidCardsService'),
                $app->make('\App\Services\CompaniesService'),
                $app->make('\App\Services\SubscriptionsService')
            );
        });

        $this->app->singleton(\App\Services\IssuedsService::class, function ($app) {
            return new \App\Services\IssuedsService();
        });

        $this->app->singleton(\App\Services\VehiclesService::class, function ($app) {
            return new \App\Services\VehiclesService(
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\RfidCardsService'),
                $app->make('\App\Services\RoutesService'),
                $app->make('\App\Services\UsersService'),
                $app->make('\App\Services\BusStationsService')
            );
        });

        $this->app->singleton(\App\Services\AttachmentsService::class, function ($app) {
            return new \App\Services\AttachmentsService();
        });

        $this->app->singleton(\App\Services\RolesService::class, function ($app) {
            return new \App\Services\RolesService(
                $app->make('\App\Services\PermissionsService'),
                $app->make('\App\Services\PermissionRolesService')
            );
        });

        $this->app->singleton(\App\Services\PermissionsService::class, function ($app) {
            return new \App\Services\PermissionsService();
        });

        $this->app->singleton(\App\Services\PermissionRolesService::class, function ($app) {
            return new \App\Services\PermissionRolesService();
        });

        $this->app->singleton(\App\Services\GpsService::class, function ($app) {
            return new \App\Services\GpsService(
                $app->make('\App\Services\AttachmentsService'),
                $app->make('\App\Services\VehiclesService')
            );
        });

        $this->app->singleton(\App\Services\FirmwaresService::class, function ($app) {
            return new \App\Services\FirmwaresService(
                $app->make('\App\Services\DevicesService')
            );
        });

        $this->app->singleton(\App\Services\ShiftsService::class, function ($app) {
            return new \App\Services\ShiftsService(
                $app->make('\App\Services\UsersService'),
                $app->make('\App\Services\DevicesService'),
                $app->make('\App\Services\IssuedsService'),
                $app->make('\App\Services\VehiclesService'),
                $app->make('\App\Services\AttachmentsService'),
                $app->make('\App\Services\RfidCardsService'),
                $app->make('\App\Services\HistoryShiftService'),
                $app->make('\App\Services\RoutesService'),
                $app->make('\App\Services\CompaniesService'),
                $app->make('\App\Services\PublicFunctionService')
            );
        });

        $this->app->singleton(\App\Services\UpdatesService::class, function ($app) {
            return new \App\Services\UpdatesService(
                $app->make('\App\Services\ShiftsService'),
                $app->make('\App\Services\TransactionsService'),
                $app->make('\App\Services\PrepaidCardsService'),
                $app->make('\App\Services\UsersService'),
                $app->make('\App\Services\VehiclesService'),
                $app->make('\App\Services\TicketTypesService'),
                $app->make('\App\Services\TicketPricesService'),
                $app->make('\App\Services\MembershipsService'),
                $app->make('\App\Services\CompaniesService'),
                $app->make('\App\Services\PublicFunctionService')
            );
        });

        $this->app->singleton(\App\Services\TicketAllocatesService::class, function ($app) {
            return new \App\Services\TicketAllocatesService(
                $app->make('\App\Services\IssuedsService'),
                $app->make('\App\Services\TicketPricesService'),
                $app->make('\App\Services\TicketTypesService')
            );
        });

        $this->app->singleton(\App\Services\TicketDestroysService::class, function ($app) {
            return new \App\Services\TicketDestroysService(
                $app->make('\App\Services\ShiftsService'),
                $app->make('\App\Services\TransactionsService'),
                $app->make('\App\Services\TicketPricesService'),
                $app->make('\App\Services\UsersService'),
                $app->make('\App\Services\MembershipsService')
            );
        });

        $this->app->singleton(\App\Services\CompaniesService::class, function ($app) {
            return new \App\Services\CompaniesService(
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\UsersService'),
                $app->make('\App\Services\GroupKeyCompaniesService')
            );
        });

        $this->app->singleton(\App\Services\DevicesModelService::class, function ($app) {
            return new \App\Services\DevicesModelService();
        });

        $this->app->singleton(\App\Services\DevicesService::class, function ($app) {
            return new \App\Services\DevicesService(
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\DevicesModelService'),
                $app->make('\App\Services\IssuedsService'),
                $app->make('\App\Services\CompaniesService'),
                $app->make('\App\Services\UsersService'),
                $app->make('\App\Services\VehiclesService'),
                $app->make('\App\Services\BusStationsService')

            );
        });

        $this->app->singleton(\App\Services\RoutesService::class, function ($app) {
            return new \App\Services\RoutesService(
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\BusStationsService')
            );
        });

        $this->app->singleton(\App\Services\BusStationsService::class, function ($app) {
            return new \App\Services\BusStationsService(
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\PartnersService'),
                $app->make('\App\Services\PublicFunctionService'),
                $app->make('\App\Services\TicketPricesService')
            );
        });

        $this->app->singleton(\App\Services\TicketTypesService::class, function ($app) {
            return new \App\Services\TicketTypesService(
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\TicketPricesService')
            );
        });

        $this->app->singleton(\App\Services\TicketPricesService::class, function ($app) {
            return new \App\Services\TicketPricesService(
                $app->make('\App\Services\PushLogsService')
            );
        });

        $this->app->singleton(\App\Services\ReportsService::class, function ($app) {
            return new \App\Services\ReportsService(
                $app->make('\App\Services\UsersService'),
                $app->make('\App\Services\ShiftsService'),
                $app->make('\App\Services\RoutesService'),
                $app->make('\App\Services\VehiclesService'),
                $app->make('\App\Services\TransactionsService'),
                $app->make('\App\Services\TicketTypesService'),
                $app->make('\App\Services\TicketAllocatesService'),
                $app->make('\App\Services\TicketPricesService'),
                $app->make('\App\Services\CompaniesService'),
                $app->make('\App\Services\RolesService'),
                $app->make('\App\Services\BusStationsService'),
                $app->make('\App\Services\RfidCardsService'),
                $app->make('\App\Services\MembershipsService'),
                $app->make('\App\Services\ModuleCompanyService'),
                $app->make('\App\Services\HistoryShiftService'),
                $app->make('\App\Services\MembershipTypeService'),
                $app->make('\App\Services\DevicesService')
            );
        });

        $this->app->singleton(\App\Services\DashboardsService::class, function ($app) {
            return new \App\Services\DashboardsService(
                $app->make('\App\Services\CompaniesService'),
                $app->make('\App\Services\VehiclesService')
            );
        });

        $this->app->singleton(\App\Services\AppsService::class, function ($app) {
            return new \App\Services\AppsService(
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\TransactionsService')
            );
        });

        $this->app->singleton(\App\Services\HistoryShiftService::class, function ($app) {
            return new \App\Services\HistoryShiftService(
                $app->make('\App\Services\TicketPricesService'),
                $app->make('\App\Services\UsersService')
            );
        });

        $this->app->singleton(\App\Services\ModuleAppService::class, function ($app) {
            return new \App\Services\ModuleAppService(
                $app->make('\App\Services\ModuleCompanyService')
            );
        });

        $this->app->singleton(\App\Services\ModuleCompanyService::class, function ($app) {
            return new \App\Services\ModuleCompanyService(
                $app->make('\App\Services\PushLogsService'),
                $app->make('\App\Services\RoutesService')
            );
        });

        $this->app->singleton(\App\Services\TopupsService::class, function ($app) {
            return new \App\Services\TopupsService(
                $app->make('\App\Services\PartnersService'),
                $app->make('\App\Services\MembershipsService'),
                $app->make('\App\Services\TransactionsService'),
                $app->make('\App\Services\PublicFunctionService')
            );
        });

        $this->app->singleton(\App\Services\SettingGlobalsService::class, function ($app) {
            return new \App\Services\SettingGlobalsService(
                $app->make('\App\Services\PushLogsService')
            );
        });

        $this->app->singleton(\App\Services\GroupKeyCompaniesService::class, function ($app) {
            return new \App\Services\GroupKeyCompaniesService(
                $app->make('\App\Services\PushLogsService')
            );
        });

        $this->app->singleton(\App\Services\ShiftDestroysService::class, function ($app) {
            return new \App\Services\ShiftDestroysService(
                $app->make('\App\Services\ShiftsService'),
                $app->make('\App\Services\TransactionsService'),
                $app->make('\App\Services\UsersService')
            );
        });

        $this->app->singleton(\App\Services\DenominationsService::class, function ($app) {
            return new \App\Services\DenominationsService(
                $app->make('\App\Services\PushLogsService')
            );
        });

        $this->app->singleton(\App\Services\NotifyService::class, function ($app) {
            return new \App\Services\NotifyService(
                $app->make('\App\Services\PublicFunctionService'),
                $app->make('\App\Services\RfidCardsService'),
                $app->make('\App\Services\MembershipsService')
            );
        });

        $this->app->singleton(\App\Services\MembershipsTmpService::class, function ($app) {
            return new \App\Services\MembershipsTmpService(
                $app->make('\App\Services\RfidCardsService'),
                $app->make('\App\Services\PublicFunctionService')
            );
        });
        $this->app->singleton(\App\Services\PartnersService::class, function ($app) {
            return new \App\Services\PartnersService(
                $app->make('\App\Services\PublicFunctionService'),
                $app->make('\App\Services\PushLogsService')
            );
        });
    }
}
