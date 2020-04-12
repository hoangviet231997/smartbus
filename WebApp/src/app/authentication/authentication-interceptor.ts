import { Injectable } from '@angular/core';
import { Router } from '@angular/router';

import { Observable } from 'rxjs';
import 'rxjs/add/operator/do';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent, HttpResponse, HttpErrorResponse, HttpHeaders } from '@angular/common/http';


@Injectable()
export class AuthenticationInterceptor implements HttpInterceptor {
    private token;
    constructor(private router: Router) { }

    intercept(request: HttpRequest < any >, next: HttpHandler): Observable < HttpEvent < any >> {

        this.token = localStorage.getItem('token');
        if (this.token) {
            request = request.clone({
                setHeaders: {
                    Authorization: `Bearer ${this.token}`
                }
            });
        }

        return next.handle(request).do((event: HttpEvent<any>) => {
            if (event instanceof HttpResponse) {
                // do stuff with response if you want
            }
        }, (err: any) => {
            if (err instanceof HttpErrorResponse) {
                if (err.status === 500 || err.status === 401) {
                    localStorage.clear();
                    this.router.navigate(['/auth/signin']);
                }
            }
        });
    }
}
