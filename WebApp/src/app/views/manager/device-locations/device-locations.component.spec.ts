import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DeviceLocationsComponent } from './device-locations.component';

describe('DeviceLocationsComponent', () => {
  let component: DeviceLocationsComponent;
  let fixture: ComponentFixture<DeviceLocationsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DeviceLocationsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DeviceLocationsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
