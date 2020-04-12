import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DeviceFirmwareVersionComponent } from './device-firmware-version.component';

describe('DeviceFirmwareVersionComponent', () => {
  let component: DeviceFirmwareVersionComponent;
  let fixture: ComponentFixture<DeviceFirmwareVersionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DeviceFirmwareVersionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DeviceFirmwareVersionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
