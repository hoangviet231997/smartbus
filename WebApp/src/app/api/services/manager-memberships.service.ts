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
import { MembershipForm } from '../models/membership-form';
import { MembershipDetail } from '../models/membership-detail';
import { MembershipDetailSearch } from '../models/membership-detail-search';
import { MembershipInput } from '../models/membership-input';
import { MembershipSwapForm } from '../models/membership-swap-form';
import { MembershipTransaction } from '../models/membership-transaction';
import { MembershipFormApp } from '../models/membership-form-app';
import { MembershipTmp } from '../models/membership-tmp';
import { MembershipTmpFormApp } from '../models/membership-tmp-form-app';


@Injectable()
export class ManagerMembershipsService extends BaseService {
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
  managerlistMembershipsResponse(params: ManagerMembershipsService.ManagerlistMembershipsParams): Observable<HttpResponse<Membership[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/memberships`,
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
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistMemberships(params: ManagerMembershipsService.ManagerlistMembershipsParams): Observable<Membership[]> {
    return this.managerlistMembershipsResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  manmagerCreateMembershipResponse(body?: MembershipForm): Observable<HttpResponse<Membership>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/memberships`,
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
  manmagerCreateMembership(body?: MembershipForm): Observable<Membership> {
    return this.manmagerCreateMembershipResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateMembershipResponse(body?: MembershipForm): Observable<HttpResponse<Membership>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/memberships`,
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
  managerUpdateMembership(body?: MembershipForm): Observable<Membership> {
    return this.managerUpdateMembershipResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param transactionType - undefined
   * @param rfid - undefined
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerTransactionGetMembershipDetailByRfidResponse(params: ManagerMembershipsService.ManagerTransactionGetMembershipDetailByRfidParams): Observable<HttpResponse<MembershipDetail>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/memberships/${params.rfid}/transaction/${params.transactionType}`,
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
        let _body: MembershipDetail = null;
        _body = _resp.body as MembershipDetail
        return _resp.clone({body: _body}) as HttpResponse<MembershipDetail>;
      })
    );
  }

  /**
   * @param transactionType - undefined
   * @param rfid - undefined
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerTransactionGetMembershipDetailByRfid(params: ManagerMembershipsService.ManagerTransactionGetMembershipDetailByRfidParams): Observable<MembershipDetail> {
    return this.managerTransactionGetMembershipDetailByRfidResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  searchMembershipsDetailResponse(body?: MembershipDetailSearch): Observable<HttpResponse<MembershipDetail>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/memberships/detail/search`,
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
        let _body: MembershipDetail = null;
        _body = _resp.body as MembershipDetail
        return _resp.clone({body: _body}) as HttpResponse<MembershipDetail>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  searchMembershipsDetail(body?: MembershipDetailSearch): Observable<MembershipDetail> {
    return this.searchMembershipsDetailResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param transactionType - undefined
   * @param rfid - undefined
   * @param dateto - undefined
   * @param datefrom - undefined
   */
  managerTransactionGetMembershipCardDetailByRfidResponse(params: ManagerMembershipsService.ManagerTransactionGetMembershipCardDetailByRfidParams): Observable<HttpResponse<MembershipDetail>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    
    
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/membershipscard/${params.rfid}/transaction/${params.transactionType}/from/${params.datefrom}/to/${params.dateto}`,
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
        let _body: MembershipDetail = null;
        _body = _resp.body as MembershipDetail
        return _resp.clone({body: _body}) as HttpResponse<MembershipDetail>;
      })
    );
  }

  /**
   * @param transactionType - undefined
   * @param rfid - undefined
   * @param dateto - undefined
   * @param datefrom - undefined
   */
  managerTransactionGetMembershipCardDetailByRfid(params: ManagerMembershipsService.ManagerTransactionGetMembershipCardDetailByRfidParams): Observable<MembershipDetail> {
    return this.managerTransactionGetMembershipCardDetailByRfidResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistMembershipActiveResponse(params: ManagerMembershipsService.ManagerlistMembershipActiveParams): Observable<HttpResponse<Membership[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/memberships/actived`,
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
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistMembershipActive(params: ManagerMembershipsService.ManagerlistMembershipActiveParams): Observable<Membership[]> {
    return this.managerlistMembershipActiveResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param membershipId - undefined
   */
  managerGetMembershipByIdResponse(membershipId: number): Observable<HttpResponse<Membership>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/memberships/${membershipId}`,
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
   * @param membershipId - undefined
   */
  managerGetMembershipById(membershipId: number): Observable<Membership> {
    return this.managerGetMembershipByIdResponse(membershipId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param membershipId - undefined
   */
  managerDeleteMembershipResponse(membershipId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/memberships/${membershipId}`,
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
   * @param membershipId - undefined
   */
  managerDeleteMembership(membershipId: number): Observable<void> {
    return this.managerDeleteMembershipResponse(membershipId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerListMembershipsByInputAndBySearchResponse(body?: MembershipInput): Observable<HttpResponse<Membership[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/memberships/search`,
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
  managerListMembershipsByInputAndBySearch(body?: MembershipInput): Observable<Membership[]> {
    return this.managerListMembershipsByInputAndBySearchResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param rfid - undefined
   */
  managerGetMembershipByRfidResponse(rfid: string): Observable<HttpResponse<Membership>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/memberships/search/${rfid}`,
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
   * @param rfid - undefined
   */
  managerGetMembershipByRfid(rfid: string): Observable<Membership> {
    return this.managerGetMembershipByRfidResponse(rfid).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateRfidMembershipByIdResponse(body?: MembershipSwapForm): Observable<HttpResponse<Membership>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/memberships/update/rfid`,
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
  managerUpdateRfidMembershipById(body?: MembershipSwapForm): Observable<Membership> {
    return this.managerUpdateRfidMembershipByIdResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateActivedMembershipByIdResponse(body?: MembershipForm): Observable<HttpResponse<Membership>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/memberships/update/actived`,
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
  managerUpdateActivedMembershipById(body?: MembershipForm): Observable<Membership> {
    return this.managerUpdateActivedMembershipByIdResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param barcode - undefined
   */
  managerGetMembershipTransactionByBarcodeForAppResponse(barcode: string): Observable<HttpResponse<MembershipTransaction>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/memberships/app/transactions/barcode/${barcode}`,
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
        let _body: MembershipTransaction = null;
        _body = _resp.body as MembershipTransaction
        return _resp.clone({body: _body}) as HttpResponse<MembershipTransaction>;
      })
    );
  }

  /**
   * @param barcode - undefined
   */
  managerGetMembershipTransactionByBarcodeForApp(barcode: string): Observable<MembershipTransaction> {
    return this.managerGetMembershipTransactionByBarcodeForAppResponse(barcode).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param barcode - undefined
   */
  managerGetMembershipByBarcodeForAppResponse(barcode: string): Observable<HttpResponse<Membership>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/memberships/app/search/barcode/${barcode}`,
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
   * @param barcode - undefined
   */
  managerGetMembershipByBarcodeForApp(barcode: string): Observable<Membership> {
    return this.managerGetMembershipByBarcodeForAppResponse(barcode).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerEditMembershipByBarcodeToForAppResponse(body?: MembershipFormApp): Observable<HttpResponse<Membership>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/memberships/app/edit/barcode`,
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
  managerEditMembershipByBarcodeToForApp(body?: MembershipFormApp): Observable<Membership> {
    return this.managerEditMembershipByBarcodeToForAppResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerRegisterMembershipForAppResponse(body?: MembershipTmpFormApp): Observable<HttpResponse<MembershipTmp>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/memberships/app/register`,
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
   * @param body - undefined
   */
  managerRegisterMembershipForApp(body?: MembershipTmpFormApp): Observable<MembershipTmp> {
    return this.managerRegisterMembershipForAppResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerMembershipsService {
  export interface ManagerlistMembershipsParams {
    page?: number;
    limit?: number;
  }
  export interface ManagerTransactionGetMembershipDetailByRfidParams {
    transactionType: number;
    rfid: string;
    page?: number;
    limit?: number;
  }
  export interface ManagerTransactionGetMembershipCardDetailByRfidParams {
    transactionType: number;
    rfid: string;
    dateto: string;
    datefrom: string;
  }
  export interface ManagerlistMembershipActiveParams {
    page?: number;
    limit?: number;
  }
}
