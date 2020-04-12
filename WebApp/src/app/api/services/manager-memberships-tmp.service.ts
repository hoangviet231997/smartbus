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

import { MembershipTmp } from '../models/membership-tmp';
import { membershipTmpInput } from '../models/membership-tmp-input';
import { Membership } from '../models/membership';
import { MembershipForm } from '../models/membership-form';


@Injectable()
export class ManagerMembershipsTmpService extends BaseService {
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
  managerlistMembershipsTmpResponse(params: ManagerMembershipsTmpService.ManagerlistMembershipsTmpParams): Observable<HttpResponse<MembershipTmp[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/membershipsTmp`,
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
        let _body: MembershipTmp[] = null;
        _body = _resp.body as MembershipTmp[]
        return _resp.clone({body: _body}) as HttpResponse<MembershipTmp[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistMembershipsTmp(params: ManagerMembershipsTmpService.ManagerlistMembershipsTmpParams): Observable<MembershipTmp[]> {
    return this.managerlistMembershipsTmpResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param membershipTmpId - undefined
   */
  managerGetMembershipTmpByIdResponse(membershipTmpId: number): Observable<HttpResponse<MembershipTmp>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/membershipsTmp/${membershipTmpId}`,
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
        let _body: MembershipTmp = null;
        _body = _resp.body as MembershipTmp
        return _resp.clone({body: _body}) as HttpResponse<MembershipTmp>;
      })
    );
  }

  /**
   * @param membershipTmpId - undefined
   */
  managerGetMembershipTmpById(membershipTmpId: number): Observable<MembershipTmp> {
    return this.managerGetMembershipTmpByIdResponse(membershipTmpId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param membershipTmpId - undefined
   */
  managerDeleteMembershipTmpResponse(membershipTmpId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/membershipsTmp/${membershipTmpId}`,
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
   * @param membershipTmpId - undefined
   */
  managerDeleteMembershipTmp(membershipTmpId: number): Observable<void> {
    return this.managerDeleteMembershipTmpResponse(membershipTmpId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerListMembershipsTmpByInputAndByTypeSearchResponse(body?: membershipTmpInput): Observable<HttpResponse<MembershipTmp[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/membershipsTmp/search`,
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
        let _body: MembershipTmp[] = null;
        _body = _resp.body as MembershipTmp[]
        return _resp.clone({body: _body}) as HttpResponse<MembershipTmp[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerListMembershipsTmpByInputAndByTypeSearch(body?: membershipTmpInput): Observable<MembershipTmp[]> {
    return this.managerListMembershipsTmpByInputAndByTypeSearchResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerAcceptMembershipsTmpResponse(body?: MembershipForm): Observable<HttpResponse<Membership[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/membershipsTmp/accept`,
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
        let _body: Membership[] = null;
        _body = _resp.body as Membership[]
        return _resp.clone({body: _body}) as HttpResponse<Membership[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerAcceptMembershipsTmp(body?: MembershipForm): Observable<Membership[]> {
    return this.managerAcceptMembershipsTmpResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerMembershipsTmpService {
  export interface ManagerlistMembershipsTmpParams {
    page?: number;
    limit?: number;
  }
}
