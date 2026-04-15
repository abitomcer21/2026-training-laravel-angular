import { Component, OnInit, inject } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { IonicModule } from '@ionic/angular';
import { AuthService } from '../../services/api/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
  standalone: true,
  imports: [FormsModule, IonicModule],
})
export class LoginPage implements OnInit {
  public email = '';
  public password = '';
  public isSubmitting = false;
  public errorMessage = '';

  private authService: AuthService = inject(AuthService);
  private router: Router = inject(Router);

  ngOnInit(): void {
    if (this.authService.isLoggedIn()) {
      void this.router.navigateByUrl('/admin', { replaceUrl: true });
    }
  }

  login(): void {
    this.errorMessage = '';
    if (this.email.trim() === '' || this.password.trim() === '') {
      this.errorMessage = 'Introduce tu email y contraseña.';
      return;
    }
    this.isSubmitting = true;
    this.authService.login(this.email.trim(), this.password).subscribe({
      next: () => {
        this.isSubmitting = false;
        void this.router.navigateByUrl('/admin', { replaceUrl: true });
      },
      error: (error: any) => {
        this.isSubmitting = false;
        if (error.status === 401) {
          this.errorMessage = 'Credenciales incorrectas.';
        } else {
          this.errorMessage = 'No se pudo iniciar sesión. Inténtalo de nuevo.';
        }
      },
    });
  }
}