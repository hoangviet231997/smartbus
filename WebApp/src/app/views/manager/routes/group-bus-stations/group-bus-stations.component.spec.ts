import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { GroupBusStationsComponent } from './group-bus-stations.component';

describe('GroupBusStationsComponent', () => {
  let component: GroupBusStationsComponent;
  let fixture: ComponentFixture<GroupBusStationsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ GroupBusStationsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(GroupBusStationsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
