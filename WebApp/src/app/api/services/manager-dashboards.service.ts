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

import { Dashboard } from '../models/dashboard';
import { Vehicle } from '../models/vehicle';
import { DashboardDetail } from '../models/dashboard-detail';
import { dbRouteBusStation } from '../models/db-route-bus-station';


@Injectable()
export class ManagerDashboardsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  managerDashboardGetDataResponse(): Observable<HttpResponse<Dashboard>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/dashboards`,
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
        let _body: Dashboard = null;
        _body = _resp.body as Dashboard
        return _resp.clone({body: _body}) as HttpResponse<Dashboard>;
      })
    );
  }

  /**
   */
  managerDashboardGetData(): Observable<Dashboard> {
    return this.managerDashboardGetDataResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  managerDashboardGetVehiclesResponse(): Observable<HttpResponse<Vehicle[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/dashboards/vehicles`,
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
        let _body: Vehicle[] = null;
        _body = _resp.body as Vehicle[]
        return _resp.clone({body: _body}) as HttpResponse<Vehicle[]>;
      })
    );
  }

  /**
   */
  managerDashboardGetVehicles(): Observable<Vehicle[]> {
    return this.managerDashboardGetVehiclesResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param vehiclesId - undefined
   */
  getVehiclesByIdResponse(vehiclesId: number): Observable<HttpResponse<DashboardDetail>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/dashboards/vehicles/${vehiclesId}`,
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
        let _body: DashboardDetail = null;
        _body = _resp.body as DashboardDetail
        return _resp.clone({body: _body}) as HttpResponse<DashboardDetail>;
      })
    );
  }

  /**
   * @param vehiclesId - undefined
   */
  getVehiclesById(vehiclesId: number): Observable<DashboardDetail> {
    return this.getVehiclesByIdResponse(vehiclesId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  managerDashboardGetRouteBusStationsResponse(): Observable<HttpResponse<dbRouteBusStation>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/dashboards/busStations`,
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
        let _body: dbRouteBusStation = null;
        _body = _resp.body as dbRouteBusStation
        return _resp.clone({body: _body}) as HttpResponse<dbRouteBusStation>;
      })
    );
  }

  /**
   */
  managerDashboardGetRouteBusStations(): Observable<dbRouteBusStation> {
    return this.managerDashboardGetRouteBusStationsResponse().pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerDashboardsService {
}
