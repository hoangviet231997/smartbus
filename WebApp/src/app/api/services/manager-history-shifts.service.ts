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

import { HistoryShift } from '../models/history-shift';
import { HistoryShiftForm } from '../models/history-shift-form';
import { HistoryShiftSearch } from '../models/history-shift-search';


@Injectable()
export class ManagerHistoryShiftsService extends BaseService {
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
  managerListHistoryShiftsResponse(params: ManagerHistoryShiftsService.ManagerListHistoryShiftsParams): Observable<HttpResponse<HistoryShift[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/history/shift`,
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
        let _body: HistoryShift[] = null;
        _body = _resp.body as HistoryShift[]
        return _resp.clone({body: _body}) as HttpResponse<HistoryShift[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerListHistoryShifts(params: ManagerHistoryShiftsService.ManagerListHistoryShiftsParams): Observable<HistoryShift[]> {
    return this.managerListHistoryShiftsResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerCreateHistoryShiftResponse(body?: HistoryShiftForm): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/history/shift`,
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
        let _body: string = null;
        _body = _resp.body as string
        return _resp.clone({body: _body}) as HttpResponse<string>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerCreateHistoryShift(body?: HistoryShiftForm): Observable<string> {
    return this.managerCreateHistoryShiftResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param historyshiftId - undefined
   */
  managerGetHistoryShiftByIdResponse(historyshiftId: number): Observable<HttpResponse<HistoryShift>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/history/shift/${historyshiftId}`,
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
        let _body: HistoryShift = null;
        _body = _resp.body as HistoryShift
        return _resp.clone({body: _body}) as HttpResponse<HistoryShift>;
      })
    );
  }

  /**
   * @param historyshiftId - undefined
   */
  managerGetHistoryShiftById(historyshiftId: number): Observable<HistoryShift> {
    return this.managerGetHistoryShiftByIdResponse(historyshiftId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param historyshiftId - undefined
   */
  managerDeleteHistoryShiftResponse(historyshiftId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/history/shift/${historyshiftId}`,
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
   * @param historyshiftId - undefined
   */
  managerDeleteHistoryShift(historyshiftId: number): Observable<void> {
    return this.managerDeleteHistoryShiftResponse(historyshiftId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerHistoryShiftSearchResponse(body?: HistoryShiftSearch): Observable<HttpResponse<HistoryShift[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/history/shift/search`,
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
        let _body: HistoryShift[] = null;
        _body = _resp.body as HistoryShift[]
        return _resp.clone({body: _body}) as HttpResponse<HistoryShift[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerHistoryShiftSearch(body?: HistoryShiftSearch): Observable<HistoryShift[]> {
    return this.managerHistoryShiftSearchResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerHistoryShiftExportResponse(body?: HistoryShiftSearch): Observable<HttpResponse<string>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/history/shift/export`,
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
        let _body: string = null;
        _body = _resp.body as string
        return _resp.clone({body: _body}) as HttpResponse<string>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerHistoryShiftExport(body?: HistoryShiftSearch): Observable<string> {
    return this.managerHistoryShiftExportResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerHistoryShiftsService {
  export interface ManagerListHistoryShiftsParams {
    page?: number;
    limit?: number;
  }
}
