import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ManagePermissionV2Component } from './manage-permission-v2.component';

describe('ManagePermissionV2Component', () => {
  let component: ManagePermissionV2Component;
  let fixture: ComponentFixture<ManagePermissionV2Component>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ManagePermissionV2Component ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ManagePermissionV2Component);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
