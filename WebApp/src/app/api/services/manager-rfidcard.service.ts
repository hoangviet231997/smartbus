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

import { RfidCards } from '../models/rfid-cards';
import { RfidCard } from '../models/rfid-card';
import { RfidCardCreate } from '../models/rfid-card-create';
import { RfidCardUpdate } from '../models/rfid-card-update';


@Injectable()
export class ManagerRfidcardService extends BaseService {
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
  managerlistRfidcardsResponse(params: ManagerRfidcardService.ManagerlistRfidcardsParams): Observable<HttpResponse<RfidCards[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/rfidcard`,
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
        let _body: RfidCards[] = null;
        _body = _resp.body as RfidCards[]
        return _resp.clone({body: _body}) as HttpResponse<RfidCards[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistRfidcards(params: ManagerRfidcardService.ManagerlistRfidcardsParams): Observable<RfidCards[]> {
    return this.managerlistRfidcardsResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerCreateRfidcardResponse(body?: RfidCardCreate): Observable<HttpResponse<RfidCard>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/rfidcard`,
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
        let _body: RfidCard = null;
        _body = _resp.body as RfidCard
        return _resp.clone({body: _body}) as HttpResponse<RfidCard>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerCreateRfidcard(body?: RfidCardCreate): Observable<RfidCard> {
    return this.managerCreateRfidcardResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateRfidcardResponse(body?: RfidCardUpdate): Observable<HttpResponse<RfidCard>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/rfidcard`,
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
        let _body: RfidCard = null;
        _body = _resp.body as RfidCard
        return _resp.clone({body: _body}) as HttpResponse<RfidCard>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdateRfidcard(body?: RfidCardUpdate): Observable<RfidCard> {
    return this.managerUpdateRfidcardResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param rfidcardId - undefined
   */
  managerGetRfidcardByIdResponse(rfidcardId: number): Observable<HttpResponse<RfidCard>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/rfidcard/${rfidcardId}`,
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
        let _body: RfidCard = null;
        _body = _resp.body as RfidCard
        return _resp.clone({body: _body}) as HttpResponse<RfidCard>;
      })
    );
  }

  /**
   * @param rfidcardId - undefined
   */
  managerGetRfidcardById(rfidcardId: number): Observable<RfidCard> {
    return this.managerGetRfidcardByIdResponse(rfidcardId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param rfidcardId - undefined
   */
  managerDeleteRfidcardResponse(rfidcardId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/rfidcard/${rfidcardId}`,
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
   * @param rfidcardId - undefined
   */
  managerDeleteRfidcard(rfidcardId: number): Observable<void> {
    return this.managerDeleteRfidcardResponse(rfidcardId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerSearchRfidcardResponse(body?: RfidCardCreate): Observable<HttpResponse<RfidCard>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/rfidcard/search`,
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
        let _body: RfidCard = null;
        _body = _resp.body as RfidCard
        return _resp.clone({body: _body}) as HttpResponse<RfidCard>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerSearchRfidcard(body?: RfidCardCreate): Observable<RfidCard> {
    return this.managerSearchRfidcardResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerRfidcardService {
  export interface ManagerlistRfidcardsParams {
    page?: number;
    limit?: number;
  }
}
