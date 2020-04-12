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
import { FormLoginMembership } from '../models/form-login-membership';


@Injectable()
export class MembershipsAppService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param body - undefined
   */
  loginMembershipForAppResponse(body?: FormLoginMembership): Observable<HttpResponse<Membership>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/membership/app`,
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
   * @param body - undefined
   */
  loginMembershipForApp(body?: FormLoginMembership): Observable<Membership> {
    return this.loginMembershipForAppResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module MembershipsAppService {
}
