import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AuthenticationRoutingModule } from './authentication-routing.module';
import { SigninComponent } from './signin/signin.component';
import { ForgotComponent } from './forgot/forgot.component';
import { FormsModule } from '@angular/forms';
import { AuthenticationInterceptor } from './authentication-interceptor';
import { HTTP_INTERCEPTORS } from '@angular/common/http';
import { SignoutComponent } from './signout/signout.component';
import { SharedModule } from '../shared/shared.module';
import { LaddaModule } from 'angular2-ladda';

@NgModule({
  imports: [
    CommonModule,
    AuthenticationRoutingModule,
    FormsModule,
    SharedModule,
    LaddaModule
  ],
  providers: [
    AuthenticationInterceptor,
    { provide: HTTP_INTERCEPTORS, useClass: AuthenticationInterceptor, multi: true }
  ],
  declarations: [SigninComponent, ForgotComponent, SignoutComponent]
})
export class AuthenticationModule { }
