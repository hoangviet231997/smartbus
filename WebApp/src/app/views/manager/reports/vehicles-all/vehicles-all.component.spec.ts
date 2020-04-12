import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { VehiclesAllComponent } from './vehicles-all.component';

describe('VehiclesAllComponent', () => {
  let component: VehiclesAllComponent;
  let fixture: ComponentFixture<VehiclesAllComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ VehiclesAllComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(VehiclesAllComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
