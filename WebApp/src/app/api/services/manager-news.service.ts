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

import { News } from '../models/news';
import { Category } from '../models/category';
import { NewsForm } from '../models/news-form';


@Injectable()
export class ManagerNewsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  managerListNewsResponse(): Observable<HttpResponse<News[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/news`,
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
        let _body: News[] = null;
        _body = _resp.body as News[]
        return _resp.clone({body: _body}) as HttpResponse<News[]>;
      })
    );
  }

  /**
   */
  managerListNews(): Observable<News[]> {
    return this.managerListNewsResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerCreateNewsResponse(body?: NewsForm): Observable<HttpResponse<Category>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/news`,
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
        let _body: Category = null;
        _body = _resp.body as Category
        return _resp.clone({body: _body}) as HttpResponse<Category>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerCreateNews(body?: NewsForm): Observable<Category> {
    return this.managerCreateNewsResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateNewsResponse(body?: NewsForm): Observable<HttpResponse<News>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/news`,
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
        let _body: News = null;
        _body = _resp.body as News
        return _resp.clone({body: _body}) as HttpResponse<News>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdateNews(body?: NewsForm): Observable<News> {
    return this.managerUpdateNewsResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param newsID - undefined
   */
  managerGetNewsByIdResponse(newsID: number): Observable<HttpResponse<News>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/news/${newsID}`,
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
        let _body: News = null;
        _body = _resp.body as News
        return _resp.clone({body: _body}) as HttpResponse<News>;
      })
    );
  }

  /**
   * @param newsID - undefined
   */
  managerGetNewsById(newsID: number): Observable<News> {
    return this.managerGetNewsByIdResponse(newsID).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param newsID - undefined
   */
  managerDeleteNewsResponse(newsID: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/news/${newsID}`,
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
   * @param newsID - undefined
   */
  managerDeleteNews(newsID: number): Observable<void> {
    return this.managerDeleteNewsResponse(newsID).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerNewsService {
}
