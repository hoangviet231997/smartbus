import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BlankCardsComponent } from './blank-cards.component';

describe('BlankCardsComponent', () => {
  let component: BlankCardsComponent;
  let fixture: ComponentFixture<BlankCardsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BlankCardsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BlankCardsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
