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

import { GroupKeyCompany } from '../models/group-key-company';
import { GroupKeyCompanyCreate } from '../models/group-key-company-create';
import { GroupKeyCompanyUpdate } from '../models/group-key-company-update';


@Injectable()
export class AdminGroupKeyService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  listGroupKeyCompaniesResponse(): Observable<HttpResponse<GroupKeyCompany[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/groupKey`,
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
        let _body: GroupKeyCompany[] = null;
        _body = _resp.body as GroupKeyCompany[]
        return _resp.clone({body: _body}) as HttpResponse<GroupKeyCompany[]>;
      })
    );
  }

  /**
   */
  listGroupKeyCompanies(): Observable<GroupKeyCompany[]> {
    return this.listGroupKeyCompaniesResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createGroupKeyCompaniesResponse(body?: GroupKeyCompanyCreate): Observable<HttpResponse<GroupKeyCompany>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/groupKey`,
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
        let _body: GroupKeyCompany = null;
        _body = _resp.body as GroupKeyCompany
        return _resp.clone({body: _body}) as HttpResponse<GroupKeyCompany>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createGroupKeyCompanies(body?: GroupKeyCompanyCreate): Observable<GroupKeyCompany> {
    return this.createGroupKeyCompaniesResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updateGroupKeyCompaniesResponse(body?: GroupKeyCompanyUpdate): Observable<HttpResponse<GroupKeyCompany>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/groupKey`,
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
        let _body: GroupKeyCompany = null;
        _body = _resp.body as GroupKeyCompany
        return _resp.clone({body: _body}) as HttpResponse<GroupKeyCompany>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  updateGroupKeyCompanies(body?: GroupKeyCompanyUpdate): Observable<GroupKeyCompany> {
    return this.updateGroupKeyCompaniesResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param groupKeyId - undefined
   */
  getGroupKeyCompaniesByIdResponse(groupKeyId: number): Observable<HttpResponse<GroupKeyCompany>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/groupKey/${groupKeyId}`,
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
        let _body: GroupKeyCompany = null;
        _body = _resp.body as GroupKeyCompany
        return _resp.clone({body: _body}) as HttpResponse<GroupKeyCompany>;
      })
    );
  }

  /**
   * @param groupKeyId - undefined
   */
  getGroupKeyCompaniesById(groupKeyId: number): Observable<GroupKeyCompany> {
    return this.getGroupKeyCompaniesByIdResponse(groupKeyId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param groupKeyId - undefined
   */
  deleteGroupKeyCompaniesByIdResponse(groupKeyId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/groupKey/${groupKeyId}`,
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
   * @param groupKeyId - undefined
   */
  deleteGroupKeyCompaniesById(groupKeyId: number): Observable<void> {
    return this.deleteGroupKeyCompaniesByIdResponse(groupKeyId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param type - undefined
   */
  listGroupKeyCompaniesByTypeForAppResponse(type: number): Observable<HttpResponse<GroupKeyCompany[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/groupKey/company/app/${type}`,
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
        let _body: GroupKeyCompany[] = null;
        _body = _resp.body as GroupKeyCompany[]
        return _resp.clone({body: _body}) as HttpResponse<GroupKeyCompany[]>;
      })
    );
  }

  /**
   * @param type - undefined
   */
  listGroupKeyCompaniesByTypeForApp(type: number): Observable<GroupKeyCompany[]> {
    return this.listGroupKeyCompaniesByTypeForAppResponse(type).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminGroupKeyService {
}
