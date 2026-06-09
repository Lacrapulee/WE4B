import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../core/api/auth.service';

@Component({
  selector: 'app-connexion',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './connexion.html',
  styleUrl: './connexion.css',
})
export class Connexion {
  email = '';
  password = '';
  passwordFieldType = 'password';
  isLoading = false;
  errorMessage = '';

  constructor(private router: Router, private authService: AuthService) {}

  togglePassword() {
    this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
  }

  onSubmit() {
    this.isLoading = true;
    this.errorMessage = '';

    if (this.email && this.password) {
      this.authService.login({ email: this.email, password: this.password }).subscribe({
        next: (response) => {
          this.isLoading = false;
          if (response.success) {
            this.router.navigate(['/catalogue']);
          } else {
            this.errorMessage = response.message || response.error || 'Erreur lors de la connexion.';
          }
        },
        error: (err) => {
          this.isLoading = false;
          this.errorMessage = err.error?.error || err.error?.message || 'Une erreur est survenue. Veuillez réessayer.';
          console.error('Login error', err);
        }
      });
    } else {
      this.isLoading = false;
      this.errorMessage = 'Veuillez remplir tous les champs.';
    }
  }
}