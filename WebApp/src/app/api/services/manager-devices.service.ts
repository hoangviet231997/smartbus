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

import { ManagerDevice } from '../models/manager-device';
import { RevenueChart } from '../models/revenue-chart';


@Injectable()
export class ManagerDevicesService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerListDevicesResponse(params: ManagerDevicesService.ManagerListDevicesParams): Observable<HttpResponse<ManagerDevice[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/devices`,
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
        let _body: ManagerDevice[] = null;
        _body = _resp.body as ManagerDevice[]
        return _resp.clone({body: _body}) as HttpResponse<ManagerDevice[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerListDevices(params: ManagerDevicesService.ManagerListDevicesParams): Observable<ManagerDevice[]> {
    return this.managerListDevicesResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param isRunning - undefined
   */
  managerListDevicesByIsRunningResponse(isRunning: number): Observable<HttpResponse<ManagerDevice[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/devices/isrunning/${isRunning}`,
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
        let _body: ManagerDevice[] = null;
        _body = _resp.body as ManagerDevice[]
        return _resp.clone({body: _body}) as HttpResponse<ManagerDevice[]>;
      })
    );
  }

  /**
   * @param isRunning - undefined
   */
  managerListDevicesByIsRunning(isRunning: number): Observable<ManagerDevice[]> {
    return this.managerListDevicesByIsRunningResponse(isRunning).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param deviceId - undefined
   */
  managerGetDeviceByIdResponse(deviceId: number): Observable<HttpResponse<ManagerDevice>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/devices/${deviceId}`,
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
        let _body: ManagerDevice = null;
        _body = _resp.body as ManagerDevice
        return _resp.clone({body: _body}) as HttpResponse<ManagerDevice>;
      })
    );
  }

  /**
   * @param deviceId - undefined
   */
  managerGetDeviceById(deviceId: number): Observable<ManagerDevice> {
    return this.managerGetDeviceByIdResponse(deviceId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param typeOpt - undefined
   * @param tranId - undefined
   * @param shiftId - undefined
   */
  managerDeviceGetRevenueByShiftIdResponse(params: ManagerDevicesService.ManagerDeviceGetRevenueByShiftIdParams): Observable<HttpResponse<RevenueChart>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.typeOpt != null) __params = __params.set("typeOpt", params.typeOpt.toString());
    if (params.tranId != null) __params = __params.set("tranId", params.tranId.toString());
    if (params.shiftId != null) __params = __params.set("shiftId", params.shiftId.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/devices/revenue`,
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
        let _body: RevenueChart = null;
        _body = _resp.body as RevenueChart
        return _resp.clone({body: _body}) as HttpResponse<RevenueChart>;
      })
    );
  }

  /**
   * @param typeOpt - undefined
   * @param tranId - undefined
   * @param shiftId - undefined
   */
  managerDeviceGetRevenueByShiftId(params: ManagerDevicesService.ManagerDeviceGetRevenueByShiftIdParams): Observable<RevenueChart> {
    return this.managerDeviceGetRevenueByShiftIdResponse(params).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerDevicesService {
  export interface ManagerListDevicesParams {
    page?: number;
    limit?: number;
  }
  export interface ManagerDeviceGetRevenueByShiftIdParams {
    typeOpt?: number;
    tranId?: number;
    shiftId?: number;
  }
}
