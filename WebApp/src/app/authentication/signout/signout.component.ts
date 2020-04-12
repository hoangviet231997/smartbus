import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../api/services';
import { User } from '../../api/models';

@Component({
  selector: 'app-signout',
  templateUrl: './signout.component.html',
  styleUrls: ['./signout.component.css']
})
export class SignoutComponent implements OnInit, AfterViewInit {

  public user: User = null;

  constructor(
    private apiAuths: AuthService, 
    private router: Router) 
  {

  }

  ngOnInit() {
  }

  ngAfterViewInit() {

    this.user = JSON.parse(localStorage.getItem('user'));
    
    this.apiAuths.logout({
      user_id: this.user.id,
      token: localStorage.getItem('token')
    }).subscribe(
      data => {

        // check old data
        if (localStorage.getItem('token_shadow')) {

          // set data new
          const token = localStorage.getItem('token_shadow');
          const user = localStorage.getItem('user_shadow');
          // const role = localStorage.getItem('role_shadow');
          // const permissions = localStorage.getItem('permissions_shadow');
          localStorage.setItem('token', token);
          localStorage.setItem('user', user);
          // localStorage.setItem('role', role);
          // localStorage.setItem('permissions', permissions);

          // remove data
          localStorage.removeItem('token_shadow');
          localStorage.removeItem('user_shadow');
          // localStorage.removeItem('role_shadow');
          // localStorage.removeItem('permissions_shadow');
          // localStorage.removeItem('company_id');
          // localStorage.removeItem('company_layout_cards');
          // localStorage.removeItem('company_lat');
          // localStorage.removeItem('company_lng');
          this.router.navigate(['/admin']);
          return;
        }

        localStorage.clear();
        this.router.navigate(['/auth/signin']);
      }
    );
  }
}
