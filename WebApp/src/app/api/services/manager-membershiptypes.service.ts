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

import { MembershipType } from '../models/membership-type';
import { MembershipTypeForm } from '../models/membership-type-form';


@Injectable()
export class ManagerMembershiptypesService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  managerListMembershipTypesResponse(): Observable<HttpResponse<MembershipType[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/membershiptype`,
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
        let _body: MembershipType[] = null;
        _body = _resp.body as MembershipType[]
        return _resp.clone({body: _body}) as HttpResponse<MembershipType[]>;
      })
    );
  }

  /**
   */
  managerListMembershipTypes(): Observable<MembershipType[]> {
    return this.managerListMembershipTypesResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  manmagerCreateMembershipTypeResponse(body?: MembershipTypeForm): Observable<HttpResponse<MembershipType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/membershiptype`,
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
        let _body: MembershipType = null;
        _body = _resp.body as MembershipType
        return _resp.clone({body: _body}) as HttpResponse<MembershipType>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  manmagerCreateMembershipType(body?: MembershipTypeForm): Observable<MembershipType> {
    return this.manmagerCreateMembershipTypeResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateMembershipTypeResponse(body?: MembershipTypeForm): Observable<HttpResponse<MembershipType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/membershiptype`,
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
        let _body: MembershipType = null;
        _body = _resp.body as MembershipType
        return _resp.clone({body: _body}) as HttpResponse<MembershipType>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdateMembershipType(body?: MembershipTypeForm): Observable<MembershipType> {
    return this.managerUpdateMembershipTypeResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param membershiptypeId - undefined
   */
  managerGetMembershipTypeByIdResponse(membershiptypeId: number): Observable<HttpResponse<MembershipType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/membershiptype/${membershiptypeId}`,
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
        let _body: MembershipType = null;
        _body = _resp.body as MembershipType
        return _resp.clone({body: _body}) as HttpResponse<MembershipType>;
      })
    );
  }

  /**
   * @param membershiptypeId - undefined
   */
  managerGetMembershipTypeById(membershiptypeId: number): Observable<MembershipType> {
    return this.managerGetMembershipTypeByIdResponse(membershiptypeId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param membershiptypeId - undefined
   */
  managerDeleteMembershipTypeResponse(membershiptypeId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/membershiptype/${membershiptypeId}`,
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
   * @param membershiptypeId - undefined
   */
  managerDeleteMembershipType(membershiptypeId: number): Observable<void> {
    return this.managerDeleteMembershipTypeResponse(membershiptypeId).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerMembershiptypesService {
}
