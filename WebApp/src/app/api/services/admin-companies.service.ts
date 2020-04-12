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
import { CompanyCreate } from '../models/company-create';
import { CompanyUpdate } from '../models/company-update';
import { UploadFile } from '../models/upload-file';
import { CompanyInput } from '../models/company-input';


@Injectable()
export class AdminCompaniesService extends BaseService {
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
  listCompaniesResponse(params: AdminCompaniesService.ListCompaniesParams): Observable<HttpResponse<Company[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/companies`,
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
        let _body: Company[] = null;
        _body = _resp.body as Company[]
        return _resp.clone({body: _body}) as HttpResponse<Company[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  listCompanies(params: AdminCompaniesService.ListCompaniesParams): Observable<Company[]> {
    return this.listCompaniesResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createCompanyResponse(body?: CompanyCreate): Observable<HttpResponse<Company>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/companies`,
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
  createCompany(body?: CompanyCreate): Observable<Company> {
    return this.createCompanyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updateCompanyResponse(body?: CompanyUpdate): Observable<HttpResponse<Company>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/companies`,
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
  updateCompany(body?: CompanyUpdate): Observable<Company> {
    return this.updateCompanyResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param companyId - undefined
   */
  getCompanyByIdResponse(companyId: number): Observable<HttpResponse<Company>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/companies/${companyId}`,
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
   * @param companyId - undefined
   */
  getCompanyById(companyId: number): Observable<Company> {
    return this.getCompanyByIdResponse(companyId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param companyId - undefined
   */
  deleteCompanyResponse(companyId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/companies/${companyId}`,
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
   * @param companyId - undefined
   */
  deleteCompany(companyId: number): Observable<void> {
    return this.deleteCompanyResponse(companyId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  uploadFileResponse(body?: UploadFile): Observable<HttpResponse<Company>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/companies/upload`,
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
  uploadFile(body?: UploadFile): Observable<Company> {
    return this.uploadFileResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerListCompanyByInputAndByTypeSearchResponse(body?: CompanyInput): Observable<HttpResponse<Company[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/companies/search`,
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
        let _body: Company[] = null;
        _body = _resp.body as Company[]
        return _resp.clone({body: _body}) as HttpResponse<Company[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerListCompanyByInputAndByTypeSearch(body?: CompanyInput): Observable<Company[]> {
    return this.managerListCompanyByInputAndByTypeSearchResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminCompaniesService {
  export interface ListCompaniesParams {
    page?: number;
    limit?: number;
  }
}
