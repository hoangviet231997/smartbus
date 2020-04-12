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

import { AppTicket } from '../models/app-ticket';


@Injectable()
export class AppTicketsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   * @param body - New ticket object
   */
  insertTicketResponse(body?: AppTicket): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/app/insert_ticket`,
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
   * @param body - New ticket object
   */
  insertTicket(body?: AppTicket): Observable<void> {
    return this.insertTicketResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param ticket_code - Ticket code
   */
  getTicketInfoResponse(ticketCode?: string): Observable<HttpResponse<AppTicket>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (ticketCode != null) __params = __params.set("ticket_code", ticketCode.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/app/get_ticket`,
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
        let _body: AppTicket = null;
        _body = _resp.body as AppTicket
        return _resp.clone({body: _body}) as HttpResponse<AppTicket>;
      })
    );
  }

  /**
   * @param ticket_code - Ticket code
   */
  getTicketInfo(ticketCode?: string): Observable<AppTicket> {
    return this.getTicketInfoResponse(ticketCode).pipe(
      map(_r => _r.body)
    );
  }}

export module AppTicketsService {
}
