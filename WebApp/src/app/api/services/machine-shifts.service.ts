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

import { MachineLoginView } from '../models/machine-login-view';
import { MachineLogin } from '../models/machine-login';
import { GenericResponse } from '../models/generic-response';


@Injectable()
export class MachineShiftsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param body - undefined
   */
  machineLoginResponse(body?: MachineLogin): Observable<HttpResponse<MachineLoginView>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/machine/shifts/login`,
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
        let _body: MachineLoginView = null;
        _body = _resp.body as MachineLoginView
        return _resp.clone({body: _body}) as HttpResponse<MachineLoginView>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  machineLogin(body?: MachineLogin): Observable<MachineLoginView> {
    return this.machineLoginResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  machineLogoutResponse(): Observable<HttpResponse<GenericResponse>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/machine/shifts/logout`,
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
        let _body: GenericResponse = null;
        _body = _resp.body as GenericResponse
        return _resp.clone({body: _body}) as HttpResponse<GenericResponse>;
      })
    );
  }

  /**
   */
  machineLogout(): Observable<GenericResponse> {
    return this.machineLogoutResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  machineUpdateRfidVehicleResponse(body?: MachineLogin): Observable<HttpResponse<MachineLoginView>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/machine/shifts/updateRfidVehicle`,
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
        let _body: MachineLoginView = null;
        _body = _resp.body as MachineLoginView
        return _resp.clone({body: _body}) as HttpResponse<MachineLoginView>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  machineUpdateRfidVehicle(body?: MachineLogin): Observable<MachineLoginView> {
    return this.machineUpdateRfidVehicleResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module MachineShiftsService {
}
