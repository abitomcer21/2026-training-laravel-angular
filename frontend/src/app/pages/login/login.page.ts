import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { 
  IonContent, IonGrid, IonRow, IonCol, IonCard, 
  IonCardHeader, IonCardTitle, IonCardSubtitle, 
  IonCardContent, IonItem, IonLabel, IonInput, 
  IonButton, IonToast, IonSpinner  // ← Añade IonSpinner aquí
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
    IonContent, IonGrid, IonRow, IonCol, IonCard,
    IonCardHeader, IonCardTitle,
    IonCardContent, IonItem, IonLabel, IonInput,
    IonButton, IonToast, IonSpinner
  ]
})
export class LoginPage {
  email = '';
  password = '';
  showError = false;
  errorMessage = '';
  loading = false;

  constructor(
    private router: Router,
    private authService: AuthService
  ) {}

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
          // Solo administradores van al dashboard
          this.router.navigate(['/dashboard']);
        } else {
          // Otros roles (camarero, chef, supervisor) van a página en desarrollo
          this.router.navigate(['/coming-soon']);
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