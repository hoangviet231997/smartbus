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

import { CategoryNews } from '../models/category-news';
import { CategoryNewsForm } from '../models/category-news-form';


@Injectable()
export class ManagerCateNewsService extends BaseService {
  constructor(
    config: ApiConfiguration,
    http: HttpClient
  ) {
    super(config, http);
  }

  /**
   */
  managerListCategoryNewsResponse(): Observable<HttpResponse<CategoryNews[]>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/cate_news`,
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
        let _body: CategoryNews[] = null;
        _body = _resp.body as CategoryNews[]
        return _resp.clone({body: _body}) as HttpResponse<CategoryNews[]>;
      })
    );
  }

  /**
   */
  managerListCategoryNews(): Observable<CategoryNews[]> {
    return this.managerListCategoryNewsResponse().pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerCreateCategoryNewsResponse(body?: CategoryNewsForm): Observable<HttpResponse<CategoryNews>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "POST",
      this.rootUrl + `/manager/cate_news`,
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
        let _body: CategoryNews = null;
        _body = _resp.body as CategoryNews
        return _resp.clone({body: _body}) as HttpResponse<CategoryNews>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerCreateCategoryNews(body?: CategoryNewsForm): Observable<CategoryNews> {
    return this.managerCreateCategoryNewsResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param body - undefined
   */
  managerUpdateCategoryNewsResponse(body?: CategoryNewsForm): Observable<HttpResponse<CategoryNews>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    __body = body;
    let req = new HttpRequest<any>(
      "PATCH",
      this.rootUrl + `/manager/cate_news`,
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
        let _body: CategoryNews = null;
        _body = _resp.body as CategoryNews
        return _resp.clone({body: _body}) as HttpResponse<CategoryNews>;
      })
    );
  }

  /**
   * @param body - undefined
   */
  managerUpdateCategoryNews(body?: CategoryNewsForm): Observable<CategoryNews> {
    return this.managerUpdateCategoryNewsResponse(body).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param cateNewsID - undefined
   */
  managerGetCategoryNewsByIdResponse(cateNewsID: number): Observable<HttpResponse<CategoryNews>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "GET",
      this.rootUrl + `/manager/cate_news/${cateNewsID}`,
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
        let _body: CategoryNews = null;
        _body = _resp.body as CategoryNews
        return _resp.clone({body: _body}) as HttpResponse<CategoryNews>;
      })
    );
  }

  /**
   * @param cateNewsID - undefined
   */
  managerGetCategoryNewsById(cateNewsID: number): Observable<CategoryNews> {
    return this.managerGetCategoryNewsByIdResponse(cateNewsID).pipe(
      map(_r => _r.body)
    );
  }
  /**
   * @param cateNewsID - undefined
   */
  managerDeleteCategoryNewsResponse(cateNewsID: number): Observable<HttpResponse<void>> {
    let __params = this.newParams();
    let __headers = new HttpHeaders();
    let __body: any = null;
    
    let req = new HttpRequest<any>(
      "DELETE",
      this.rootUrl + `/manager/cate_news/${cateNewsID}`,
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
   * @param cateNewsID - undefined
   */
  managerDeleteCategoryNews(cateNewsID: number): Observable<void> {
    return this.managerDeleteCategoryNewsResponse(cateNewsID).pipe(
      map(_r => _r.body)
    );
  }}

export module ManagerCateNewsService {
}
