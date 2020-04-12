import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TicketDestroysComponent } from './ticket-destroys.component';

describe('TicketDestroysComponent', () => {
  let component: TicketDestroysComponent;
  let fixture: ComponentFixture<TicketDestroysComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TicketDestroysComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TicketDestroysComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
