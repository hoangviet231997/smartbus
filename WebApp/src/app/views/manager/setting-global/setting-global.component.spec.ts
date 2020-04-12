import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SettingGlobalComponent } from './setting-global.component';

describe('SettingGlobalComponent', () => {
  let component: SettingGlobalComponent;
  let fixture: ComponentFixture<SettingGlobalComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SettingGlobalComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SettingGlobalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
