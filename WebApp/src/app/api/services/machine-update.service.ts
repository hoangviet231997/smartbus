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

import { GenericResponse } from '../models/generic-response';
import { MachineActivity } from '../models/machine-activity';
import { GPSRecord } from '../models/gpsrecord';
import { UpdateAction } from '../models/update-action';
import { Firmware } from '../models/firmware';
import { Ping } from '../models/ping';
import { ManagerDevice } from '../models/manager-device';
import { MachineDeviceStatus } from '../models/machine-device-status';
import { TotalBill } from '../models/total-bill';


@Injectable()
export class MachineUpdateService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param body - undefined
   */
  machineUpdateActivitiesResponse(body?: MachineActivity[]): Observable<HttpResponse<GenericResponse>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/machine/update/activities`,
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
   * @param body - undefined
   */
  machineUpdateActivities(body?: MachineActivity[]): Observable<GenericResponse> {
    return this.machineUpdateActivitiesResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  machineUpdatePostionResponse(body?: GPSRecord[]): Observable<HttpResponse<GenericResponse>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/machine/update/postion`,
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
   * @param body - undefined
   */
  machineUpdatePostion(body?: GPSRecord[]): Observable<GenericResponse> {
    return this.machineUpdatePostionResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param from - undefined
   */
  machineUpdateDatabaseResponse(from?: number): Observable<HttpResponse<UpdateAction[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (from != null) __params = __params.set("from", from.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/machine/update/database`,
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
        let _body: UpdateAction[] = null;
        _body = _resp.body as UpdateAction[]
        return _resp.clone({body: _body}) as HttpResponse<UpdateAction[]>;
      })
    );
  }

  /**
   * @param from - undefined
   */
  machineUpdateDatabase(from?: number): Observable<UpdateAction[]> {
    return this.machineUpdateDatabaseResponse(from).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param version - undefined
   */
  machineUpdateFirmwareResponse(version?: string): Observable<HttpResponse<Firmware>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (version != null) __params = __params.set("version", version.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/machine/update/firmware`,
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
        let _body: Firmware = null;
        _body = _resp.body as Firmware
        return _resp.clone({body: _body}) as HttpResponse<Firmware>;
      })
    );
  }

  /**
   * @param version - undefined
   */
  machineUpdateFirmware(version?: string): Observable<Firmware> {
    return this.machineUpdateFirmwareResponse(version).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  machineUpdatePingResponse(): Observable<HttpResponse<Ping>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/machine/update/ping`,
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
        let _body: Ping = null;
        _body = _resp.body as Ping
        return _resp.clone({body: _body}) as HttpResponse<Ping>;
      })
    );
  }

  /**
   */
  machineUpdatePing(): Observable<Ping> {
    return this.machineUpdatePingResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  machineUpdateDeviceStatusResponse(body?: MachineDeviceStatus[]): Observable<HttpResponse<ManagerDevice>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/machine/update/deviceStatus`,
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
   * @param body - undefined
   */
  machineUpdateDeviceStatus(body?: MachineDeviceStatus[]): Observable<ManagerDevice> {
    return this.machineUpdateDeviceStatusResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param from - undefined
   */
  machineUpdateGetTotalBillsByDeviceResponse(from?: number): Observable<HttpResponse<TotalBill[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (from != null) __params = __params.set("from", from.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/machine/update/get/shifts`,
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
        let _body: TotalBill[] = null;
        _body = _resp.body as TotalBill[]
        return _resp.clone({body: _body}) as HttpResponse<TotalBill[]>;
      })
    );
  }

  /**
   * @param from - undefined
   */
  machineUpdateGetTotalBillsByDevice(from?: number): Observable<TotalBill[]> {
    return this.machineUpdateGetTotalBillsByDeviceResponse(from).pipe(
      map(_r => _r.body)
    );
  }}

export module MachineUpdateService {
}
