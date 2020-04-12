/* tslint:disable */
import { Injectable } from '@angular/core';
import {
  HttpClient, HttpRequest, HttpResponse, 
  HttpHeaders, HttpParams } from '@angular/common/http';
import { BaseService } from '../base-service';
import { ApiConfiguration } from '../api-configuration';
import { Observable } from 'rxjs/Observable';
import { map } from 'rxjs/operators/map';
import { filter } from 'rxjs/operators/filter';

import { LoginView } from '../models/login-view';
import { Login } from '../models/login';
import { Logout } from '../models/logout';


@Injectable()
export class AuthService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param body - The username/password
   */
  loginResponse(body: Login): Observable<HttpResponse<LoginView>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/auth/login`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'json'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: LoginView = null;
        _body = _resp.body as LoginView
        return _resp.clone({body: _body}) as HttpResponse<LoginView>;
      })
    );
  }

  /**
   * @param body - The username/password
   */
  login(body: Login): Observable<LoginView> {
    return this.loginResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - The userid/token
   */
  logoutResponse(body: Logout): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/auth/logout`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'text'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: void = null;
        
        return _resp.clone({body: _body}) as HttpResponse<void>;
      })
    );
  }

  /**
   * @param body - The userid/token
   */
  logout(body: Logout): Observable<void> {
    return this.logoutResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param companyId - undefined
   */
  loginAsCompanyResponse(companyId: number): Observable<HttpResponse<LoginView>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/auth/loginAs/${companyId}`,
      __body,
      {
        headers: __headers,
        params: __params,
        responseType: 'json'
      });

    return this.http.request<any>(req).pipe(
      filter(_r => _r instanceof HttpResponse),
      map(_r => {
        let _resp = _r as HttpResponse<any>;
        let _body: LoginView = null;
        _body = _resp.body as LoginView
        return _resp.clone({body: _body}) as HttpResponse<LoginView>;
      })
    );
  }

  /**
   * @param companyId - undefined
   */
  loginAsCompany(companyId: number): Observable<LoginView> {
    return this.loginAsCompanyResponse(companyId).pipe(
      map(_r => _r.body)
    );
  }}

export module AuthService {
}
