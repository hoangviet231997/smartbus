import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';
import { AuthLayoutComponent } from './layouts/auth-layout/auth-layout.component';
import { AdminLayoutComponent } from './layouts/admin-layout/admin-layout.component';
import { ManagerLayoutComponent } from './layouts/manager-layout/manager-layout.component';
import { SharedModule } from './shared/shared.module';
import { ModalModule, PaginationModule } from 'ngx-bootstrap';
import { TimepickerModule } from 'ngx-bootstrap';
import { ApiModule } from './api/api.module';
import { FormsModule } from '@angular/forms';
import { AuthenticationModule } from './authentication/authentication.module';
import { TranslateModule, TranslateLoader } from '@ngx-translate/core';
import { HttpClient } from '@angular/common/http';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { AgmCoreModule } from '@agm/core';
import { ChartModule } from 'angular-highcharts';
import { NgxSpinnerModule } from 'ngx-spinner';
export function createTranslateLoader(http: HttpClient) {
  return new TranslateHttpLoader(http, './assets/i18n/', '.json');
}

@NgModule({
  declarations: [
    AppComponent,
    AuthLayoutComponent,
    AdminLayoutComponent,
    ManagerLayoutComponent,
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    SharedModule,
    ApiModule,
    ModalModule.forRoot(),
    PaginationModule.forRoot(),
    TimepickerModule.forRoot(),
    AgmCoreModule.forRoot({
      apiKey: 'AIzaSyCcL1uP1e6wUohluzq3Y818bCT5dbRYINU'
    }),
    FormsModule,
    AuthenticationModule,
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: (createTranslateLoader),
        deps: [HttpClient]
      }
    }),
    NgxSpinnerModule,
    ChartModule
  ],
  exports: [
    AuthenticationModule,
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
