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

import { Vehicle } from '../models/vehicle';
import { VehicleForm } from '../models/vehicle-form';
import { VehicleSearch } from '../models/vehicle-search';


@Injectable()
export class ManagerVehiclesService extends BaseService {
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
  managerlistVehiclesResponse(params: ManagerVehiclesService.ManagerlistVehiclesParams): Observable<HttpResponse<Vehicle[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/vehicles`,
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
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistVehicles(params: ManagerVehiclesService.ManagerlistVehiclesParams): Observable<Vehicle[]> {
    return this.managerlistVehiclesResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerCreateVehicleResponse(body?: VehicleForm): Observable<HttpResponse<Vehicle>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/vehicles`,
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
        let _body: Vehicle = null;
        _body = _resp.body as Vehicle
        return _resp.clone({body: _body}) as HttpResponse<Vehicle>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerCreateVehicle(body?: VehicleForm): Observable<Vehicle> {
    return this.managerCreateVehicleResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateVehicleResponse(body?: VehicleForm): Observable<HttpResponse<Vehicle>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/vehicles`,
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
        let _body: Vehicle = null;
        _body = _resp.body as Vehicle
        return _resp.clone({body: _body}) as HttpResponse<Vehicle>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdateVehicle(body?: VehicleForm): Observable<Vehicle> {
    return this.managerUpdateVehicleResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param vehicleId - undefined
   */
  managerGetVehicleByIdResponse(vehicleId: number): Observable<HttpResponse<Vehicle>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/vehicles/${vehicleId}`,
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
        let _body: Vehicle = null;
        _body = _resp.body as Vehicle
        return _resp.clone({body: _body}) as HttpResponse<Vehicle>;
      })
    );
  }

  /**
   * @param vehicleId - undefined
   */
  managerGetVehicleById(vehicleId: number): Observable<Vehicle> {
    return this.managerGetVehicleByIdResponse(vehicleId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param vehicleId - undefined
   */
  managerDeleteVehicleResponse(vehicleId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/vehicles/${vehicleId}`,
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
   * @param vehicleId - undefined
   */
  managerDeleteVehicle(vehicleId: number): Observable<void> {
    return this.managerDeleteVehicleResponse(vehicleId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerVehicleAssignRouteResponse(body?: VehicleForm): Observable<HttpResponse<Vehicle>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/vehicles/assignRoute`,
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
        let _body: Vehicle = null;
        _body = _resp.body as Vehicle
        return _resp.clone({body: _body}) as HttpResponse<Vehicle>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerVehicleAssignRoute(body?: VehicleForm): Observable<Vehicle> {
    return this.managerVehicleAssignRouteResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerVehicleSearchResponse(body?: VehicleSearch): Observable<HttpResponse<Vehicle[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/vehicles/search`,
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
   * @param body - undefined
   */
  managerVehicleSearch(body?: VehicleSearch): Observable<Vehicle[]> {
    return this.managerVehicleSearchResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  getlistVehicleAllResponse(): Observable<HttpResponse<Vehicle[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/vehicles/all`,
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
  getlistVehicleAll(): Observable<Vehicle[]> {
    return this.getlistVehicleAllResponse().pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerVehiclesService {
  export interface ManagerlistVehiclesParams {
    page?: number;
    limit?: number;
  }
}
