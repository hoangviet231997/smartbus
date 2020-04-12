import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PrintBlankCardsComponent } from './print-blank-cards.component';

describe('PrintBlankCardsComponent', () => {
  let component: PrintBlankCardsComponent;
  let fixture: ComponentFixture<PrintBlankCardsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PrintBlankCardsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PrintBlankCardsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
