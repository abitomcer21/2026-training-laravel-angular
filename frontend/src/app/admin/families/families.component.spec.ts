import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { FamiliesComponent } from './families.component';

describe('FamiliesComponent', () => {
  let component: FamiliesComponent;
  let fixture: ComponentFixture<FamiliesComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      imports: [FamiliesComponent],
    }).compileComponents();

    fixture = TestBed.createComponent(FamiliesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
