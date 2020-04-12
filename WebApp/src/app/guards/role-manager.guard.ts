import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router } from '@angular/router';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class RoleManagerGuard implements CanActivate {

  constructor(private router: Router) { }

  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
      if (JSON.parse(localStorage.getItem('user')).role.name !== 'admin') {
        // logged in so return true
        return true;
      }

      // not logged in so redirect to login page
      this.router.navigate(['/admin']);
      return false;
  }
}
