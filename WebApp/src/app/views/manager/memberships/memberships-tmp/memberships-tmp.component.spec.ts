import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MembershipsTmpComponent } from './memberships-tmp.component';

describe('MembershipsTmpComponent', () => {
  let component: MembershipsTmpComponent;
  let fixture: ComponentFixture<MembershipsTmpComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MembershipsTmpComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MembershipsTmpComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
