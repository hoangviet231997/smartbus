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

import { TicketType } from '../models/ticket-type';
import { TicketTypeForm } from '../models/ticket-type-form';
import { TicketAllocate } from '../models/ticket-allocate';
import { TicketAllocateSearch } from '../models/ticket-allocate-search';
import { TicketAllocateForm } from '../models/ticket-allocate-form';
import { TicketTypeInput } from '../models/ticket-type-input';


@Injectable()
export class ManagerTicketTypesService extends BaseService {
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
  managerlistTicketTypesResponse(params: ManagerTicketTypesService.ManagerlistTicketTypesParams): Observable<HttpResponse<TicketType[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/ticketTypes`,
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
        let _body: TicketType[] = null;
        _body = _resp.body as TicketType[]
        return _resp.clone({body: _body}) as HttpResponse<TicketType[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistTicketTypes(params: ManagerTicketTypesService.ManagerlistTicketTypesParams): Observable<TicketType[]> {
    return this.managerlistTicketTypesResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerCreateTicketTypeResponse(body?: TicketTypeForm): Observable<HttpResponse<TicketType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/ticketTypes`,
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
        let _body: TicketType = null;
        _body = _resp.body as TicketType
        return _resp.clone({body: _body}) as HttpResponse<TicketType>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerCreateTicketType(body?: TicketTypeForm): Observable<TicketType> {
    return this.managerCreateTicketTypeResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateTicketTypeResponse(body?: TicketTypeForm): Observable<HttpResponse<TicketType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/ticketTypes`,
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
        let _body: TicketType = null;
        _body = _resp.body as TicketType
        return _resp.clone({body: _body}) as HttpResponse<TicketType>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdateTicketType(body?: TicketTypeForm): Observable<TicketType> {
    return this.managerUpdateTicketTypeResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param ticketTypeId - undefined
   */
  managerGetTicketTypeByIdResponse(ticketTypeId: number): Observable<HttpResponse<TicketType>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/ticketTypes/${ticketTypeId}`,
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
        let _body: TicketType = null;
        _body = _resp.body as TicketType
        return _resp.clone({body: _body}) as HttpResponse<TicketType>;
      })
    );
  }

  /**
   * @param ticketTypeId - undefined
   */
  managerGetTicketTypeById(ticketTypeId: number): Observable<TicketType> {
    return this.managerGetTicketTypeByIdResponse(ticketTypeId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param ticketTypeId - undefined
   */
  managerDeleteticketTypeResponse(ticketTypeId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/ticketTypes/${ticketTypeId}`,
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
   * @param ticketTypeId - undefined
   */
  managerDeleteticketType(ticketTypeId: number): Observable<void> {
    return this.managerDeleteticketTypeResponse(ticketTypeId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  managerlistTicketAllocatesResponse(): Observable<HttpResponse<TicketAllocate[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/ticketTypes/ticketAllocates`,
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
        let _body: TicketAllocate[] = null;
        _body = _resp.body as TicketAllocate[]
        return _resp.clone({body: _body}) as HttpResponse<TicketAllocate[]>;
      })
    );
  }

  /**
   */
  managerlistTicketAllocates(): Observable<TicketAllocate[]> {
    return this.managerlistTicketAllocatesResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerlistTicketAllocateSearchsResponse(body?: TicketAllocateForm): Observable<HttpResponse<TicketAllocateSearch[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/ticketTypes/ticketAllocateSearch`,
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
        let _body: TicketAllocateSearch[] = null;
        _body = _resp.body as TicketAllocateSearch[]
        return _resp.clone({body: _body}) as HttpResponse<TicketAllocateSearch[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerlistTicketAllocateSearchs(body?: TicketAllocateForm): Observable<TicketAllocateSearch[]> {
    return this.managerlistTicketAllocateSearchsResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param typeParam - undefined
   */
  managerListTicketTypesByTypeParamResponse(typeParam: number): Observable<HttpResponse<TicketType[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/ticketTypes/types/${typeParam}`,
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
        let _body: TicketType[] = null;
        _body = _resp.body as TicketType[]
        return _resp.clone({body: _body}) as HttpResponse<TicketType[]>;
      })
    );
  }

  /**
   * @param typeParam - undefined
   */
  managerListTicketTypesByTypeParam(typeParam: number): Observable<TicketType[]> {
    return this.managerListTicketTypesByTypeParamResponse(typeParam).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerSearchTicketTypesByKeyWordResponse(body?: TicketTypeInput): Observable<HttpResponse<TicketType[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/ticketTypes/searchbykeyword`,
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
        let _body: TicketType[] = null;
        _body = _resp.body as TicketType[]
        return _resp.clone({body: _body}) as HttpResponse<TicketType[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerSearchTicketTypesByKeyWord(body?: TicketTypeInput): Observable<TicketType[]> {
    return this.managerSearchTicketTypesByKeyWordResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerTicketTypesService {
  export interface ManagerlistTicketTypesParams {
    page?: number;
    limit?: number;
  }
}
