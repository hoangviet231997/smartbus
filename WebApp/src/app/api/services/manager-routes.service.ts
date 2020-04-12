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

import { Route } from '../models/route';
import { RouteForm } from '../models/route-form';
import { RoutesBusStions } from '../models/routes-bus-stions';
import { RouteInput } from '../models/route-input';


@Injectable()
export class ManagerRoutesService extends BaseService {
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
  managerlistRoutesResponse(params: ManagerRoutesService.ManagerlistRoutesParams): Observable<HttpResponse<Route[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    if (params.page != null) __params = __params.set("page", params.page.toString());
    if (params.limit != null) __params = __params.set("limit", params.limit.toString());
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/routes`,
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
        let _body: Route[] = null;
        _body = _resp.body as Route[]
        return _resp.clone({body: _body}) as HttpResponse<Route[]>;
      })
    );
  }

  /**
   * @param page - The number of items to skip before starting to collect the result set.
   * @param limit - The numbers of items to return.
   */
  managerlistRoutes(params: ManagerRoutesService.ManagerlistRoutesParams): Observable<Route[]> {
    return this.managerlistRoutesResponse(params).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  manmagerCreateRouteResponse(body?: RouteForm): Observable<HttpResponse<Route>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/routes`,
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
        let _body: Route = null;
        _body = _resp.body as Route
        return _resp.clone({body: _body}) as HttpResponse<Route>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  manmagerCreateRoute(body?: RouteForm): Observable<Route> {
    return this.manmagerCreateRouteResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateRouteResponse(body?: RouteForm): Observable<HttpResponse<Route>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/routes`,
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
        let _body: Route = null;
        _body = _resp.body as Route
        return _resp.clone({body: _body}) as HttpResponse<Route>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdateRoute(body?: RouteForm): Observable<Route> {
    return this.managerUpdateRouteResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param routeId - undefined
   */
  managerGetRouteByIdResponse(routeId: number): Observable<HttpResponse<Route>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/routes/${routeId}`,
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
        let _body: Route = null;
        _body = _resp.body as Route
        return _resp.clone({body: _body}) as HttpResponse<Route>;
      })
    );
  }

  /**
   * @param routeId - undefined
   */
  managerGetRouteById(routeId: number): Observable<Route> {
    return this.managerGetRouteByIdResponse(routeId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param routeId - undefined
   */
  managerDeleteRouteResponse(routeId: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/routes/${routeId}`,
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
   * @param routeId - undefined
   */
  managerDeleteRoute(routeId: number): Observable<void> {
    return this.managerDeleteRouteResponse(routeId).pipe(
      map(_r => _r.body)
    );
  }
  /**
   */
  managerGetRoutesBusStionsResponse(): Observable<HttpResponse<RoutesBusStions>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/routesBusStions/`,
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
        let _body: RoutesBusStions = null;
        _body = _resp.body as RoutesBusStions
        return _resp.clone({body: _body}) as HttpResponse<RoutesBusStions>;
      })
    );
  }

  /**
   */
  managerGetRoutesBusStions(): Observable<RoutesBusStions> {
    return this.managerGetRoutesBusStionsResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerSearchRouteResponse(body?: RouteInput): Observable<HttpResponse<Route[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/routes/search`,
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
        let _body: Route[] = null;
        _body = _resp.body as Route[]
        return _resp.clone({body: _body}) as HttpResponse<Route[]>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerSearchRoute(body?: RouteInput): Observable<Route[]> {
    return this.managerSearchRouteResponse(body).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerRoutesService {
  export interface ManagerlistRoutesParams {
    page?: number;
    limit?: number;
  }
}
