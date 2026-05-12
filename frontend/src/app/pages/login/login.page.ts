import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { 
  IonContent, IonGrid, IonRow, IonCol, IonCard, 
  IonCardHeader, IonCardTitle, IonCardSubtitle, 
  IonCardContent, IonItem, IonLabel, IonInput, 
  IonButton, IonSpinner
} from '@ionic/angular/standalone';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    IonContent,  IonInput,
    IonButton, IonSpinner
  ]
})
export class LoginPage implements OnInit {
  email = '';
  password = '';
  showError = false;
  errorMessage = '';
  loading = false;
  tpvMode = false;

  constructor(
    private router: Router,
    private authService: AuthService
  ) {}

  ngOnInit() {
    // Detectar si venimos del TPV
    const currentUrl = window.location.pathname;
    if (currentUrl.includes('point-of-sale') || currentUrl.includes('punto-venta')) {
      this.tpvMode = true;
      this.email = 'titovicent@restaurante.com';
    }
  }

  login() {
    if (!this.email || !this.password) {
      this.errorMessage = 'Por favor, completa email y contraseña';
      this.showError = true;
      return;
    }

    this.loading = true;

    this.authService.login(this.email, this.password).subscribe({
      next: (response: any) => {
        this.loading = false;
        
        this.authService.saveToken(response.token);
        
        localStorage.setItem('userData', JSON.stringify(response.user));
        localStorage.setItem('isLoggedIn', 'true');
        
        const role = response.user.role;
        if (role === 'admin') {
          this.router.navigate(['/dashboard']);
        } else {
          this.router.navigate(['/point-of-sale']);
        }
      },
      error: (error) => {
        this.loading = false;
        this.errorMessage = error.message || 'Credenciales incorrectas';
        this.showError = true;
      }
    });
  }

  cerrarError() {
    this.showError = false;
  }
}
