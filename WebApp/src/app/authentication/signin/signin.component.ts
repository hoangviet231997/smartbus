import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../api/services';
import { User, Login } from '../../api/models';
import swal from 'sweetalert2';
// import { RolesService } from '../../shared/roles.service';

@Component({
  selector: 'app-signin',
  templateUrl: './signin.component.html',
  styleUrls: ['./signin.component.css']
})
export class SigninComponent implements OnInit, AfterViewInit {

  public login: Login;
  public token = null;
  public user: User = null;
  public success = false;

  constructor(
    // private roles: RolesService,
    private apiAuths: AuthService,
    private router: Router) {
    this.login = new Login();
  }

  ngOnInit() {
  }

  ngAfterViewInit() {

    if (localStorage.getItem('token')) {
      var user = JSON.parse(localStorage.getItem('user'));
      if(user){
        if (user.role.name === 'admin') {
          this.router.navigate(['/admin']);
        } else {
          if (user.role.name === 'driver' || user.role.name === 'subdriver') {
            if(user.permissions) this.router.navigate(['/manager/sub-dashboard/']);
          }else{
            this.router.navigate(['/manager']);
          }
        }
      }
    }
  }

  onLogin() {
    this.success = true;
    this.apiAuths.login({
      username: this.login.username,
      password: this.login.password
    }).subscribe(
      res => {
        this.success = false;
        localStorage.setItem('token', res.token);
        localStorage.setItem('user', JSON.stringify(res.user));
        // const roleName = res.user.role.name;
        // localStorage.setItem('role', roleName);
        // this.roles.setPermissions(res.user.permissions);
        
        if (res.user.role.name === 'admin') {
          this.router.navigate(['/admin']);
        } else {
          if (res.user.role.name === 'driver' || res.user.role.name === 'subdriver') {
            if(res.user.permissions) this.router.navigate(['/manager/sub-dashboard/']);
          }else{
            this.router.navigate(['/manager']);
          }
          // localStorage.setItem('company_id', res.user.company.id.toString());
          // localStorage.setItem('company_layout_cards', res.user.company.layout_cards);
          // localStorage.setItem('company_lat', res.user.company['position'].coordinates[1]);
          // localStorage.setItem('company_lng', res.user.company['position'].coordinates[0]);
        }
      },
      err => {
        this.success = false;
        swal('Warning', 'Logged in failed', 'error');
      }
    );
  }
}
