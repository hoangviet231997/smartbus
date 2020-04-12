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

import { Partner } from '../models/partner';
import { PartnerForm } from '../models/partner-form';
import { PartnerAccount } from '../models/partner-account';
import { PartnerAccountForm } from '../models/partner-account-form';


@Injectable()
export class AdminPartnersService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  listPartnersResponse(): Observable<HttpResponse<Partner[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/partners`,
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
        let _body: Partner[] = null;
        _body = _resp.body as Partner[]
        return _resp.clone({body: _body}) as HttpResponse<Partner[]>;
      })
    );
  }

  /**
   */
  listPartners(): Observable<Partner[]> {
    return this.listPartnersResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createPartnerResponse(body?: PartnerForm): Observable<HttpResponse<Partner>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/partners`,
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
        let _body: Partner = null;
        _body = _resp.body as Partner
        return _resp.clone({body: _body}) as HttpResponse<Partner>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createPartner(body?: PartnerForm): Observable<Partner> {
    return this.createPartnerResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updatePartnerResponse(body?: PartnerForm): Observable<HttpResponse<Partner>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/partners`,
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
        let _body: Partner = null;
        _body = _resp.body as Partner
        return _resp.clone({body: _body}) as HttpResponse<Partner>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  updatePartner(body?: PartnerForm): Observable<Partner> {
    return this.updatePartnerResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param partnerId - undefined
   */
  getParnertByIdResponse(partnerId: number): Observable<HttpResponse<Partner>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/partners/${partnerId}`,
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
        let _body: Partner = null;
        _body = _resp.body as Partner
        return _resp.clone({body: _body}) as HttpResponse<Partner>;
      })
    );
  }

  /**
   * @param partnerId - undefined
   */
  getParnertById(partnerId: number): Observable<Partner> {
    return this.getParnertByIdResponse(partnerId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param partnerId - undefined
   */
  deletePartnerResponse(partnerId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/partners/${partnerId}`,
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
   * @param partnerId - undefined
   */
  deletePartner(partnerId: number): Observable<void> {
    return this.deletePartnerResponse(partnerId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  listPartnerAccountsResponse(): Observable<HttpResponse<PartnerAccount[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/partnerAccount`,
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
        let _body: PartnerAccount[] = null;
        _body = _resp.body as PartnerAccount[]
        return _resp.clone({body: _body}) as HttpResponse<PartnerAccount[]>;
      })
    );
  }

  /**
   */
  listPartnerAccounts(): Observable<PartnerAccount[]> {
    return this.listPartnerAccountsResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createPartnerAccountResponse(body?: PartnerAccountForm): Observable<HttpResponse<PartnerAccount>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/partnerAccount`,
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
        let _body: PartnerAccount = null;
        _body = _resp.body as PartnerAccount
        return _resp.clone({body: _body}) as HttpResponse<PartnerAccount>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createPartnerAccount(body?: PartnerAccountForm): Observable<PartnerAccount> {
    return this.createPartnerAccountResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  updatePartnerAccountResponse(body?: PartnerAccountForm): Observable<HttpResponse<PartnerAccount>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/admin/partnerAccount`,
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
        let _body: PartnerAccount = null;
        _body = _resp.body as PartnerAccount
        return _resp.clone({body: _body}) as HttpResponse<PartnerAccount>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  updatePartnerAccount(body?: PartnerAccountForm): Observable<PartnerAccount> {
    return this.updatePartnerAccountResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param partnerAccountId - undefined
   */
  getParnertAccountByIdResponse(partnerAccountId: number): Observable<HttpResponse<PartnerAccount>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/partnerAccount/${partnerAccountId}`,
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
        let _body: PartnerAccount = null;
        _body = _resp.body as PartnerAccount
        return _resp.clone({body: _body}) as HttpResponse<PartnerAccount>;
      })
    );
  }

  /**
   * @param partnerAccountId - undefined
   */
  getParnertAccountById(partnerAccountId: number): Observable<PartnerAccount> {
    return this.getParnertAccountByIdResponse(partnerAccountId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param partnerAccountId - undefined
   */
  deletePartnerAccountResponse(partnerAccountId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/admin/partnerAccount/${partnerAccountId}`,
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
   * @param partnerAccountId - undefined
   */
  deletePartnerAccount(partnerAccountId: number): Observable<void> {
    return this.deletePartnerAccountResponse(partnerAccountId).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminPartnersService {
}
