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

import { Denomination } from '../models/denomination';


@Injectable()
export class ManagerDenominationsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param typeStr - undefined
   */
  managerListDenominationResponse(typeStr: string): Observable<HttpResponse<Denomination[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/denominations/denomination/${typeStr}`,
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
        let _body: Denomination[] = null;
        _body = _resp.body as Denomination[]
        return _resp.clone({body: _body}) as HttpResponse<Denomination[]>;
      })
    );
  }

  /**
   * @param typeStr - undefined
   */
  managerListDenomination(typeStr: string): Observable<Denomination[]> {
    return this.managerListDenominationResponse(typeStr).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - Created Denomination  Object
   */
  managerCreateDenominationResponse(body?: Denomination): Observable<HttpResponse<Denomination>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/denominations/denomination`,
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
        let _body: Denomination = null;
        _body = _resp.body as Denomination
        return _resp.clone({body: _body}) as HttpResponse<Denomination>;
      })
    );
  }

  /**
   * @param body - Created Denomination  Object
   */
  managerCreateDenomination(body?: Denomination): Observable<Denomination> {
    return this.managerCreateDenominationResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param denominationId - undefined
   */
  managerDeleteDenominationByIdResponse(denominationId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/denominations/denominationBy/${denominationId}`,
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
   * @param denominationId - undefined
   */
  managerDeleteDenominationById(denominationId: number): Observable<void> {
    return this.managerDeleteDenominationByIdResponse(denominationId).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerDenominationsService {
}
