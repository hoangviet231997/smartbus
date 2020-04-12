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

import { BusStation } from '../models/bus-station';
import { GroupBusStation } from '../models/group-bus-station';
import { GroupBusStationForm } from '../models/group-bus-station-form';


@Injectable()
export class ManagerBusStationsService extends BaseService {
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
  managerlistBusStationsResponse(params: ManagerBusStationsService.ManagerlistBusStationsParams): Observable<HttpResponse<BusStation[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/busStations`,
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
        let _body: BusStation[] = null;
        _body = _resp.body as BusStation[]
        return _resp.clone({body: _body}) as HttpResponse<BusStation[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistBusStations(params: ManagerBusStationsService.ManagerlistBusStationsParams): Observable<BusStation[]> {
    return this.managerlistBusStationsResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistGroupBusStationResponse(params: ManagerBusStationsService.ManagerlistGroupBusStationParams): Observable<HttpResponse<GroupBusStation[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/busStations/group`,
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
        let _body: GroupBusStation[] = null;
        _body = _resp.body as GroupBusStation[]
        return _resp.clone({body: _body}) as HttpResponse<GroupBusStation[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistGroupBusStation(params: ManagerBusStationsService.ManagerlistGroupBusStationParams): Observable<GroupBusStation[]> {
    return this.managerlistGroupBusStationResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  manmagerCreateGroupBusStationResponse(body?: GroupBusStationForm): Observable<HttpResponse<GroupBusStation>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/busStations/group`,
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
        let _body: GroupBusStation = null;
        _body = _resp.body as GroupBusStation
        return _resp.clone({body: _body}) as HttpResponse<GroupBusStation>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  manmagerCreateGroupBusStation(body?: GroupBusStationForm): Observable<GroupBusStation> {
    return this.manmagerCreateGroupBusStationResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  manmagerUpdateGroupBusStationResponse(body?: GroupBusStationForm): Observable<HttpResponse<GroupBusStation>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/busStations/group`,
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
        let _body: GroupBusStation = null;
        _body = _resp.body as GroupBusStation
        return _resp.clone({body: _body}) as HttpResponse<GroupBusStation>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  manmagerUpdateGroupBusStation(body?: GroupBusStationForm): Observable<GroupBusStation> {
    return this.manmagerUpdateGroupBusStationResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param groupBusStationId - undefined
   */
  managerGetGroupBusStationByIdResponse(groupBusStationId: number): Observable<HttpResponse<GroupBusStation>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/busStations/group/${groupBusStationId}`,
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
        let _body: GroupBusStation = null;
        _body = _resp.body as GroupBusStation
        return _resp.clone({body: _body}) as HttpResponse<GroupBusStation>;
      })
    );
  }

  /**
   * @param groupBusStationId - undefined
   */
  managerGetGroupBusStationById(groupBusStationId: number): Observable<GroupBusStation> {
    return this.managerGetGroupBusStationByIdResponse(groupBusStationId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param groupBusStationId - undefined
   */
  managerDeleteGroupBusStationByIdResponse(groupBusStationId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/busStations/group/${groupBusStationId}`,
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
   * @param groupBusStationId - undefined
   */
  managerDeleteGroupBusStationById(groupBusStationId: number): Observable<void> {
    return this.managerDeleteGroupBusStationByIdResponse(groupBusStationId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerSearchGroupBusStationResponse(body?: GroupBusStationForm): Observable<HttpResponse<GroupBusStation>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/busStations/group/search`,
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
        let _body: GroupBusStation = null;
        _body = _resp.body as GroupBusStation
        return _resp.clone({body: _body}) as HttpResponse<GroupBusStation>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerSearchGroupBusStation(body?: GroupBusStationForm): Observable<GroupBusStation> {
    return this.managerSearchGroupBusStationResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerBusStationsService {
  export interface ManagerlistBusStationsParams {
    page?: number;
    limit?: number;
  }
  export interface ManagerlistGroupBusStationParams {
    page?: number;
    limit?: number;
  }
}
