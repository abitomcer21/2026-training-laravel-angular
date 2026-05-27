import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class ThemeService {
  private darkModeSubject = new BehaviorSubject<boolean>(
    this.loadThemePreference(),
  );

  darkMode$ = this.darkModeSubject.asObservable();

  constructor() {
    this.applyTheme(this.darkModeSubject.value);
  }

  private loadThemePreference(): boolean {
    const saved = localStorage.getItem('darkMode');
    if (saved !== null) {
      return saved === 'true';
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
  }

  toggleTheme(): void {
    const newValue = !this.darkModeSubject.value;
    this.darkModeSubject.next(newValue);
    localStorage.setItem('darkMode', String(newValue));
    this.applyTheme(newValue);
  }

  setTheme(isDark: boolean): void {
    this.darkModeSubject.next(isDark);
    localStorage.setItem('darkMode', String(isDark));
    this.applyTheme(isDark);
  }

  private applyTheme(isDark: boolean): void {
    const html = document.documentElement;
    if (isDark) {
      html.classList.add('ion-palette-dark');
    } else {
      html.classList.remove('ion-palette-dark');
    }
  }

  isDarkMode(): boolean {
    return this.darkModeSubject.value;
  }
}
