import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PrepaidCardsComponent } from './prepaid-cards.component';

describe('PrepaidCardsComponent', () => {
  let component: PrepaidCardsComponent;
  let fixture: ComponentFixture<PrepaidCardsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PrepaidCardsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PrepaidCardsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
