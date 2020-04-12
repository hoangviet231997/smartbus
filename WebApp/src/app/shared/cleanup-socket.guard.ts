import { Injectable, Component } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, CanDeactivate } from '@angular/router';
import { Observable } from 'rxjs';
import { SocketComponent } from './socket-component';

@Injectable({
  providedIn: 'root'
})
export class CleanupSocketGuard implements CanActivate, CanDeactivate<SocketComponent> {
  canActivate(
    next: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean> | Promise<boolean> | boolean {
    return true;
  }

  canDeactivate(component: SocketComponent, currentRoute: ActivatedRouteSnapshot,
    currentState: RouterStateSnapshot, nextState?: RouterStateSnapshot) {
      component.socketDown();
      return true;
  }
}
