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

import { Company } from '../models/company';
import { CompanyUpdate } from '../models/company-update';


@Injectable()
export class ManagerCompaniesService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  managerGetCompanyResponse(): Observable<HttpResponse<Company>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/companies`,
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
        let _body: Company = null;
        _body = _resp.body as Company
        return _resp.clone({body: _body}) as HttpResponse<Company>;
      })
    );
  }

  /**
   */
  managerGetCompany(): Observable<Company> {
    return this.managerGetCompanyResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateCompanyResponse(body?: CompanyUpdate): Observable<HttpResponse<Company>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/companies`,
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
        let _body: Company = null;
        _body = _resp.body as Company
        return _resp.clone({body: _body}) as HttpResponse<Company>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdateCompany(body?: CompanyUpdate): Observable<Company> {
    return this.managerUpdateCompanyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  managerGetCompanyByNotArrayResponse(): Observable<HttpResponse<Company>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/companies/arrayNot`,
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
        let _body: Company = null;
        _body = _resp.body as Company
        return _resp.clone({body: _body}) as HttpResponse<Company>;
      })
    );
  }

  /**
   */
  managerGetCompanyByNotArray(): Observable<Company> {
    return this.managerGetCompanyByNotArrayResponse().pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerCompaniesService {
}
