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

import { Membership } from '../models/membership';


@Injectable()
export class GetMembershipsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param qrcode - undefined
   */
  getMembershipByMomoResponse(qrcode: string): Observable<HttpResponse<Membership>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/membership/momo/${qrcode}`,
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
        let _body: Membership = null;
        _body = _resp.body as Membership
        return _resp.clone({body: _body}) as HttpResponse<Membership>;
      })
    );
  }

  /**
   * @param qrcode - undefined
   */
  getMembershipByMomo(qrcode: string): Observable<Membership> {
    return this.getMembershipByMomoResponse(qrcode).pipe(
      map(_r => _r.body)
    );
  }}

export module GetMembershipsService {
}
