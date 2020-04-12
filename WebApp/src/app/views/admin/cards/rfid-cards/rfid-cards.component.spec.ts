import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RfidCardsComponent } from './rfid-cards.component';

describe('RfidCardsComponent', () => {
  let component: RfidCardsComponent;
  let fixture: ComponentFixture<RfidCardsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RfidCardsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RfidCardsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
