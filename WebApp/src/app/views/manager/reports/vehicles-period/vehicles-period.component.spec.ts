import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { VehiclesPeriodComponent } from './vehicles-period.component';

describe('VehiclesPeriodComponent', () => {
  let component: VehiclesPeriodComponent;
  let fixture: ComponentFixture<VehiclesPeriodComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ VehiclesPeriodComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(VehiclesPeriodComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
