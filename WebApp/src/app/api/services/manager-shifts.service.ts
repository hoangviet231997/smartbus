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

import { Shift } from '../models/shift';
import { Collected } from '../models/collected';


@Injectable()
export class ManagerShiftsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param shiftId - undefined
   */
  managerGetShiftsResponse(shiftId: number): Observable<HttpResponse<Shift>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/shifts/${shiftId}`,
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
        let _body: Shift = null;
        _body = _resp.body as Shift
        return _resp.clone({body: _body}) as HttpResponse<Shift>;
      })
    );
  }

  /**
   * @param shiftId - undefined
   */
  managerGetShifts(shiftId: number): Observable<Shift> {
    return this.managerGetShiftsResponse(shiftId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerShiftsUpdateCollectedResponse(body?: Collected): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/shifts/collected`,
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
        let _body: string = null;
        _body = _resp.body as string
        return _resp.clone({body: _body}) as HttpResponse<string>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerShiftsUpdateCollected(body?: Collected): Observable<string> {
    return this.managerShiftsUpdateCollectedResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerShiftsService {
}
