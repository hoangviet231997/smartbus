import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { VehiclesRoutesPeriodComponent } from './vehicles-routes-period.component';

describe('VehiclesRoutesPeriodComponent', () => {
  let component: VehiclesRoutesPeriodComponent;
  let fixture: ComponentFixture<VehiclesRoutesPeriodComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ VehiclesRoutesPeriodComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(VehiclesRoutesPeriodComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
