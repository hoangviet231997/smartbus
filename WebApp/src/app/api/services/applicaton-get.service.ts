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



@Injectable()
export class ApplicatonGetService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param from - undefined
   */
  getListCompaniesResponse(from?: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (from != null) __params = __params.set("from", from.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/application/get/companies`,
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
   * @param from - undefined
   */
  getListCompanies(from?: number): Observable<void> {
    return this.getListCompaniesResponse(from).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param from - undefined
   */
  getListRoutesResponse(from?: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (from != null) __params = __params.set("from", from.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/application/get/routes`,
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
   * @param from - undefined
   */
  getListRoutes(from?: number): Observable<void> {
    return this.getListRoutesResponse(from).pipe(
      map(_r => _r.body)
    );
  }}

export module ApplicatonGetService {
}
