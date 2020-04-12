import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TimeTripsComponent } from './time-trips.component';

describe('TimeTripsComponent', () => {
  let component: TimeTripsComponent;
  let fixture: ComponentFixture<TimeTripsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TimeTripsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TimeTripsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
