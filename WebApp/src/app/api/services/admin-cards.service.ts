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
import { RfidCardCreate } from '../models/rfid-card-create';


@Injectable()
export class AdminCardsService extends BaseService {
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
  listRfidCardsResponse(params: AdminCardsService.ListRfidCardsParams): Observable<HttpResponse<RfidCards[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/cards`,
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
  listRfidCards(params: AdminCardsService.ListRfidCardsParams): Observable<RfidCards[]> {
    return this.listRfidCardsResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  createAndPrintRfidCardResponse(body?: RfidCardCreate): Observable<HttpResponse<RfidCards>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/admin/cards/printCreated`,
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
        let _body: RfidCards = null;
        _body = _resp.body as RfidCards
        return _resp.clone({body: _body}) as HttpResponse<RfidCards>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  createAndPrintRfidCard(body?: RfidCardCreate): Observable<RfidCards> {
    return this.createAndPrintRfidCardResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param KeyWord - undefined
   */
  listRfidCardsByInputRfidResponse(KeyWord: string): Observable<HttpResponse<RfidCards[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/admin/cards/search/${KeyWord}`,
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
   * @param KeyWord - undefined
   */
  listRfidCardsByInputRfid(KeyWord: string): Observable<RfidCards[]> {
    return this.listRfidCardsByInputRfidResponse(KeyWord).pipe(
      map(_r => _r.body)
    );
  }}

export module AdminCardsService {
  export interface ListRfidCardsParams {
    page?: number;
    limit?: number;
  }
}
